<?php

declare(strict_types=1);

namespace MasyukAI\Shipping\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use MasyukAI\Shipping\Models\Shipment;

class ShipmentStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Shipment $shipment,
        public readonly string $status
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return config('shipping.notifications.channels', ['mail', 'database']);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject("Shipment Update - {$this->getStatusTitle()}")
            ->greeting("Hello {$notifiable->name}!")
            ->line($this->getStatusMessage())
            ->line("Tracking Number: {$this->shipment->tracking_number}");

        if ($this->shipment->isDelivered()) {
            $message->line('Thank you for your business!');
        } else {
            $message->action('Track Your Package', url("/shipping/track/{$this->shipment->tracking_number}"));
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'shipment_id' => $this->shipment->id,
            'tracking_number' => $this->shipment->tracking_number,
            'status' => $this->status,
            'message' => $this->getStatusMessage(),
        ];
    }

    /**
     * Get the status title.
     */
    protected function getStatusTitle(): string
    {
        return match ($this->status) {
            'created' => 'Shipment Created',
            'dispatched' => 'Package Dispatched',
            'in_transit' => 'Package In Transit',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Package Delivered',
            'failed' => 'Delivery Failed',
            default => 'Shipment Updated',
        };
    }

    /**
     * Get the status message.
     */
    protected function getStatusMessage(): string
    {
        return match ($this->status) {
            'created' => 'Your shipment has been created and will be dispatched soon.',
            'dispatched' => 'Your package has been dispatched from our facility.',
            'in_transit' => 'Your package is on its way to you.',
            'out_for_delivery' => 'Your package is out for delivery and should arrive today.',
            'delivered' => 'Your package has been successfully delivered.',
            'failed' => 'There was an issue with the delivery. Please contact us for assistance.',
            default => 'Your shipment status has been updated.',
        };
    }
}