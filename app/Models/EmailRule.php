<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_account_id',
        'name',
        'action',
        'condition_type',
        'condition_value',
        'auto_reply_message',
        'forward_to',
        'move_to_folder_name',
        'is_active',
        'priority_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function emailAccount(): BelongsTo
    {
        return $this->belongsTo(EmailAccount::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority_order', 'asc');
    }

    public function matchesCondition(array $emailData): bool
    {
        return match ($this->condition_type) {
            'from_email' => $this->matchesEmail($emailData['from_email'] ?? '', $this->condition_value),
            'to_email' => $this->matchesEmail($emailData['to_email'] ?? '', $this->condition_value),
            'subject_contains' => str_contains(strtolower($emailData['subject'] ?? ''), strtolower($this->condition_value)),
            'body_contains' => str_contains(strtolower($emailData['body'] ?? ''), strtolower($this->condition_value)),
            'has_attachment' => ($this->condition_value === 'true') === !empty($emailData['attachments'] ?? []),
            'priority' => ($emailData['priority'] ?? 'medium') === $this->condition_value,
            default => false,
        };
    }

    private function matchesEmail(string $email, string $condition): bool
    {
        if (str_contains($condition, '*')) {
            $pattern = str_replace('*', '.*', $condition);
            return preg_match("/^{$pattern}$/i", $email);
        }
        
        return strtolower($email) === strtolower($condition);
    }
}
