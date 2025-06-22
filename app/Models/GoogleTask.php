<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoogleTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ticket_id',
        'google_task_id',
        'title',
        'notes',
        'internal_note',
        'due_date',
        'completed',
        'list_id',
        'list_name',
        'priority',
        'parent_task_id',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(GoogleTask::class, 'parent_task_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(GoogleTask::class, 'parent_task_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'google_task_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    public function scopePending($query)
    {
        return $query->where('completed', false);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())->where('completed', false);
    }

    public function markAsCompleted(): void
    {
        $this->update(['completed' => true]);
    }

    public function markAsPending(): void
    {
        $this->update(['completed' => false]);
    }
}
