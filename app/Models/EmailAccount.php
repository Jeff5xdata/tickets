<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class EmailAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'type',
        'provider',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'imap_host',
        'imap_port',
        'imap_encryption',
        'imap_username',
        'imap_password',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_username',
        'smtp_password',
        'is_active',
        'signature_text',
        'signature_image_path',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'is_active' => 'boolean',
        'imap_config' => 'array',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
        'imap_password',
        'smtp_password',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function emailRules(): HasMany
    {
        return $this->hasMany(EmailRule::class);
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function isTokenExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }

    public function needsTokenRefresh(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->subMinutes(5)->isPast();
    }

    public function getImapConfig(): array
    {
        return [
            'host' => $this->imap_host,
            'port' => $this->imap_port,
            'encryption' => $this->imap_encryption,
            'validate_cert' => true,
            'username' => $this->imap_username,
            'password' => $this->imap_password,
        ];
    }

    public function hasSignature(): bool
    {
        return !empty($this->signature_text) || !empty($this->signature_image_path);
    }

    public function getSignatureText(): ?string
    {
        return $this->signature_text;
    }

    public function getSignatureImageUrl(): ?string
    {
        return $this->signature_image_path ? Storage::url($this->signature_image_path) : null;
    }

    public function getSignatureHtml(): string
    {
        $html = '';
        
        if ($this->signature_text) {
            $html .= '<div class="signature-text">' . $this->signature_text . '</div>';
        }
        
        if ($this->signature_image_path) {
            $html .= '<div class="signature-image"><img src="' . Storage::url($this->signature_image_path) . '" alt="Signature" style="max-width: 300px;"></div>';
        }
        
        return $html;
    }

    public function getFormattedSignature(): string
    {
        $signature = '';
        
        if ($this->signature_text) {
            $signature .= $this->signature_text;
        }
        
        if ($this->signature_image_path) {
            $signature .= "\n[Signature Image: " . basename($this->signature_image_path) . "]";
        }
        
        return $signature;
    }
}
