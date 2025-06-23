<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Services\WebPushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewTicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'subject' => $this->ticket->subject,
            'from_email' => $this->ticket->from_email,
            'from_name' => $this->ticket->from_name,
            'priority' => $this->ticket->priority,
            'status' => $this->ticket->status,
            'message' => 'New ticket received: ' . $this->ticket->subject,
            'received_at' => $this->ticket->received_at->toISOString(),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'ticket_id' => $this->ticket->id,
            'subject' => $this->ticket->subject,
            'from_email' => $this->ticket->from_email,
            'from_name' => $this->ticket->from_name,
            'priority' => $this->ticket->priority,
            'status' => $this->ticket->status,
            'message' => 'New ticket received: ' . $this->ticket->subject,
            'received_at' => $this->ticket->received_at->toISOString(),
        ]);
    }

    /**
     * Send the notification.
     */
    public function afterCommit(): void
    {
        // Send web push notification
        $webPushService = app(WebPushService::class);
        $webPushService->sendNewTicketNotification($this->ticket->user, [
            'id' => $this->ticket->id,
            'subject' => $this->ticket->subject,
            'from_email' => $this->ticket->from_email,
            'from_name' => $this->ticket->from_name,
            'priority' => $this->ticket->priority,
            'status' => $this->ticket->status,
        ]);
    }
} 