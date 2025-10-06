<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class WebhookProcessingFailed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $eventType,
        private string $error,
        private ?string $purchaseId = null,
        private ?array $webhookData = null
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->error()
            ->subject('⚠️ Webhook Processing Failed - '.$this->eventType)
            ->line('A webhook from CHIP failed to process correctly.')
            ->line('**Event Type:** '.$this->eventType)
            ->line('**Error:** '.$this->error);

        if ($this->purchaseId) {
            $message->line('**Purchase ID:** '.$this->purchaseId);
        }

        return $message
            ->action('View Logs', url('/admin/logs'))
            ->line('Please investigate this issue to ensure no orders are lost.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event_type' => $this->eventType,
            'error' => $this->error,
            'purchase_id' => $this->purchaseId,
            'webhook_data' => $this->webhookData,
        ];
    }
}
