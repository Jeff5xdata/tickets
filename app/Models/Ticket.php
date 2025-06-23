<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_account_id',
        'google_task_id',
        'subject',
        'original_content',
        'html_content',
        'from_email',
        'from_name',
        'to_email',
        'to_emails',
        'cc_emails',
        'bcc_emails',
        'message_id',
        'thread_id',
        'status',
        'priority',
        'attachment_metadata',
        'received_at',
        'responded_at',
        'is_ai_rewritten',
        'ai_prompt_used',
    ];

    protected $casts = [
        'to_emails' => 'array',
        'cc_emails' => 'array',
        'bcc_emails' => 'array',
        'attachment_metadata' => 'array',
        'received_at' => 'datetime',
        'responded_at' => 'datetime',
        'is_ai_rewritten' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function emailAccount(): BelongsTo
    {
        return $this->belongsTo(EmailAccount::class);
    }

    public function googleTask(): BelongsTo
    {
        return $this->belongsTo(GoogleTask::class);
    }

    public function googleTasks(): HasMany
    {
        return $this->hasMany(GoogleTask::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function markAsResponded(): void
    {
        $this->update(['responded_at' => now()]);
    }

    public function updateStatus(string $status): void
    {
        $this->update(['status' => $status]);
    }

    /**
     * Get processed HTML content with CID URLs handled
     */
    public function getProcessedHtmlContent(): string
    {
        if (empty($this->html_content)) {
            return '';
        }

        // Process CID URLs in the HTML content
        $processedHtml = $this->processCidUrls($this->html_content);

        return $processedHtml;
    }

    /**
     * Process CID URLs in HTML content
     */
    protected function processCidUrls(string $html): string
    {
        // Replace cid: URLs with placeholder text or remove them
        $processedHtml = preg_replace_callback(
            '/src=["\']cid:([^"\']+)["\']/i',
            function ($matches) {
                $cid = $matches[1];
                $attachmentName = $this->findAttachmentNameByCid($cid);
                
                if ($attachmentName) {
                    return 'src="data:image/svg+xml;base64,' . base64_encode(
                        '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
                            <rect width="100" height="100" fill="#f3f4f6"/>
                            <text x="50" y="45" text-anchor="middle" font-family="Arial" font-size="10" fill="#6b7280">Inline Image</text>
                            <text x="50" y="60" text-anchor="middle" font-family="Arial" font-size="8" fill="#9ca3af">' . htmlspecialchars($attachmentName) . '</text>
                        </svg>'
                    ) . '" alt="Inline image: ' . htmlspecialchars($attachmentName) . '"';
                } else {
                    return 'src="data:image/svg+xml;base64,' . base64_encode(
                        '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
                            <rect width="100" height="100" fill="#f3f4f6"/>
                            <text x="50" y="50" text-anchor="middle" font-family="Arial" font-size="10" fill="#6b7280">Image Not Found</text>
                        </svg>'
                    ) . '" alt="Inline image not found"';
                }
            },
            $html
        );

        // Also handle background-image CSS properties with cid: URLs
        $processedHtml = preg_replace_callback(
            '/background-image:\s*url\(["\']?cid:([^"\']+)["\']?\)/i',
            function ($matches) {
                $cid = $matches[1];
                $attachmentName = $this->findAttachmentNameByCid($cid);
                
                if ($attachmentName) {
                    return 'background-image: url("data:image/svg+xml;base64,' . base64_encode(
                        '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
                            <rect width="100" height="100" fill="#f3f4f6"/>
                            <text x="50" y="45" text-anchor="middle" font-family="Arial" font-size="10" fill="#6b7280">Inline Image</text>
                            <text x="50" y="60" text-anchor="middle" font-family="Arial" font-size="8" fill="#9ca3af">' . htmlspecialchars($attachmentName) . '</text>
                        </svg>'
                    ) . '")';
                } else {
                    return 'background-image: url("data:image/svg+xml;base64,' . base64_encode(
                        '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
                            <rect width="100" height="100" fill="#f3f4f6"/>
                            <text x="50" y="50" text-anchor="middle" font-family="Arial" font-size="10" fill="#6b7280">Image Not Found</text>
                        </svg>'
                    ) . '")';
                }
            },
            $processedHtml
        );

        return $processedHtml;
    }

    /**
     * Find attachment name by CID
     */
    protected function findAttachmentNameByCid(string $cid): ?string
    {
        if (empty($this->attachment_metadata)) {
            return null;
        }

        // Look for attachment with matching CID
        foreach ($this->attachment_metadata as $attachment) {
            if (isset($attachment['content_id']) && $attachment['content_id'] === $cid) {
                return $attachment['name'] ?? 'Unknown attachment';
            }
        }

        // If no exact match, try to find by filename that might contain the CID
        foreach ($this->attachment_metadata as $attachment) {
            $name = $attachment['name'] ?? '';
            if (str_contains($name, $cid) || str_contains($cid, pathinfo($name, PATHINFO_FILENAME))) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Get a safe version of HTML content that blocks all CID URLs
     */
    public function getSafeHtmlContent(): string
    {
        if (empty($this->html_content)) {
            return '';
        }

        // First process with our CID handling
        $processedHtml = $this->getProcessedHtmlContent();
        
        // Additional safety: remove any remaining cid: URLs
        $safeHtml = preg_replace('/src=["\']cid:[^"\']+["\']/i', 'src="data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100">
                <rect width="100" height="100" fill="#f3f4f6"/>
                <text x="50" y="50" text-anchor="middle" font-family="Arial" font-size="10" fill="#6b7280">Blocked Image</text>
            </svg>'
        ) . '" alt="Blocked inline image"', $processedHtml);
        
        // Also remove background-image with cid: URLs
        $safeHtml = preg_replace('/background-image:\s*url\(["\']?cid:[^"\']+["\']?\)/i', 'background-image: none', $safeHtml);
        
        return $safeHtml;
    }
}
