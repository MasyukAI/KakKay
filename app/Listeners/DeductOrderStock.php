<?php

declare(strict_types=1);

namespace App\Listeners;

use AIArmada\Stock\Services\StockService;
use App\Events\OrderPaid;
use App\Models\OrderItem;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

final class DeductOrderStock implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $backoff = 60; // 1 minute

    public function __construct(
        private readonly StockService $stockService
    ) {}

    /**
     * Handle the event - deduct stock for paid order items.
     */
    public function handle(OrderPaid $event): void
    {
        Log::info('Processing stock deduction for paid order', [
            'order_id' => $event->order->id,
            'order_number' => $event->order->order_number,
        ]);

        try {
            // Process stock deduction for each order item
            foreach ($event->order->orderItems as $orderItem) {
                $this->processStockDeduction($orderItem);
            }

            Log::info('Stock deduction completed successfully', [
                'order_id' => $event->order->id,
                'order_number' => $event->order->order_number,
                'items_count' => $event->order->orderItems->count(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to process stock deduction', [
                'order_id' => $event->order->id,
                'order_number' => $event->order->order_number,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(OrderPaid $event, Throwable $exception): void
    {
        Log::critical('Stock deduction failed permanently after retries', [
            'order_id' => $event->order->id,
            'order_number' => $event->order->order_number,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Process stock deduction for an order item.
     */
    private function processStockDeduction(OrderItem $orderItem): void
    {
        try {
            $product = $orderItem->product;

            if (! $product) {
                Log::warning('Product not found for order item', [
                    'order_item_id' => $orderItem->id,
                    'product_id' => $orderItem->product_id,
                ]);

                return;
            }

            // Check if we have sufficient stock
            if (! $this->stockService->hasStock($product, $orderItem->quantity)) {
                Log::warning('Insufficient stock for order item', [
                    'order_item_id' => $orderItem->id,
                    'product_id' => $product->id,
                    'requested_quantity' => $orderItem->quantity,
                    'available_stock' => $this->stockService->getCurrentStock($product),
                ]);

                return;
            }

            // Record the sale and deduct stock
            $stockTransaction = $this->stockService->removeStock(
                model: $product,
                quantity: $orderItem->quantity,
                reason: 'sale',
                note: "Stock deducted for Order #{$orderItem->order->order_number}"
            );

            Log::info('Stock deducted successfully', [
                'order_item_id' => $orderItem->id,
                'product_id' => $product->id,
                'quantity' => $orderItem->quantity,
                'stock_transaction_id' => $stockTransaction->id,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to deduct stock for order item', [
                'order_item_id' => $orderItem->id,
                'product_id' => $orderItem->product_id ?? null,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
