<?php

declare(strict_types=1);

namespace App\Listeners;

use AIArmada\Orders\Events\OrderPaid;
use App\Models\Order;
use App\Models\Payment;
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
        /** @var Order $order */
        $order = $event->order;

        Log::info('Sending order confirmation email', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'customer_email' => $order->user->email ?? 'unknown',
            'transaction_id' => $event->transactionId,
        ]);

        try {
            // Look up the payment by transaction_id or get the latest payment
            /** @var Payment|null $payment */
            $payment = $order->payments()
                ->where('transaction_id', $event->transactionId)
                ->first() ?? $order->payments()->latest()->first();

            if (! $payment) {
                Log::warning('No payment found for order confirmation email', [
                    'order_id' => $order->id,
                    'transaction_id' => $event->transactionId,
                ]);

                return;
            }

            // Send confirmation email to customer
            /** @var \App\Models\User $user */
            $user = $order->user;
            Notification::route('mail', $user->email)
                ->notify(new OrderConfirmation($order, $payment));

            Log::info('Order confirmation email sent successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);
        } catch (Throwable $throwable) {
            Log::error('Failed to send order confirmation email', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
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
