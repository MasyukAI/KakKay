<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\Shipment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ProcessOrderShipping implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $backoff = 120; // 2 minutes (shipping can be slower)

    /**
     * Handle the event.
     */
    public function handle(OrderPaid $event): void
    {
        Log::info('Processing shipping for order', [
            'order_id' => $event->order->id,
            'order_number' => $event->order->order_number,
        ]);

        try {
            // Check if shipping is needed (physical products)
            $hasPhysicalItems = $event->order->orderItems->contains(function ($item) {
                // TODO: Check if product requires shipping
                // This would depend on your product model having a 'requires_shipping' field
                return true; // Placeholder - assume all items need shipping for now
            });

            if (! $hasPhysicalItems) {
                Log::info('Order contains no physical items requiring shipping', [
                    'order_id' => $event->order->id,
                ]);

                return;
            }

            // TODO: Implement shipping logic
            // This could integrate with shipping providers like:
            // - Shippo, EasyShip, or local couriers
            // - Create shipment records
            // - Generate tracking numbers
            // - Send shipping notifications

            // For now, create a placeholder shipment record
            $shipment = Shipment::create([
                'order_id' => $event->order->id,
                'tracking_number' => 'SHIP-'.$event->order->order_number.'-'.now()->format('YmdHis'),
                'carrier' => 'Placeholder Carrier',
                'status' => 'processing',
                'shipped_at' => null,
                'delivered_at' => null,
                'shipping_address' => $event->order->address,
                'estimated_delivery' => now()->addDays(3), // Placeholder
            ]);

            Log::info('Shipping processed successfully', [
                'order_id' => $event->order->id,
                'order_number' => $event->order->order_number,
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
            ]);
        } catch (Throwable $throwable) {
            Log::error('Failed to process shipping', [
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
        Log::critical('Shipping processing failed permanently after retries', [
            'order_id' => $event->order->id,
            'order_number' => $event->order->order_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
