<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'content',
        'type',
        'is_private',
        'metadata',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'metadata' => 'array',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeInternal($query)
    {
        return $query->where('type', 'internal');
    }

    public function scopePublic($query)
    {
        return $query->where('type', 'public');
    }

    public function scopeSystem($query)
    {
        return $query->where('type', 'system');
    }

    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_private', false);
    }
}
