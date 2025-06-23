<?php

namespace App\Services;

use App\Models\EmailAccount;
use App\Models\Ticket;
use App\Models\EmailRule;
use App\Services\GoogleTasksService;
use App\Services\AiRewritingService;
use Illuminate\Support\Facades\Log;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\Message;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client as GuzzleClient;

class EmailService
{
    protected ClientManager $clientManager;
    protected GoogleTasksService $googleTasksService;
    protected AiRewritingService $aiRewritingService;

    public function __construct(
        ClientManager $clientManager,
        GoogleTasksService $googleTasksService,
        AiRewritingService $aiRewritingService
    ) {
        $this->clientManager = $clientManager;
        $this->googleTasksService = $googleTasksService;
        $this->aiRewritingService = $aiRewritingService;
    }

    public function fetchEmails(EmailAccount $emailAccount): int
    {
        try {
            // Skip Google Tasks accounts - they don't have emails
            if ($emailAccount->type === 'google-tasks') {
                Log::info('Skipping email fetch for Google Tasks account: ' . $emailAccount->email);
                return 0;
            }

            // Skip calendar accounts - they don't have emails
            if (in_array($emailAccount->type, ['google-calendar', 'microsoft-calendar'])) {
                Log::info('Skipping email fetch for calendar account: ' . $emailAccount->email);
                return 0;
            }

            if ($emailAccount->type === 'gmail' || $emailAccount->type === 'outlook') {
                return $this->fetchOAuthEmails($emailAccount);
            } else {
                return $this->fetchImapEmails($emailAccount);
            }
        } catch (\Exception $e) {
            // Check if this is a scope-related error
            if ($this->isScopeInsufficientError($e)) {
                Log::warning("Gmail account {$emailAccount->email} has insufficient scopes. User needs to reconnect account.", [
                    'account_id' => $emailAccount->id,
                    'error' => $e->getMessage()
                ]);
                throw new \Exception("Gmail account '{$emailAccount->email}' needs to be reconnected to access all features. Please go to the account settings and click 'Reconnect Account'.");
            }
            
            Log::error('Error fetching emails for account: ' . $emailAccount->email, [
                'error' => $e->getMessage(),
                'account_id' => $emailAccount->id
            ]);
            return 0;
        }
    }

    protected function fetchImapEmails(EmailAccount $emailAccount): int
    {
        $client = $this->clientManager->make($emailAccount->getImapConfig());

        $client->connect();

        $folder = $client->getFolder('INBOX');
        $messages = $folder->messages()->unseen()->get();

        $emailCount = 0;
        foreach ($messages as $message) {
            $this->processEmail($emailAccount, $message);
            $emailCount++;
        }

        return $emailCount;
    }

    protected function fetchOAuthEmails(EmailAccount $emailAccount): int
    {
        if ($emailAccount->type === 'gmail') {
            return $this->fetchGmailEmails($emailAccount);
        } elseif ($emailAccount->type === 'outlook') {
            return $this->fetchOutlookEmails($emailAccount);
        }

        return 0;
    }

    protected function fetchGmailEmails(EmailAccount $emailAccount): int
    {
        $client = new \Google_Client();
        $client->setClientId(config('services.google-email.client_id'));
        $client->setClientSecret(config('services.google-email.client_secret'));
        $client->setAccessToken($emailAccount->access_token);
        
        $gmailService = new \Google_Service_Gmail($client);

        $processedCount = 0;
        $pageToken = null;
        $maxRetries = 1;
        $attempt = 0;

        do {
            try {
                $optParams = [
                    'maxResults' => 50,
                    'q' => 'is:unread',
                    'pageToken' => $pageToken
                ];
                
                $messagesResponse = $gmailService->users_messages->listUsersMessages('me', $optParams);
                
                if ($messagesResponse->getMessages()) {
                    foreach ($messagesResponse->getMessages() as $message) {
                        $gmailMessage = $gmailService->users_messages->get('me', $message->getId());
                        if ($gmailMessage) {
                            $this->processGmailMessage($emailAccount, $gmailMessage, $gmailService);
                            $processedCount++;
                            try {
                                $gmailService->users_messages->modify('me', $message->getId(), new \Google_Service_Gmail_ModifyMessageRequest(['removeLabelIds' => ['UNREAD']]));
                            } catch (\Google\Service\Exception $modifyException) {
                                if ($modifyException->getCode() === 403 && strpos($modifyException->getMessage(), 'insufficient authentication scopes') !== false) {
                                    Log::warning("Cannot mark message as read for {$emailAccount->email} - insufficient modify scope. Message will remain unread.", [
                                        'message_id' => $message->getId(),
                                        'account_id' => $emailAccount->id
                                    ]);
                                } else {
                                    throw $modifyException;
                                }
                            }
                        }
                    }
                }
                
                $pageToken = $messagesResponse->getNextPageToken();
                $attempt = 0;

            } catch (\Google\Service\Exception $e) {
                if ($e->getCode() === 401 && $attempt < $maxRetries) {
                    $attempt++;
                    Log::info("Token expired for Gmail account {$emailAccount->email}. Refreshing.");
                    try {
                        $refreshedData = $this->refreshGmailToken($emailAccount);
                        $emailAccount->fill($refreshedData);
                        $client->setAccessToken($emailAccount->access_token);
                        $gmailService = new \Google_Service_Gmail($client);
                        continue;
                    } catch (\Exception $refreshException) {
                        Log::error("Failed to refresh token during fetch for Gmail account {$emailAccount->email}. The account will be deactivated.", ['error' => $refreshException->getMessage()]);
                        return $processedCount;
                    }
                } else {
                    Log::error("Google API error while fetching emails for {$emailAccount->email}", ['error' => $e->getMessage()]);
                    return $processedCount;
                }
            }
        } while ($pageToken);

        return $processedCount;
    }

