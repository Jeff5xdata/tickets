<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\EmailAccount;
use App\Models\Reply;
use App\Models\Note;
use App\Models\Attachment;
use App\Services\AiRewritingService;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    use AuthorizesRequests;
    
    protected AiRewritingService $aiRewritingService;
    protected EmailService $emailService;

    public function __construct(
        AiRewritingService $aiRewritingService,
        EmailService $emailService
    ) {
        $this->aiRewritingService = $aiRewritingService;
        $this->emailService = $emailService;
    }

    public function fetchAllEmails()
    {
        $this->authorize('viewAny', Ticket::class);
        $accounts = auth()->user()->emailAccounts()->where('is_active', true)->get();
        $totalFetched = 0;
        $failedAccounts = [];

        foreach ($accounts as $account) {
            try {
                $totalFetched += $this->emailService->fetchEmails($account);
            } catch (\Exception $e) {
                Log::error("Failed to fetch emails for account {$account->email}", [
                    'account_id' => $account->id,
                    'error' => $e->getMessage(),
                ]);
                if (str_contains($e->getMessage(), 'requires re-authentication')) {
                    $failedAccounts[] = $account->email;
                }
            }
        }

        $message = "Email fetch process completed.";
        if (!empty($failedAccounts)) {
            $message .= " However, some accounts require re-authentication.";
        }

        return response()->json([
            'message' => $message,
            'email_count' => $totalFetched,
            'failed_accounts' => $failedAccounts,
        ]);
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Ticket::class);
        $query = auth()->user()->tickets()->with(['emailAccount']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        // Filter by email account
        if ($request->has('email_account') && $request->email_account !== 'all') {
            $query->where('email_account_id', $request->email_account);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('original_content', 'like', "%{$search}%")
                  ->orWhere('from_email', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderBy('received_at', 'desc')->paginate(20);
        $emailAccounts = auth()->user()->emailAccounts()->where('is_active', true)->get();

        // Add cache control headers to prevent aggressive caching
        if (!app()->environment('local')) {
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
        }

        return view('tickets.index', compact('tickets', 'emailAccounts'));
    }

    public function create()
    {
        $this->authorize('create', Ticket::class);
        $emailAccounts = auth()->user()->emailAccounts()
            ->where('is_active', true)
            ->where('type', '!=', 'google-tasks')
            ->get();
        $recipientEmails = auth()->user()->tickets()
            ->whereNotNull('from_email')
            ->pluck('from_email')
            ->unique()
            ->values();
        $aiEnabled = config('services.gemini.enabled', false) && config('services.gemini.api_key');
        return view('tickets.create', compact('emailAccounts', 'recipientEmails', 'aiEnabled'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Ticket::class);
        $validated = $request->validate([
            'from_account' => 'required|exists:email_accounts,id',
            'to' => 'required|email',
            'cc' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240',
        ]);
        $emailAccount = EmailAccount::where('user_id', auth()->id())->findOrFail($validated['from_account']);
        try {
            $emailData = [
                'to' => $validated['to'],
                'cc' => $validated['cc'] ? array_filter(array_map('trim', explode(',', $validated['cc']))) : [],
                'subject' => $validated['subject'],
                'body' => $validated['message'],
                'attachments' => [],
                'signature_text' => $emailAccount->signature_text,
                'signature_image_path' => $emailAccount->signature_image_path,
            ];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $emailData['attachments'][] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $file->getRealPath(),
                        'mime' => $file->getMimeType(),
                    ];
                }
            }
            // Send email using the email service
            Log::info('Attempting to send email', [
                'ticket_id' => null,
                'email_account_id' => $emailAccount->id,
                'to' => $emailData['to'],
                'subject' => $emailData['subject'],
                'has_attachments' => !empty($emailData['attachments'])
            ]);
            $this->emailService->sendEmail($emailAccount, $emailData);
            Log::info('Email sent successfully', [
                'ticket_id' => null,
                'email_account_id' => $emailAccount->id
            ]);
            $ticket = Ticket::create([
                'user_id' => auth()->id(),
                'email_account_id' => $emailAccount->id,
                'subject' => $validated['subject'],
                'original_content' => $validated['message'],
                'from_email' => $emailAccount->email,
                'from_name' => $emailAccount->from_name,
                'to_email' => $validated['to'],
                'status' => 'resolved',
                'priority' => 'medium',
                'received_at' => now(),
                'responded_at' => now(),
            ]);
            // Store attachments in the new table
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    Attachment::storeFile($file, $ticket);
                }
            }
            // Fire event for new ticket creation
            event(new \App\Events\TicketCreated($ticket));
            // Send notification to user
            auth()->user()->notify(new \App\Notifications\NewTicketNotification($ticket));
            return redirect()->route('tickets.index')->with('success', 'Ticket created and email sent successfully.');
        } catch (\Exception $e) {
            Log::error('Error sending new ticket email: ' . $e->getMessage(), ['user_id' => auth()->id(), 'error' => $e->getMessage()]);
            return back()->with('error', 'Failed to send email: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Ticket $ticket): View
    {
        $this->authorize('view', $ticket);

        $ticket->load(['emailAccount', 'googleTasks', 'replies.user', 'notes.user', 'attachments']);

        // Get related tickets from the same sender
        $relatedTickets = auth()->user()->tickets()
            ->where('from_email', $ticket->from_email)
            ->where('id', '!=', $ticket->id)
            ->orderBy('received_at', 'desc')
            ->limit(10)
            ->get();

        // Check if AI rewriting is enabled
        $aiEnabled = config('services.gemini.enabled', false) && config('services.gemini.api_key');

        return view('tickets.show', compact('ticket', 'relatedTickets', 'aiEnabled'));
    }

    public function edit(Ticket $ticket): View
    {
        $this->authorize('update', $ticket);

        $emailAccounts = auth()->user()->emailAccounts()
            ->where('is_active', true)
            ->where('type', '!=', 'google-tasks')
            ->get();

        $aiEnabled = config('services.gemini.enabled', false) && config('services.gemini.api_key');

        return view('tickets.edit', compact('ticket', 'emailAccounts', 'aiEnabled'));
    }

    public function update(Request $request, Ticket $ticket): JsonResponse|View|\Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'subject' => 'sometimes|string|max:255',
            'from_email' => 'sometimes|email',
            'from_name' => 'sometimes|string|max:255',
            'to_email' => 'sometimes|email',
            'to_emails' => 'sometimes|string',
            'cc_emails' => 'sometimes|string',
            'bcc_emails' => 'sometimes|string',
            'status' => 'sometimes|in:new,in_progress,waiting,resolved,closed',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'original_content' => 'sometimes|string',
            'html_content' => 'sometimes|string',
            'email_account_id' => 'sometimes|exists:email_accounts,id',
            'received_at' => 'sometimes|date',
            'message_id' => 'sometimes|string',
            'thread_id' => 'sometimes|string',
            'attachment_metadata' => 'sometimes|string',
        ]);

        // Handle JSON fields
        if (isset($validated['to_emails'])) {
            $validated['to_emails'] = json_decode($validated['to_emails'], true);
        }
        if (isset($validated['cc_emails'])) {
            $validated['cc_emails'] = json_decode($validated['cc_emails'], true);
        }
        if (isset($validated['bcc_emails'])) {
            $validated['bcc_emails'] = json_decode($validated['bcc_emails'], true);
        }
        if (isset($validated['attachment_metadata'])) {
            $validated['attachment_metadata'] = json_decode($validated['attachment_metadata'], true);
        }

        $ticket->update($validated);

        // For PATCH requests, always return JSON (they are typically AJAX)
        if ($request->method() === 'PATCH') {
            return response()->json([
                'message' => 'Ticket updated successfully',
                'ticket' => $ticket->fresh()
            ])->header('Access-Control-Allow-Origin', '*')
              ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
              ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, X-Requested-With, X-CSRF-TOKEN');
        }

        // If it's a regular form submission, redirect with success message
        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket updated successfully');
    }

    public function destroy(Ticket $ticket): JsonResponse
    {
        $this->authorize('delete', $ticket);

        try {
            $ticket->delete();

            return response()->json([
                'message' => 'Ticket deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendReply(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('update', $ticket);

        // Log the incoming request data for debugging
        Log::info('Reply request received', [
            'ticket_id' => $ticket->id,
            'request_data' => $request->all(),
            'files' => $request->hasFile('attachments') ? 'Has attachments' : 'No attachments',
            'include_original_raw' => $request->input('include_original'),
            'reply_to_all_raw' => $request->input('reply_to_all'),
        ]);

        $validated = $request->validate([
            'to' => 'required|email',
            'cc' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
            'include_original' => 'nullable|in:0,1,true,false,on',
            'reply_to_all' => 'nullable|in:0,1,true,false,on',
        ]);

        // Convert string boolean values to actual booleans
        $includeOriginalValue = $validated['include_original'] ?? false;
        $replyToAllValue = $validated['reply_to_all'] ?? false;
        
        // Handle 'on' value from checkboxes
        $validated['include_original'] = in_array($includeOriginalValue, ['1', 'true', 'on'], true);
        $validated['reply_to_all'] = in_array($replyToAllValue, ['1', 'true', 'on'], true);
        
        Log::info('Checkbox values processed', [
            'ticket_id' => $ticket->id,
            'include_original_raw' => $includeOriginalValue,
            'include_original_processed' => $validated['include_original'],
            'reply_to_all_raw' => $replyToAllValue,
            'reply_to_all_processed' => $validated['reply_to_all'],
        ]);

        try {
            // Get the email account associated with this ticket
            $emailAccount = $ticket->emailAccount;
            
            if (!$emailAccount) {
                Log::error('No email account found for ticket', ['ticket_id' => $ticket->id]);
                return response()->json([
                    'message' => 'No email account found for this ticket. Please configure an email account first.'
                ], 422);
            }

            // Check if email account is active
            if (!$emailAccount->is_active) {
                Log::error('Email account is not active', [
                    'ticket_id' => $ticket->id,
                    'email_account_id' => $emailAccount->id
                ]);
                return response()->json([
                    'message' => 'The email account for this ticket is not active. Please activate it first.'
                ], 422);
            }

            // Check if email account has required configuration
            $hasValidCredentials = false;
            
            if (in_array($emailAccount->type, ['gmail', 'outlook'])) {
                // OAuth accounts need access token and refresh token
                $hasValidCredentials = !empty($emailAccount->access_token) && !empty($emailAccount->refresh_token);
            } else {
                // IMAP accounts need email and password
                $hasValidCredentials = !empty($emailAccount->email) && !empty($emailAccount->password);
            }
            
            if (!$emailAccount->email || !$hasValidCredentials) {
                Log::error('Email account missing required configuration', [
                    'ticket_id' => $ticket->id,
                    'email_account_id' => $emailAccount->id,
                    'account_type' => $emailAccount->type,
                    'has_email' => !empty($emailAccount->email),
                    'has_password' => !empty($emailAccount->password),
                    'has_access_token' => !empty($emailAccount->access_token),
                    'has_refresh_token' => !empty($emailAccount->refresh_token),
                    'has_valid_credentials' => $hasValidCredentials
                ]);
                return response()->json([
                    'message' => 'The email account is missing required configuration. Please configure it properly.'
                ], 422);
            }

            // Check if email account is configured for sending emails
            if (!in_array($emailAccount->type, ['gmail', 'outlook'])) {
                Log::error('Email account not configured for sending emails', [
                    'ticket_id' => $ticket->id,
                    'email_account_id' => $emailAccount->id,
                    'account_type' => $emailAccount->type
                ]);
                return response()->json([
                    'message' => 'The email account is configured for ' . $emailAccount->type . ' but not for sending emails. Please configure a Gmail or Outlook account for sending emails.'
                ], 422);
            }

            // Prepare email data
            $emailData = [
                'to' => $validated['to'],
                'cc' => $validated['cc'] ? array_filter(array_map('trim', explode(',', $validated['cc']))) : [],
                'subject' => $validated['subject'],
                'body' => $validated['message'],
                'attachments' => [],
                'signature_text' => $emailAccount->signature_text,
                'signature_image_path' => $emailAccount->signature_image_path,
            ];

            // Handle file attachments
            $attachmentNames = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $emailData['attachments'][] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $file->getRealPath(),
                        'mime' => $file->getMimeType(),
                    ];
                    $attachmentNames[] = $file->getClientOriginalName();
                }
            }

            // Send email using the email service
            Log::info('Attempting to send email', [
                'ticket_id' => $ticket->id,
                'email_account_id' => $emailAccount->id,
                'to' => $emailData['to'],
                'subject' => $emailData['subject'],
                'has_attachments' => !empty($emailData['attachments'])
            ]);
            
            $this->emailService->sendEmail($emailAccount, $emailData);
            
            Log::info('Email sent successfully', [
                'ticket_id' => $ticket->id,
                'email_account_id' => $emailAccount->id
            ]);

            // Save reply to database
            $reply = Reply::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'email_account_id' => $emailAccount->id,
                'message' => $validated['message'],
                'subject' => $validated['subject'],
                'to_email' => $validated['to'],
                'cc_emails' => $validated['cc'] ? array_filter(array_map('trim', explode(',', $validated['cc']))) : null,
                'bcc_emails' => null,
                'include_original' => $validated['include_original'],
                'reply_to_all' => $validated['reply_to_all'],
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // Store attachments in the new table
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    Attachment::storeFile($file, $reply);
                }
            }

            // Update ticket status to responded
            $ticket->update([
                'status' => 'in_progress',
                'responded_at' => now(),
            ]);

            return response()->json([
                'message' => 'Reply sent successfully',
                'reply' => $reply
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending email reply: ' . $e->getMessage(), [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error sending reply: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rewriteMessage(Request $request): JsonResponse
    {
        $this->authorize('create', Ticket::class);

        $validated = $request->validate([
            'message' => 'required|string',
            'subject' => 'required|string|max:255',
        ]);

        try {
            $rewrittenContent = $this->aiRewritingService->rewriteEmailContent(
                $validated['message'], 
                $validated['subject']
            );
            
            return response()->json([
                'message' => 'Message rewritten successfully',
                'rewritten_content' => $rewrittenContent
            ]);
        } catch (\Exception $e) {
            Log::error('Error rewriting message with AI: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to rewrite message: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateResponse(Ticket $ticket, Request $request): JsonResponse
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'context' => 'nullable|string',
        ]);

        try {
            $response = $this->aiRewritingService->generateResponse(
                $ticket->original_content,
                $validated['context'] ?? ''
            );

            return response()->json([
                'response' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error generating response: ' . $e->getMessage()
            ], 500);
        }
    }

    public function extractActionItems(Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        try {
            $actionItems = $this->aiRewritingService->extractActionItems($ticket->original_content);

            return response()->json([
                'action_items' => $actionItems
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error extracting action items: ' . $e->getMessage()
            ], 500);
        }
    }

    public function summarize(Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        try {
            $summary = $this->aiRewritingService->summarizeEmail($ticket->original_content);

            return response()->json([
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error summarizing email: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkUpdate(Request $request): JsonResponse
    {
        $this->authorize('updateAny', Ticket::class);
        $validated = $request->validate([
            'ticket_ids' => 'required|array',
            'ticket_ids.*' => 'exists:tickets,id',
            'status' => 'sometimes|in:new,in_progress,waiting,resolved,closed',
            'priority' => 'sometimes|in:low,medium,high,urgent',
        ]);

        $tickets = auth()->user()->tickets()->whereIn('id', $validated['ticket_ids'])->get();

        foreach ($tickets as $ticket) {
            $updateData = [];
            if (isset($validated['status'])) {
                $updateData['status'] = $validated['status'];
            }
            if (isset($validated['priority'])) {
                $updateData['priority'] = $validated['priority'];
            }
            
            if (!empty($updateData)) {
                $ticket->update($updateData);
            }
        }

        return response()->json([
            'message' => count($tickets) . ' tickets updated successfully'
        ]);
    }

    public function deleteFiltered(Request $request)
    {
        $this->authorize('deleteAny', Ticket::class);

        $query = Ticket::query()->where('user_id', auth()->id());

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('email_account') && $request->email_account !== 'all') {
            $query->where('email_account_id', $request->email_account);
        }
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('subject', 'like', "%{$searchTerm}%")
                  ->orWhere('from_email', 'like', "%{$searchTerm}%")
                  ->orWhere('original_content', 'like', "%{$searchTerm}%");
            });
        }
        
        $count = $query->count();
        $query->delete();

        return response()->json(['message' => "Successfully deleted {$count} ticket(s)."]);
    }

    public function storeNote(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'content' => 'required|string',
            'type' => 'nullable|in:internal,public,system',
            'is_private' => 'nullable|boolean',
        ]);

        try {
            $note = Note::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'content' => $validated['content'],
                'type' => $validated['type'] ?? 'internal',
                'is_private' => $validated['is_private'] ?? false,
            ]);

            return response()->json([
                'message' => 'Note added successfully',
                'note' => $note->load('user')
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating note: ' . $e->getMessage(), [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error creating note: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateNote(Request $request, Ticket $ticket, Note $note): JsonResponse
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'content' => 'required|string',
            'type' => 'nullable|in:internal,public,system',
            'is_private' => 'nullable|boolean',
        ]);

        try {
            $note->update($validated);

            return response()->json([
                'message' => 'Note updated successfully',
                'note' => $note->fresh()->load('user')
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating note: ' . $e->getMessage(), [
                'note_id' => $note->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error updating note: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyNote(Ticket $ticket, Note $note): JsonResponse
    {
        $this->authorize('update', $ticket);

        try {
            $note->delete();

            return response()->json([
                'message' => 'Note deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting note: ' . $e->getMessage(), [
                'note_id' => $note->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Error deleting note: ' . $e->getMessage()
            ], 500);
        }
    }
}
