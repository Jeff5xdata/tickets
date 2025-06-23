<?php

namespace App\Services;

use App\Models\CalendarEvent;
use App\Models\EmailAccount;
use App\Models\User;
use Illuminate\Support\Facades\Log;

abstract class CalendarService
{
    protected EmailAccount $emailAccount;

    public function __construct(EmailAccount $emailAccount)
    {
        $this->emailAccount = $emailAccount;
    }

    abstract public function syncEvents(): int;
    abstract public function createEvent(array $eventData): ?CalendarEvent;
    abstract public function updateEvent(CalendarEvent $event, array $eventData): bool;
    abstract public function deleteEvent(CalendarEvent $event): bool;
    abstract public function getCalendars(): array;

    protected function syncEvent(array $eventData): void
    {
        try {
            $existingEvent = CalendarEvent::where('external_id', $eventData['external_id'])
                ->where('email_account_id', $this->emailAccount->id)
                ->first();

            if ($existingEvent) {
                $existingEvent->update($eventData);
            } else {
                CalendarEvent::create(array_merge($eventData, [
                    'user_id' => $this->emailAccount->user_id,
                    'email_account_id' => $this->emailAccount->id,
                ]));
            }
        } catch (\Exception $e) {
            Log::error('Error syncing calendar event', [
                'error' => $e->getMessage(),
                'event_data' => $eventData,
                'email_account_id' => $this->emailAccount->id
            ]);
        }
    }

    protected function refreshTokenIfNeeded(): bool
    {
        if ($this->emailAccount->needsTokenRefresh()) {
            try {
                if ($this->emailAccount->provider === 'google') {
                    $this->refreshGoogleToken();
                } elseif ($this->emailAccount->provider === 'microsoft') {
                    $this->refreshMicrosoftToken();
                }
                return true;
            } catch (\Exception $e) {
                Log::error('Failed to refresh token', [
                    'email_account_id' => $this->emailAccount->id,
                    'error' => $e->getMessage()
                ]);
                return false;
            }
        }
        return true;
    }

    protected function refreshGoogleToken(): void
    {
        $response = \Illuminate\Support\Facades\Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google-email.client_id'),
            'client_secret' => config('services.google-email.client_secret'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->emailAccount->refresh_token,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to refresh Google token: ' . $response->body());
        }

        $data = $response->json();
        $this->emailAccount->update([
            'access_token' => $data['access_token'],
            'token_expires_at' => now()->addSeconds($data['expires_in']),
        ]);
    }

    protected function refreshMicrosoftToken(): void
    {
        $response = \Illuminate\Support\Facades\Http::asForm()->post('https://login.microsoftonline.com/' . config('services.microsoft.tenant', 'common') . '/oauth2/v2.0/token', [
            'client_id' => config('services.microsoft.client_id'),
            'client_secret' => config('services.microsoft.client_secret'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->emailAccount->refresh_token,
            'scope' => 'offline_access https://graph.microsoft.com/Calendars.ReadWrite',
            'redirect_uri' => config('services.microsoft.redirect'),
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to refresh Microsoft token: ' . $response->body());
        }

        $data = $response->json();
        $this->emailAccount->update([
            'access_token' => $data['access_token'],
            'token_expires_at' => now()->addSeconds($data['expires_in']),
        ]);
    }
} 