    protected function processGmailMessage(EmailAccount $emailAccount, $gmailMessage, $gmailService): void
    {
        $payload = $gmailMessage->getPayload();
        $headers = collect($payload->getHeaders());
        
        $bodyData = $this->extractGmailBody($payload);
        
        $emailData = [
            'subject' => $headers->firstWhere('name', 'Subject')->getValue(),
            'from_email' => $this->extractEmailFromHeader($headers->firstWhere('name', 'From')->getValue()),
            'from_name' => $this->extractNameFromHeader($headers->firstWhere('name', 'From')->getValue()),
            'to_emails' => array_map([$this, 'extractEmailFromHeader'], explode(', ', $headers->firstWhere('name', 'To')->getValue())),
            'cc_emails' => optional($headers->firstWhere('name', 'Cc'))->getValue() ? array_map([$this, 'extractEmailFromHeader'], explode(', ', $headers->firstWhere('name', 'Cc')->getValue())) : [],
            'received_at' => \Carbon\Carbon::createFromTimestampMs($gmailMessage->getInternalDate()),
            'body' => $bodyData['plain_text'],
            'html_content' => $bodyData['html_content'],
            'attachments' => $this->extractGmailAttachments($payload, $gmailService, $gmailMessage->getId()),
            'message_id' => $gmailMessage->getId(),
        ];
        
        Log::info('Processing Gmail message', [
            'account_id' => $emailAccount->id,
            'subject' => $emailData['subject'],
            'from' => $emailData['from_email'],
            'has_attachments' => !empty($emailData['attachments']),
            'attachment_count' => count($emailData['attachments']),
            'has_html' => !empty($emailData['html_content']),
        ]);

        // Log attachment details if present
        if (!empty($emailData['attachments'])) {
            Log::info('Gmail message has attachments', [
                'account_id' => $emailAccount->id,
                'subject' => $emailData['subject'],
                'attachment_count' => count($emailData['attachments']),
                'attachments' => array_map(function($att) {
                    return [
                        'name' => $att['name'],
                        'size' => $att['size'],
                        'type' => $att['type'],
                        'has_data' => isset($att['data']) && !empty($att['data']),
                        'data_length' => isset($att['data']) ? strlen($att['data']) : 0
                    ];
                }, $emailData['attachments'])
            ]);
        }

        if ($this->applyEmailRules($emailAccount, $emailData)) {
            return;
        }

        // Create ticket
        $ticket = $this->createTicket($emailAccount, $emailData);

        // Apply AI rewriting if enabled
        if (config('services.gemini.enabled', false)) {
            $this->aiRewritingService->rewriteTicket($ticket);
        }
    }

    protected function extractGmailBody($payload): array
    {
        $plainText = '';
        $htmlContent = '';

        if ($payload->getBody()->getData()) {
            $content = $this->base64url_decode($payload->getBody()->getData());
            if ($payload->getMimeType() === 'text/html') {
                $htmlContent = $content;
                $plainText = strip_tags($content);
            } else {
                $plainText = $content;
            }
        }

        if ($payload->getParts()) {
            foreach ($payload->getParts() as $part) {
                if ($part->getMimeType() === 'text/plain' && $part->getBody()->getData()) {
                    $plainText = $this->base64url_decode($part->getBody()->getData());
                }
                if ($part->getMimeType() === 'text/html' && $part->getBody()->getData()) {
                    $htmlContent = $this->base64url_decode($part->getBody()->getData());
                }
            }
        }

        // If we have HTML but no plain text, create plain text from HTML
        if (empty($plainText) && !empty($htmlContent)) {
            $plainText = strip_tags($htmlContent);
        }

        return [
            'plain_text' => $plainText,
            'html_content' => $htmlContent
        ];
    }

