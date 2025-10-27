<?php

declare(strict_types=1);

namespace App\Listeners;

use AIArmada\Cart\Facades\Cart;
use AIArmada\Vouchers\Facades\Voucher;
use AIArmada\Vouchers\Models\VoucherUsage;
use App\Events\OrderPaid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

final class RecordVoucherUsage implements ShouldQueue
{
    public int $tries = 3;

    public int $backoff = 60;

    /**
     * Handle the event.
     */
    public function handle(OrderPaid $event): void
    {
        $order = $event->order;
        $payment = $event->payment;

        // Extract voucher codes from order metadata or cart conditions
        $voucherCodes = $this->extractVoucherCodes($order, $event->webhookData);

        if (empty($voucherCodes)) {
            Log::debug('No voucher codes found for order', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

            return;
        }

        foreach ($voucherCodes as $code) {
            try {
                $voucher = Voucher::find($code);

                if (! $voucher) {
                    Log::warning('Voucher not found during usage recording', [
                        'code' => $code,
                        'order_id' => $order->id,
                    ]);

                    continue;
                }

                // Calculate discount amount from order data
                $discountAmount = $this->calculateDiscountAmount($order, $voucher);

                // Determine channel based on source
                $channel = match ($event->source) {
                    'webhook' => VoucherUsage::CHANNEL_AUTOMATIC,
                    'success_callback' => VoucherUsage::CHANNEL_AUTOMATIC,
                    'manual' => VoucherUsage::CHANNEL_MANUAL,
                    default => VoucherUsage::CHANNEL_API,
                };

                // Record usage with metadata
                Voucher::recordUsage(
                    code: $code,
                    userIdentifier: (string) $order->user_id,
                    discountAmount: $discountAmount,
                    cartIdentifier: $order->id,
                    cartSnapshot: [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'subtotal' => $order->subtotal,
                        'total' => $order->total,
                        'items_count' => $order->orderItems->count(),
                    ],
                    channel: $channel,
                    metadata: [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'payment_id' => $payment->id,
                        'payment_method' => $payment->method,
                        'source' => $event->source,
                        'webhook_id' => $event->webhookData['webhook_id'] ?? null,
                    ],
                    redeemedBy: null, // No staff user for automatic redemption
                    notes: "Applied during checkout for order {$order->order_number}"
                );

                Log::info('Voucher usage recorded successfully', [
                    'voucher_code' => $code,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'channel' => $channel,
                ]);
            } catch (Throwable $e) {
                Log::error('Failed to record voucher usage', [
                    'voucher_code' => $code,
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);

                // Don't fail the entire listener if one voucher fails
                continue;
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(OrderPaid $event, Throwable $exception): void
    {
        Log::critical('RecordVoucherUsage listener failed', [
            'order_id' => $event->order->id,
            'order_number' => $event->order->order_number,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    /**
     * Extract voucher codes from order or webhook data
     *
     * @param  array<string, mixed>  $webhookData
     * @return array<string>
     */
    private function extractVoucherCodes($order, array $webhookData): array
    {
        $codes = [];

        // Check order metadata for voucher codes
        if (isset($order->metadata['voucher_codes']) && is_array($order->metadata['voucher_codes'])) {
            $codes = array_merge($codes, $order->metadata['voucher_codes']);
        }

        // Check webhook data for voucher codes
        if (isset($webhookData['voucher_codes']) && is_array($webhookData['voucher_codes'])) {
            $codes = array_merge($codes, $webhookData['voucher_codes']);
        }

        // Try to extract from cart conditions if available
        try {
            $cartInstance = Cart::instance('default', (string) $order->user_id);
            $conditions = $cartInstance->getConditions();

            foreach ($conditions as $condition) {
                if ($condition->getType() === 'voucher' && method_exists($condition, 'getVoucherCode')) {
                    $codes[] = $condition->getVoucherCode();
                }
            }
        } catch (Throwable $e) {
            Log::debug('Could not extract vouchers from cart', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        return array_unique(array_filter($codes));
    }

    /**
     * Calculate discount amount from order data
     */
    private function calculateDiscountAmount($order, $voucher): \Akaunting\Money\Money
    {
        // If order has discount metadata, use it
        if (isset($order->metadata['voucher_discounts'][$voucher->code])) {
            $amount = (int) $order->metadata['voucher_discounts'][$voucher->code];

            return \Akaunting\Money\Money::{$order->currency}($amount);
        }

        // Calculate based on voucher type and order subtotal
        $discountAmount = match ($voucher->type->value) {
            'percentage' => (int) ($order->subtotal * ($voucher->value / 100)),
            'fixed' => (int) ($voucher->value * 100), // Convert to cents
            'free_shipping' => 0, // Shipping discount handled separately
            default => 0,
        };

        // Apply max discount cap if set
        if ($voucher->maxDiscount && $discountAmount > ($voucher->maxDiscount * 100)) {
            $discountAmount = (int) ($voucher->maxDiscount * 100);
        }

        return \Akaunting\Money\Money::{$order->currency}($discountAmount);
    }
}
