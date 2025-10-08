<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class OrderConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly Order $order,
        public readonly Payment $payment,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
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
        $customerName = $this->order->user->name ?? 'Valued Customer';
        $orderNumber = $this->order->order_number;
        $orderTotal = $this->order->total->format();
        $paymentMethod = $this->payment->gateway_name ?? 'CHIP';

        return (new MailMessage)
            ->subject("Order Confirmation #{$orderNumber}")
            ->greeting("Hi {$customerName},")
            ->line('Thank you for your order! Your payment has been successfully processed.')
            ->line("**Order Number:** {$orderNumber}")
            ->line("**Order Total:** {$orderTotal}")
            ->line("**Payment Method:** {$paymentMethod}")
            ->action('View Order Details', route('orders.show', $this->order->id))
            ->line('If you have any questions about your order, please don\'t hesitate to contact our support team.')
            ->salutation('Best regards,')
            ->salutation(config('app.name').' Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total' => $this->order->total,
            'payment_id' => $this->payment->id,
        ];
    }
}
