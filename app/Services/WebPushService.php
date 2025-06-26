<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Facades\Log;

class WebPushService
{
    protected WebPush $webPush;

    public function __construct()
    {
        $this->webPush = new WebPush([
            'VAPID' => [
                'subject' => config('services.webpush.vapid.subject'),
                'publicKey' => config('services.webpush.vapid.public_key'),
                'privateKey' => config('services.webpush.vapid.private_key'),
            ],
        ]);
    }

    /**
     * Send push notification to a specific user.
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        $subscriptions = $user->pushSubscriptions;

        foreach ($subscriptions as $subscription) {
            $this->sendToSubscription($subscription, $title, $body, $data);
        }
    }

    /**
     * Send push notification to a specific subscription.
     */
    public function sendToSubscription(PushSubscription $subscription, string $title, string $body, array $data = []): void
    {
        try {
            $pushSubscription = Subscription::create([
                'endpoint' => $subscription->endpoint,
                'keys' => [
                    'p256dh' => $subscription->p256dh_key,
                    'auth' => $subscription->auth_token,
                ],
            ]);

            $payload = json_encode([
                'title' => $title,
                'body' => $body,
                'icon' => '/images/icon-192x192.png',
                'badge' => '/images/icon-72x72.png',
                'data' => $data,
                'actions' => [
                    [
                        'action' => 'view',
                        'title' => 'View',
                        'icon' => '/images/icon-96x96.png',
                    ],
                    [
                        'action' => 'close',
                        'title' => 'Close',
                        'icon' => '/images/icon-96x96.png',
                    ],
                ],
            ]);

            $report = $this->webPush->sendOneNotification($pushSubscription, $payload);

            if (!$report->isSuccess()) {
                Log::warning('Push notification failed', [
                    'subscription_id' => $subscription->id,
                    'user_id' => $subscription->user_id,
                    'reason' => $report->getReason(),
                    'response' => $report->getResponse(),
                ]);

                // If subscription is invalid, delete it
                if ($report->isSubscriptionExpired()) {
                    $subscription->delete();
                    Log::info('Deleted expired push subscription', [
                        'subscription_id' => $subscription->id,
                        'user_id' => $subscription->user_id,
                    ]);
                }
            } else {
                Log::info('Push notification sent successfully', [
                    'subscription_id' => $subscription->id,
                    'user_id' => $subscription->user_id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error sending push notification', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send notification for new ticket.
     */
    public function sendNewTicketNotification(User $user, array $ticketData): void
    {
        $title = 'New Ticket Received';
        $body = 'New ticket: ' . $ticketData['subject'];
        
        $data = [
            'type' => 'new_ticket',
            'ticket_id' => $ticketData['id'],
            'url' => route('tickets.show', $ticketData['id']),
        ];

        $this->sendToUser($user, $title, $body, $data);
    }

    /**
     * Clean up expired subscriptions.
     */
    public function cleanupExpiredSubscriptions(): void
    {
        // This method can be called periodically to clean up expired subscriptions
        // For now, we handle cleanup in sendToSubscription method
        Log::info('Push subscription cleanup completed');
    }
} 