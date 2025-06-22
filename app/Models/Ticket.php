<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'attachments',
        'received_at',
        'responded_at',
        'is_ai_rewritten',
        'ai_prompt_used',
    ];

    protected $casts = [
        'to_emails' => 'array',
        'cc_emails' => 'array',
        'bcc_emails' => 'array',
        'attachments' => 'array',
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
}