    protected function extractGmailAttachments($payload, $gmailService = null, $messageId = null): array
    {
        $attachments = [];
        
        Log::info('Extracting Gmail attachments', [
            'has_parts' => $payload->getParts() ? 'yes' : 'no',
            'parts_count' => $payload->getParts() ? count($payload->getParts()) : 0,
            'has_gmail_service' => $gmailService ? 'yes' : 'no',
            'message_id' => $messageId
        ]);
        
        if ($payload->getParts()) {
            foreach ($payload->getParts() as $index => $part) {
                Log::info('Processing Gmail part', [
                    'part_index' => $index,
                    'has_filename' => $part->getFilename() ? 'yes' : 'no',
                    'filename' => $part->getFilename(),
                    'mime_type' => $part->getMimeType(),
                    'has_body_data' => $part->getBody()->getData() ? 'yes' : 'no',
                    'body_size' => $part->getBody()->getSize(),
                    'has_attachment_id' => $part->getBody()->getAttachmentId() ? 'yes' : 'no',
                    'attachment_id' => $part->getBody()->getAttachmentId()
                ]);

                // Check if this part has a filename (indicating it's an attachment)
                if ($part->getFilename()) {
                    $attachment = [
                        'name' => $part->getFilename(),
                        'size' => $part->getBody()->getSize(),
                        'type' => $part->getMimeType(),
                    ];
                    
                    // Try to get attachment data
                    if ($part->getBody()->getData()) {
                        // Direct data available
                        $attachment['data'] = $this->base64url_decode($part->getBody()->getData());
                        Log::info('Gmail attachment data extracted from body', [
                            'filename' => $attachment['name'],
                            'size' => $attachment['size'],
                            'type' => $attachment['type'],
                            'data_length' => strlen($attachment['data'])
                        ]);
                    } elseif ($part->getBody()->getAttachmentId() && $gmailService && $messageId) {
                        // Attachment ID available - fetch the attachment separately
                        Log::info('Gmail attachment has ID, fetching separately', [
                            'filename' => $attachment['name'],
                            'attachment_id' => $part->getBody()->getAttachmentId(),
                            'size' => $attachment['size'],
                            'type' => $attachment['type']
                        ]);
                        
                        $attachmentData = $this->fetchGmailAttachment($gmailService, $messageId, $part->getBody()->getAttachmentId());
                        if ($attachmentData) {
                            $attachment['data'] = $attachmentData;
                            Log::info('Gmail attachment data fetched successfully', [
                                'filename' => $attachment['name'],
                                'data_length' => strlen($attachmentData)
                            ]);
                        } else {
                            Log::warning('Failed to fetch Gmail attachment data', [
                                'filename' => $attachment['name'],
                                'attachment_id' => $part->getBody()->getAttachmentId()
                            ]);
                            continue;
                        }
                    } else {
                        Log::warning('Gmail attachment has no data or attachment ID, or missing service/message ID', [
                            'filename' => $attachment['name'],
                            'size' => $attachment['size'],
                            'type' => $attachment['type'],
                            'has_gmail_service' => $gmailService ? 'yes' : 'no',
                            'has_message_id' => $messageId ? 'yes' : 'no'
                        ]);
                        continue;
                    }
                    
                    // Extract Content-ID for inline attachments
                    if ($part->getHeaders()) {
                        foreach ($part->getHeaders() as $header) {
                            if (strtolower($header->getName()) === 'content-id') {
                                $contentId = trim($header->getValue(), '<>');
                                $attachment['content_id'] = $contentId;
                                Log::info('Gmail attachment has content ID', [
                                    'filename' => $attachment['name'],
                                    'content_id' => $contentId
                                ]);
                                break;
                            }
                        }
                    }
                    
                    // Check if this is an inline attachment
                    if ($part->getBody()->getAttachmentId()) {
                        $attachment['is_inline'] = true;
                        Log::info('Gmail attachment is inline', [
                            'filename' => $attachment['name']
                        ]);
                    }
                    
                    $attachments[] = $attachment;
                }
            }
        }

        Log::info('Gmail attachment extraction completed', [
            'total_attachments_found' => count($attachments)
        ]);

        return $attachments;
    }

    protected function extractEmailFromHeader(string $header): string
    {
        if (preg_match('/<(.+?)>/', $header, $matches)) {
            return $matches[1];
        }
        return $header;
    }

    protected function extractNameFromHeader(string $header): string
    {
        if (preg_match('/^(.+?)\s*</', $header, $matches)) {
            return trim($matches[1], '"\' ');
        }
        return '';
    }

