<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Notifications\OrderConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

final class SendOrderConfirmationEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $backoff = 60; // 1 minute

    /**
     * Handle the event.
     */
    public function handle(OrderPaid $event): void
    {
        Log::info('Sending order confirmation email', [
            'order_id' => $event->order->id,
            'order_number' => $event->order->order_number,
            'customer_email' => $event->order->user->email ?? 'unknown',
        ]);

        try {
            // Send confirmation email to customer
            Notification::route('mail', $event->order->user->email)
                ->notify(new OrderConfirmation($event->order, $event->payment));

            Log::info('Order confirmation email sent successfully', [
                'order_id' => $event->order->id,
                'order_number' => $event->order->order_number,
            ]);
        } catch (Throwable $throwable) {
            Log::error('Failed to send order confirmation email', [
                'order_id' => $event->order->id,
                'order_number' => $event->order->order_number,
                'error' => $throwable->getMessage(),
            ]);

            throw $throwable; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(OrderPaid $event, Throwable $exception): void
    {
        Log::critical('Order confirmation email failed permanently after retries', [
            'order_id' => $event->order->id,
            'order_number' => $event->order->order_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
