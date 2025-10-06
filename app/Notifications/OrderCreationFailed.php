<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class OrderCreationFailed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $purchaseId,
        private string $error,
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
        return (new MailMessage)
            ->error()
            ->subject('⚠️ Order Creation Failed - Purchase ID: '.$this->purchaseId)
            ->line('An order failed to be created from a successful payment.')
            ->line('**Purchase ID:** '.$this->purchaseId)
            ->line('**Error:** '.$this->error)
            ->line('**Amount:** RM '.number_format(($this->webhookData['amount'] ?? 0) / 100, 2))
            ->action('View Logs', url('/admin/logs'))
            ->line('Please investigate this issue immediately to ensure the customer receives their order.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'purchase_id' => $this->purchaseId,
            'error' => $this->error,
            'webhook_data' => $this->webhookData,
        ];
    }
}