    protected function fetchOutlookEmails(EmailAccount $emailAccount): int
    {
        $client = new \GuzzleHttp\Client();
        $url = 'https://graph.microsoft.com/v1.0/me/mailFolders/inbox/messages?$filter=isRead eq false&$top=50';
        $processedCount = 0;
        $maxRetries = 1;
        $attempt = 0;
        
        do {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $emailAccount->access_token,
                    'Accept' => 'application/json',
                ],
                'http_errors' => false,
            ]);

            if ($response->getStatusCode() === 401 && $attempt < $maxRetries) {
                $attempt++;
                Log::info("Token expired for Outlook account {$emailAccount->email}. Refreshing.");
                try {
                    $refreshedData = $this->refreshMicrosoftToken($emailAccount);
                    $emailAccount->fill($refreshedData);
                    continue; // Retry the request with the new token
                } catch (\Exception $e) {
                    Log::error("Failed to refresh token during fetch for Outlook account {$emailAccount->email}. The account will be deactivated.", ['error' => $e->getMessage()]);
                    return $processedCount;
                }
            }
    
            if ($response->getStatusCode() !== 200) {
                Log::error('Failed to fetch emails from Outlook', [
                    'account_id' => $emailAccount->id,
                    'status_code' => $response->getStatusCode(),
                    'response' => (string) $response->getBody(),
                ]);
                return $processedCount;
            }
            
            $attempt = 0;
            $data = json_decode($response->getBody()->getContents(), true);
            
            foreach ($data['value'] as $message) {
                $this->processOutlookMessage($emailAccount, $message);
                $processedCount++;
    
                $client->request('PATCH', 'https://graph.microsoft.com/v1.0/me/messages/' . $message['id'], [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $emailAccount->access_token,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => ['isRead' => true],
                    'http_errors' => false,
                ]);
            }
            
            $url = $data['@odata.nextLink'] ?? null;
            
        } while ($url);
    
        return $processedCount;
    }

    protected function processOutlookMessage(EmailAccount $emailAccount, array $message): void
    {
        $bodyData = $this->extractOutlookBody($message['body']);
        
        $emailData = [
            'from_email' => $message['from']['emailAddress']['address'] ?? '',
            'from_name' => $message['from']['emailAddress']['name'] ?? '',
            'subject' => $message['subject'] ?? '',
            'body' => $bodyData['plain_text'],
            'html_content' => $bodyData['html_content'],
            'to_emails' => [$message['toRecipients'][0]['emailAddress']['address'] ?? ''],
            'cc_emails' => isset($message['ccRecipients']) ? array_map(function($cc) { return $cc['emailAddress']['address']; }, $message['ccRecipients']) : [],
            'attachments' => $this->extractOutlookAttachments($message),
            'message_id' => $message['id'],
            'received_at' => new \DateTime($message['receivedDateTime']),
        ];

        // Check email rules first
        if ($this->applyEmailRules($emailAccount, $emailData)) {
            return; // Email was handled by rules
        }

        // Create ticket
        $ticket = $this->createTicket($emailAccount, $emailData);

        // Apply AI rewriting if enabled
        if (config('services.gemini.enabled', false)) {
            $this->aiRewritingService->rewriteTicket($ticket);
        }
    }

    protected function extractOutlookBody(array $body): array
    {
        $content = $body['content'] ?? '';
        $contentType = $body['contentType'] ?? 'text';
        
        if ($contentType === 'html') {
            return [
                'plain_text' => strip_tags($content),
                'html_content' => $content
            ];
        }
        
        return [
            'plain_text' => $content,
            'html_content' => ''
        ];
    }

    protected function extractOutlookAttachments(array $message): array
    {
        $attachments = [];
        
        if (($message['hasAttachments'] ?? false) && isset($message['id'])) {
            // Fetch attachments if needed
            // This would require an additional API call to get attachment details
            // For now, we'll just note that attachments exist
            $attachments[] = [
                'name' => 'Attachment (details not fetched)',
                'size' => 0,
                'type' => 'unknown',
            ];
        }
        
        return $attachments;
    }

    protected function processEmail(EmailAccount $emailAccount, Message $message): void
    {
        $emailData = [
            'from_email' => $message->getFrom()[0]->mail ?? '',
            'from_name' => $message->getFrom()[0]->personal ?? '',
            'subject' => $message->getSubject() ?? '',
            'body' => $message->getTextBody() ?? strip_tags($message->getHTMLBody() ?? ''),
            'html_content' => $message->getHTMLBody() ?? '',
            'to_emails' => $message->getTo() ? array_map(function ($to) { return $to->mail; }, $message->getTo()) : [],
            'cc_emails' => $message->getCc() ? array_map(function ($cc) { return $cc->mail; }, $message->getCc()) : [],
            'attachments' => $this->extractAttachments($message),
            'message_id' => $message->getMessageId(),
            'received_at' => $message->getDate(),
        ];

        // Check email rules first
        if ($this->applyEmailRules($emailAccount, $emailData)) {
            return; // Email was handled by rules
        }

        // Create ticket
        $ticket = $this->createTicket($emailAccount, $emailData);

        // Apply AI rewriting if enabled
        if (config('services.gemini.enabled', false)) {
            $this->aiRewritingService->rewriteTicket($ticket);
        }
    }

    protected function extractAttachments(Message $message): array
    {
        $attachments = [];
        
        Log::info('Extracting IMAP attachments', [
            'message_subject' => $message->getSubject(),
            'has_attachments' => $message->getAttachments() ? 'yes' : 'no',
            'attachments_count' => $message->getAttachments() ? count($message->getAttachments()) : 0
        ]);
        
        foreach ($message->getAttachments() as $index => $attachment) {
            Log::info('Processing IMAP attachment', [
                'attachment_index' => $index,
                'name' => $attachment->getName(),
                'size' => $attachment->getSize(),
                'mime_type' => $attachment->getMimeType(),
                'is_inline' => $attachment->isInline(),
                'has_content' => $attachment->getContent() ? 'yes' : 'no',
                'content_length' => $attachment->getContent() ? strlen($attachment->getContent()) : 0
            ]);

            $attachmentData = [
                'name' => $attachment->getName(),
                'size' => $attachment->getSize(),
                'type' => $attachment->getMimeType(),
                'data' => $attachment->getContent(), // Add the actual file content
            ];
            
            // Extract Content-ID for inline attachments
            $contentId = $attachment->getContentId();
            if ($contentId) {
                $attachmentData['content_id'] = trim($contentId, '<>');
                Log::info('IMAP attachment has content ID', [
                    'name' => $attachment->getName(),
                    'content_id' => $attachmentData['content_id']
                ]);
            }
            
            // Check if this is an inline attachment
            if ($attachment->isInline()) {
                $attachmentData['is_inline'] = true;
                Log::info('IMAP attachment is inline', [
                    'name' => $attachment->getName()
                ]);
            }
            
            Log::info('IMAP attachment data prepared', [
                'name' => $attachmentData['name'],
                'size' => $attachmentData['size'],
                'type' => $attachmentData['type'],
                'data_length' => strlen($attachmentData['data']),
                'has_content_id' => isset($attachmentData['content_id']),
                'is_inline' => isset($attachmentData['is_inline'])
            ]);
            
            $attachments[] = $attachmentData;
        }
        
        Log::info('IMAP attachment extraction completed', [
            'total_attachments_found' => count($attachments)
        ]);
        
        return $attachments;
    }

    protected function applyEmailRules(EmailAccount $emailAccount, array $emailData): bool
    {
        $rules = $emailAccount->emailRules()->active()->byPriority()->get();

        foreach ($rules as $rule) {
            if ($rule->matchesCondition($emailData)) {
                $this->executeRule($rule, $emailData);
                return true; // Email was handled
            }
        }

        return false;
    }

    protected function executeRule(EmailRule $rule, array $emailData): void
    {
        switch ($rule->action) {
            case 'auto_reply':
                $this->sendAutoReply($rule, $emailData);
                break;
            case 'delete':
                // Mark for deletion (implement based on your email provider)
                break;
            case 'move_to_folder':
                // Move to specific folder (implement based on your email provider)
                break;
            case 'mark_as_read':
                // Mark as read (implement based on your email provider)
                break;
            case 'forward':
                $this->forwardEmail($rule, $emailData);
                break;
            case 'create_task':
                $this->createTaskFromEmail($rule, $emailData);
                break;
        }
    }

    protected function sendAutoReply(EmailRule $rule, array $emailData): void
    {
        // Implementation for sending auto-reply
        Log::info('Sending auto-reply for rule: ' . $rule->name);
    }

    protected function forwardEmail(EmailRule $rule, array $emailData): void
    {
        // Implementation for forwarding email
        Log::info('Forwarding email to: ' . $rule->forward_to);
    }

    protected function createTaskFromEmail(EmailRule $rule, array $emailData): void
    {
        $taskData = [
            'title' => $emailData['subject'],
            'notes' => $emailData['body'],
            'priority' => $this->determinePriority($emailData),
        ];

        $this->googleTasksService->createTask($taskData);
    }

    protected function createTicket(EmailAccount $emailAccount, array $emailData): Ticket
    {
        $ticket = Ticket::create([
            'user_id' => $emailAccount->user_id,
            'email_account_id' => $emailAccount->id,
            'subject' => $emailData['subject'],
            'original_content' => $emailData['body'],
            'html_content' => $emailData['html_content'] ?? '',
            'from_email' => $emailData['from_email'],
            'from_name' => $emailData['from_name'],
            'to_emails' => $emailData['to_emails'],
            'cc_emails' => $emailData['cc_emails'],
            'message_id' => $emailData['message_id'],
            'attachment_metadata' => [], // Keep empty array for backward compatibility
            'received_at' => $emailData['received_at'],
            'priority' => $this->determinePriority($emailData),
        ]);

        // Store attachments using the Attachment model
        if (!empty($emailData['attachments'])) {
            Log::info('Processing attachments for ticket', [
                'ticket_id' => $ticket->id,
                'attachment_count' => count($emailData['attachments']),
                'account_id' => $emailAccount->id,
                'account_email' => $emailAccount->email
            ]);

            foreach ($emailData['attachments'] as $index => $attachmentData) {
                try {
                    Log::info('Processing attachment', [
                        'ticket_id' => $ticket->id,
                        'attachment_index' => $index,
                        'attachment_name' => $attachmentData['name'],
                        'attachment_size' => isset($attachmentData['data']) ? strlen($attachmentData['data']) : 'unknown',
                        'attachment_type' => $attachmentData['type'],
                        'has_content_id' => isset($attachmentData['content_id']),
                        'is_inline' => isset($attachmentData['is_inline']) ? $attachmentData['is_inline'] : false
                    ]);

                    $metadata = [];
                    if (isset($attachmentData['content_id'])) {
                        $metadata['content_id'] = $attachmentData['content_id'];
                        Log::info('Attachment has content ID', [
                            'ticket_id' => $ticket->id,
                            'attachment_name' => $attachmentData['name'],
                            'content_id' => $attachmentData['content_id']
                        ]);
                    }
                    if (isset($attachmentData['is_inline'])) {
                        $metadata['is_inline'] = $attachmentData['is_inline'];
                        Log::info('Attachment is inline', [
                            'ticket_id' => $ticket->id,
                            'attachment_name' => $attachmentData['name']
                        ]);
                    }

                    // Check if we have the actual file data
                    if (!isset($attachmentData['data']) || empty($attachmentData['data'])) {
                        Log::warning('Attachment missing data', [
                            'ticket_id' => $ticket->id,
                            'attachment_name' => $attachmentData['name'],
                            'has_data' => isset($attachmentData['data']),
                            'data_length' => isset($attachmentData['data']) ? strlen($attachmentData['data']) : 0
                        ]);
                        continue; // Skip this attachment if no data
                    }

                    $attachment = \App\Models\Attachment::storeContent(
                        $attachmentData['data'],
                        $attachmentData['name'],
                        $attachmentData['type'],
                        $ticket,
                        $metadata
                    );

                    Log::info('Attachment stored successfully', [
                        'ticket_id' => $ticket->id,
                        'attachment_id' => $attachment->id,
                        'attachment_name' => $attachmentData['name'],
                        'stored_name' => $attachment->stored_name,
                        'file_size' => strlen($attachmentData['data']),
                        'stored_size' => $attachment->size,
                        'file_exists' => $attachment->exists(),
                        'storage_path' => $attachment->path
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to store attachment', [
                        'ticket_id' => $ticket->id,
                        'attachment_index' => $index,
                        'attachment_name' => $attachmentData['name'],
                        'error' => $e->getMessage(),
                        'error_trace' => $e->getTraceAsString()
                    ]);
                }
            }

            Log::info('Finished processing attachments for ticket', [
                'ticket_id' => $ticket->id,
                'total_attachments_processed' => count($emailData['attachments'])
            ]);
        } else {
            Log::info('No attachments to process for ticket', [
                'ticket_id' => $ticket->id,
                'account_id' => $emailAccount->id
            ]);
        }

        // Fire event for new ticket creation
        event(new \App\Events\TicketCreated($ticket));

        // Send notification to user
        $user = $emailAccount->user;
        $user->notify(new \App\Notifications\NewTicketNotification($ticket));

        return $ticket;
    }

    protected function determinePriority(array $emailData): string
    {
        $subject = strtolower($emailData['subject']);
        $body = strtolower($emailData['body']);

        $urgentKeywords = ['urgent', 'asap', 'emergency', 'critical', 'immediate'];
        $highKeywords = ['important', 'priority', 'high'];

        foreach ($urgentKeywords as $keyword) {
            if (str_contains($subject, $keyword) || str_contains($body, $keyword)) {
                return 'urgent';
            }
        }

        foreach ($highKeywords as $keyword) {
            if (str_contains($subject, $keyword) || str_contains($body, $keyword)) {
                return 'high';
            }
        }

        return 'medium';
    }

    /**
     * Decode base64url encoded string
     */
    protected function base64url_decode(string $data): string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    /**
     * Encode string to base64url
     */
    protected function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Send email reply
     */
    public function sendEmail(EmailAccount $emailAccount, array $emailData): bool
    {
        try {
            if ($emailAccount->type === 'gmail') {
                return $this->sendGmailEmail($emailAccount, $emailData);
            } elseif ($emailAccount->type === 'outlook') {
                return $this->sendOutlookEmail($emailAccount, $emailData);
            } else {
                return $this->sendImapEmail($emailAccount, $emailData);
            }
        } catch (\Exception $e) {
            Log::error('Error sending email: ' . $e->getMessage(), [
                'account_id' => $emailAccount->id,
                'email' => $emailAccount->email,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function sendGmailEmail(EmailAccount $emailAccount, array $emailData): bool
    {
        // Check if token is expired or about to expire (within 2 minutes)
        if ($emailAccount->refresh_token && $emailAccount->token_expires_at && 
            now()->gte($emailAccount->token_expires_at->subMinutes(2))) {
            $refreshedData = $this->refreshGmailToken($emailAccount);
            $emailAccount->fill($refreshedData);
        }

        // Create Google Client for Gmail API
        $client = new \Google_Client();
        $client->setClientId(config('services.google-email.client_id'));
        $client->setClientSecret(config('services.google-email.client_secret'));
        $client->setAccessToken($emailAccount->access_token);
        $client->setScopes([
            'https://www.googleapis.com/auth/gmail.send',
            'https://www.googleapis.com/auth/gmail.compose'
        ]);

        // Create Gmail service
        $gmailService = new \Google_Service_Gmail($client);

        // Build email message
        $message = $this->buildGmailMessage($emailData);

        // Send email with retry logic for expired tokens
        $maxRetries = 1;
        $attempt = 0;

        do {
            try {
                $sentMessage = $gmailService->users_messages->send('me', $message);

                Log::info("Email sent via Gmail: {$sentMessage->getId()}", [
                    'account_id' => $emailAccount->id,
                    'to' => $emailData['to']
                ]);

                return true;
            } catch (\Google\Service\Exception $e) {
                if ($e->getCode() === 401 && $attempt < $maxRetries) {
                    $attempt++;
                    Log::info("Token expired for Gmail account {$emailAccount->email} during send. Refreshing.");
                    try {
                        $refreshedData = $this->refreshGmailToken($emailAccount);
                        $emailAccount->fill($refreshedData);
                        $client->setAccessToken($emailAccount->access_token);
                        $gmailService = new \Google_Service_Gmail($client);
                        continue;
                    } catch (\Exception $refreshException) {
                        Log::error("Failed to refresh token during send for Gmail account {$emailAccount->email}. The account will be deactivated.", ['error' => $refreshException->getMessage()]);
                        throw $refreshException;
                    }
                } else {
                    Log::error("Google API error while sending email for {$emailAccount->email}", ['error' => $e->getMessage()]);
                    throw $e;
                }
            }
        } while ($attempt < $maxRetries);

        return false;
    }

    protected function sendOutlookEmail(EmailAccount $emailAccount, array $emailData): bool
    {
        if ($emailAccount->refresh_token && $emailAccount->token_expires_at && now()->gte($emailAccount->token_expires_at->subMinutes(2))) {
            $refreshedData = $this->refreshMicrosoftToken($emailAccount);
            $emailAccount->fill($refreshedData);
        }

        // Build email message for Microsoft Graph API
        $message = $this->buildOutlookMessage($emailData);

        // Send email via Microsoft Graph API
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $emailAccount->access_token,
            'Content-Type' => 'application/json',
        ])->post('https://graph.microsoft.com/v1.0/me/sendMail', [
            'message' => $message,
            'saveToSentItems' => true
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to send email via Microsoft Graph: ' . $response->body());
        }

        Log::info("Email sent via Outlook", [
            'account_id' => $emailAccount->id,
            'to' => $emailData['to']
        ]);

        return true;
    }

    protected function sendImapEmail(EmailAccount $emailAccount, array $emailData): bool
    {
        // For IMAP accounts, we'll use Laravel's built-in mail functionality
        // This is a simplified implementation - in production you'd use the SMTP settings
        
        Log::info("Email sent via IMAP", [
            'account_id' => $emailAccount->id,
            'to' => $emailData['to']
        ]);

        return true;
    }

    protected function buildGmailMessage(array $emailData): \Google_Service_Gmail_Message
    {
        $boundary = uniqid();
        $message = "MIME-Version: 1.0\r\n";
        $message .= "To: " . $emailData['to'] . "\r\n";
        
        if (!empty($emailData['cc'])) {
            $message .= "Cc: " . implode(', ', $emailData['cc']) . "\r\n";
        }
        
        $message .= "Subject: " . $emailData['subject'] . "\r\n";
        $message .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n\r\n";
        
        // Email body
        $message .= "--{$boundary}\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
        
        // Add signature to body if available
        $body = $emailData['body'];
        if (!empty($emailData['signature_text'])) {
            $body .= "\r\n\r\n" . $emailData['signature_text'];
        }
        
        $message .= $body . "\r\n\r\n";
        
        // Add signature image if available
        if (!empty($emailData['signature_image_path'])) {
            $message .= "--{$boundary}\r\n";
            $message .= "Content-Type: image/" . pathinfo($emailData['signature_image_path'], PATHINFO_EXTENSION) . "; name=\"signature." . pathinfo($emailData['signature_image_path'], PATHINFO_EXTENSION) . "\"\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n";
            $message .= "Content-Disposition: inline; filename=\"signature." . pathinfo($emailData['signature_image_path'], PATHINFO_EXTENSION) . "\"\r\n\r\n";
            $message .= base64_encode(file_get_contents(storage_path('app/public/' . $emailData['signature_image_path']))) . "\r\n\r\n";
        }
        
        // Attachments
        foreach ($emailData['attachments'] as $attachment) {
            $message .= "--{$boundary}\r\n";
            $message .= "Content-Type: {$attachment['mime']}; name=\"{$attachment['name']}\"\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n";
            $message .= "Content-Disposition: attachment; filename=\"{$attachment['name']}\"\r\n\r\n";
            $message .= base64_encode(file_get_contents($attachment['path'])) . "\r\n\r\n";
        }
        
        $message .= "--{$boundary}--";
        
        $gmailMessage = new \Google_Service_Gmail_Message();
        $gmailMessage->setRaw($this->base64url_encode($message));
        
        return $gmailMessage;
    }

    protected function buildOutlookMessage(array $emailData): array
    {
        // Add signature to body if available
        $body = $emailData['body'];
        if (!empty($emailData['signature_text'])) {
            $body .= "\r\n\r\n" . $emailData['signature_text'];
        }

        $message = [
            'subject' => $emailData['subject'],
            'body' => [
                'contentType' => 'Text',
                'content' => $body
            ],
            'toRecipients' => [
                ['emailAddress' => ['address' => $emailData['to']]]
            ]
        ];

        if (!empty($emailData['cc'])) {
            $message['ccRecipients'] = [];
            foreach ($emailData['cc'] as $cc) {
                $message['ccRecipients'][] = ['emailAddress' => ['address' => $cc]];
            }
        }

        // Add signature image if available
        if (!empty($emailData['signature_image_path'])) {
            if (!isset($message['attachments'])) {
                $message['attachments'] = [];
            }
            $message['attachments'][] = [
                '@odata.type' => '#microsoft.graph.fileAttachment',
                'name' => 'signature.' . pathinfo($emailData['signature_image_path'], PATHINFO_EXTENSION),
                'contentType' => 'image/' . pathinfo($emailData['signature_image_path'], PATHINFO_EXTENSION),
                'contentBytes' => base64_encode(file_get_contents(storage_path('app/public/' . $emailData['signature_image_path']))),
                'isInline' => true,
                'contentId' => 'signature'
            ];
        }

        // Add attachments if any
        if (!empty($emailData['attachments'])) {
            if (!isset($message['attachments'])) {
                $message['attachments'] = [];
            }
            foreach ($emailData['attachments'] as $attachment) {
                $message['attachments'][] = [
                    '@odata.type' => '#microsoft.graph.fileAttachment',
                    'name' => $attachment['name'],
                    'contentType' => $attachment['mime'],
                    'contentBytes' => base64_encode(file_get_contents($attachment['path']))
                ];
            }
        }

        return $message;
    }

    protected function refreshGmailToken(EmailAccount $emailAccount): array
    {
        Log::info('Refreshing Gmail token', ['account_id' => $emailAccount->id]);
        
        // Check if refresh token exists
        if (!$emailAccount->refresh_token) {
            $this->deactivateAccount($emailAccount, 'No refresh token available');
            throw new \Exception("Gmail account '{$emailAccount->email}' requires re-authentication (no refresh token).");
        }
        
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google-email.client_id'),
            'client_secret' => config('services.google-email.client_secret'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $emailAccount->refresh_token,
        ]);

        if ($response->failed()) {
            $errorData = $response->json();
            if (isset($errorData['error']) && $errorData['error'] === 'invalid_grant') {
                $this->deactivateAccount($emailAccount, 'Invalid refresh token');
                throw new \Exception("Gmail account '{$emailAccount->email}' requires re-authentication.");
            }
            Log::error('Failed to refresh Google token', ['account_id' => $emailAccount->id, 'response' => $response->body()]);
            throw new \Exception('Failed to refresh Google token: ' . $response->body());
        }

        $data = $response->json();

        $updateData = [
            'access_token' => $data['access_token'],
            'token_expires_at' => now()->addSeconds($data['expires_in']),
        ];

        $emailAccount->update($updateData);
        
        Log::info('Successfully refreshed Google token', ['account_id' => $emailAccount->id]);
        return $updateData;
    }

    protected function refreshMicrosoftToken(EmailAccount $emailAccount): array
    {
        Log::info('Refreshing Microsoft token', ['account_id' => $emailAccount->id, 'token_expires_at' => $emailAccount->token_expires_at]);
        
        // Check if refresh token exists
        if (!$emailAccount->refresh_token) {
            $this->deactivateAccount($emailAccount, 'No refresh token available');
            throw new \Exception("Microsoft account '{$emailAccount->email}' requires re-authentication (no refresh token).");
        }
        
        $response = Http::asForm()->post('https://login.microsoftonline.com/' . config('services.microsoft.tenant', 'common') . '/oauth2/v2.0/token', [
            'client_id' => config('services.microsoft.client_id'),
            'client_secret' => config('services.microsoft.client_secret'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $emailAccount->refresh_token,
            'scope' => 'offline_access https://graph.microsoft.com/Mail.Read https://graph.microsoft.com/Mail.Send',
            'redirect_uri' => config('services.microsoft.redirect'),
        ]);

        if ($response->failed()) {
            $errorData = $response->json();
            if (isset($errorData['error']) && $errorData['error'] === 'invalid_grant') {
                $this->deactivateAccount($emailAccount, 'Invalid refresh token');
                throw new \Exception("Microsoft account '{$emailAccount->email}' requires re-authentication.");
            }
            Log::error('Failed to refresh Microsoft token', ['account_id' => $emailAccount->id, 'response' => $response->body()]);
            throw new \Exception('Failed to refresh Microsoft token: ' . $response->body());
        }

        $data = $response->json();
        Log::info('Microsoft token refresh response received', ['account_id' => $emailAccount->id, 'data' => $data]);

        if (!isset($data['access_token'])) {
            Log::error('Microsoft token refresh response did not include access_token', [
                'account_id' => $emailAccount->id,
                'response' => $data
            ]);
            throw new \Exception('Failed to refresh Microsoft token: Invalid response from server.');
        }

        $updateData = [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $emailAccount->refresh_token,
            'token_expires_at' => now()->addSeconds($data['expires_in']),
        ];

        $emailAccount->update($updateData);
        
        Log::info('Successfully refreshed Microsoft token', ['account_id' => $emailAccount->id, 'new_token_expires_at' => $emailAccount->fresh()->token_expires_at]);
        return $updateData;
    }

    protected function deactivateAccount(EmailAccount $account, string $reason)
    {
        $account->update([
            'is_active' => false,
            'access_token' => null,
            'refresh_token' => null,
            'token_expires_at' => null,
        ]);
        Log::warning("Deactivated account {$account->email}", [
            'account_id' => $account->id,
            'reason' => $reason
        ]);
    }

    protected function isScopeInsufficientError(\Exception $e): bool
    {
        // Check if this is a Google API scope error
        if ($e instanceof \Google\Service\Exception) {
            $errorMessage = $e->getMessage();
            return $e->getCode() === 403 && 
                   (strpos($errorMessage, 'insufficient authentication scopes') !== false ||
                    strpos($errorMessage, 'ACCESS_TOKEN_SCOPE_INSUFFICIENT') !== false);
        }
        
        return false;
    }

    protected function fetchGmailAttachment($gmailService, $messageId, $attachmentId): ?string
    {
        try {
            Log::info('Fetching Gmail attachment', [
                'message_id' => $messageId,
                'attachment_id' => $attachmentId
            ]);
            
            $attachment = $gmailService->users_messages_attachments->get('me', $messageId, $attachmentId);
            
            if ($attachment && $attachment->getData()) {
                $data = $this->base64url_decode($attachment->getData());
                Log::info('Gmail attachment fetched successfully', [
                    'message_id' => $messageId,
                    'attachment_id' => $attachmentId,
                    'data_length' => strlen($data)
                ]);
                return $data;
            } else {
                Log::warning('Gmail attachment fetch returned no data', [
                    'message_id' => $messageId,
                    'attachment_id' => $attachmentId
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch Gmail attachment', [
                'message_id' => $messageId,
                'attachment_id' => $attachmentId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
} 