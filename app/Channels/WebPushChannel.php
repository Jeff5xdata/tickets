<?php

namespace App\Channels;

use App\Models\User;
use App\Services\WebPushService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WebPushChannel
{
    protected WebPushService $webPushService;

    public function __construct(WebPushService $webPushService)
    {
        $this->webPushService = $webPushService;
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification): void
    {
        if (!$notifiable instanceof User) {
            Log::warning('WebPushChannel: Notifiable is not a User instance', [
                'notifiable_type' => get_class($notifiable),
            ]);
            return;
        }

        if (!method_exists($notification, 'toWebPush')) {
            Log::warning('WebPushChannel: Notification does not have toWebPush method', [
                'notification_type' => get_class($notification),
            ]);
            return;
        }

        try {
            $webPushData = $notification->toWebPush($notifiable);
            
            if (!isset($webPushData['title']) || !isset($webPushData['body'])) {
                Log::warning('WebPushChannel: Invalid web push data format', [
                    'notification_type' => get_class($notification),
                    'data' => $webPushData,
                ]);
                return;
            }

            $this->webPushService->sendToUser(
                $notifiable,
                $webPushData['title'],
                $webPushData['body'],
                $webPushData['data'] ?? []
            );

            Log::info('WebPushChannel: Notification sent successfully', [
                'user_id' => $notifiable->id,
                'notification_type' => get_class($notification),
            ]);
        } catch (\Exception $e) {
            Log::error('WebPushChannel: Failed to send notification', [
                'user_id' => $notifiable->id,
                'notification_type' => get_class($notification),
                'error' => $e->getMessage(),
            ]);
        }
    }
} 