<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MasyukAI\Shipping\Events\ShipmentCreated;
use MasyukAI\Shipping\Notifications\ShipmentStatusNotification;

class ProcessOrderShipment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly string $shippingMethod = 'standard'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Only process paid orders that require shipping
        if (! $this->order->isPaid() || ! $this->order->requiresShipping()) {
            return;
        }

        // Check if shipment already exists
        if ($this->order->activeShipment()) {
            return;
        }

        try {
            // Create the shipment
            $shipment = $this->order->ship($this->shippingMethod);

            // Update order status
            $this->order->update(['status' => 'processing']);

            // Dispatch shipment created event
            ShipmentCreated::dispatch($shipment);

            // Notify customer
            if ($this->order->user) {
                $this->order->user->notify(
                    new ShipmentStatusNotification($shipment, 'created')
                );
            }

            logger()->info("Shipment created for order {$this->order->order_number}", [
                'order_id' => $this->order->id,
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
            ]);

        } catch (\Exception $e) {
            logger()->error("Failed to create shipment for order {$this->order->order_number}: " . $e->getMessage());
            throw $e;
        }
    }
}