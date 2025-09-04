<?php

namespace App\Listeners;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\StockService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MasyukAI\Cart\Facades\Cart;
use Masyukai\Chip\Events\PurchasePaid;

class HandlePaymentSuccess implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private StockService $stockService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(PurchasePaid $event): void
    {
        try {
            $purchase = $event->purchase;

            Log::info('Processing payment success for stock deduction', [
                'purchase_id' => $purchase->id,
                'reference' => $purchase->reference,
            ]);

            // Find the order by reference or purchase ID
            $order = $this->findOrderByPurchase($purchase);

            if (! $order) {
                Log::warning('No order found for purchase', [
                    'purchase_id' => $purchase->id,
                    'reference' => $purchase->reference,
                ]);

                return;
            }

            // Update order status to paid
            DB::transaction(function () use ($order, $purchase) {
                $oldStatus = $order->status;
                $newStatus = 'confirmed';

                // Update order status
                $order->update([
                    'status' => $newStatus,
                ]);

                // Record status change in history
                $order->statusHistories()->create([
                    'from_status' => $oldStatus,
                    'to_status' => $newStatus,
                    'actor_type' => 'system',
                    'note' => 'Payment was successfully completed',
                    'changed_at' => now(),
                    'meta' => [
                        'payment_id' => $purchase->id,
                        'payment_method' => 'chip',
                    ],
                ]);

                // Update payment record if exists
                $payment = $order->payments()->where('status', 'pending')->first();
                if ($payment) {
                    $payment->update([
                        'status' => 'completed',
                        'gateway_transaction_id' => $purchase->id,
                        'gateway_payment_id' => $purchase->id,
                        'gateway_response' => $purchase->toArray(),
                        'paid_at' => now(),
                    ]);
                }

                // Process stock deduction for each order item
                foreach ($order->orderItems as $orderItem) {
                    $this->processStockDeduction($orderItem);
                }

                // Clear the cart after successful payment
                \MasyukAI\Cart\Facades\Cart::clear();
            });

            Log::info('Payment success processed successfully', [
                'order_id' => $order->id,
                'purchase_id' => $purchase->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process payment success', [
                'purchase_id' => $purchase->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Find order by purchase reference or ID
     */
    private function findOrderByPurchase($purchase): ?Order
    {
        // Primary method: find by reference (order ID is stored in reference)
        if ($purchase->reference) {
            $order = Order::find($purchase->reference);
            if ($order) {
                return $order;
            }
        }

        // Alternative: find by payment record with gateway_payment_id
        $payment = \App\Models\Payment::where('gateway_payment_id', $purchase->id)
            ->orWhere('gateway_transaction_id', $purchase->id)
            ->first();

        if ($payment && $payment->order) {
            return $payment->order;
        }

        // Fallback: find recent pending order for the same amount
        return Order::where('status', 'pending')
            ->where('total', $purchase->purchase->total ?? $purchase->amountInCents ?? 0)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Process stock deduction for an order item
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

            // Check if stock transaction already exists for this order item
            $existingTransaction = \App\Models\StockTransaction::where('order_item_id', $orderItem->id)
                ->where('type', 'out')
                ->first();

            if ($existingTransaction) {
                Log::info('Stock already deducted for order item', [
                    'order_item_id' => $orderItem->id,
                    'transaction_id' => $existingTransaction->id,
                ]);

                return;
            }

            // Record the sale and deduct stock
            $stockTransaction = $this->stockService->recordSale(
                product: $product,
                orderItem: $orderItem,
                note: "Stock deducted for Order #{$orderItem->order_id}"
            );

            Log::info('Stock deducted successfully', [
                'order_item_id' => $orderItem->id,
                'product_id' => $product->id,
                'quantity' => $orderItem->quantity,
                'stock_transaction_id' => $stockTransaction->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to deduct stock for order item', [
                'order_item_id' => $orderItem->id,
                'product_id' => $orderItem->product_id ?? null,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(PurchasePaid $event, \Throwable $exception): void
    {
        Log::error('Payment success handling failed permanently', [
            'purchase_id' => $event->purchase->id ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}
