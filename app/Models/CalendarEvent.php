<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_account_id',
        'external_id',
        'calendar_id',
        'title',
        'description',
        'location',
        'start_time',
        'end_time',
        'all_day',
        'status',
        'provider',
        'attendees',
        'recurrence',
        'color_id',
        'is_synced',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'all_day' => 'boolean',
        'is_synced' => 'boolean',
        'attendees' => 'array',
        'recurrence' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function emailAccount(): BelongsTo
    {
        return $this->belongsTo(EmailAccount::class);
    }

    public function isUpcoming(): bool
    {
        return $this->start_time->isFuture();
    }

    public function isToday(): bool
    {
        return $this->start_time->isToday();
    }

    public function isThisWeek(): bool
    {
        return $this->start_time->isThisWeek();
    }

    public function getDurationAttribute(): string
    {
        if ($this->all_day) {
            return 'All day';
        }

        $duration = $this->start_time->diffInMinutes($this->end_time);
        
        if ($duration < 60) {
            return $duration . ' minutes';
        } elseif ($duration < 1440) {
            $hours = floor($duration / 60);
            $minutes = $duration % 60;
            return $hours . 'h ' . ($minutes > 0 ? $minutes . 'm' : '');
        } else {
            $days = floor($duration / 1440);
            return $days . ' day' . ($days > 1 ? 's' : '');
        }
    }

    public function getFormattedStartTimeAttribute(): string
    {
        if ($this->all_day) {
            return $this->start_time->format('M j, Y');
        }
        return $this->start_time->format('M j, Y g:i A');
    }

    public function getFormattedEndTimeAttribute(): string
    {
        if ($this->all_day) {
            return $this->end_time->format('M j, Y');
        }
        return $this->end_time->format('M j, Y g:i A');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'confirmed' => 'green',
            'tentative' => 'yellow',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>=', now());
    }

    public function scopeToday($query)
    {
        return $query->whereDate('start_time', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }
} 