<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Masyukai\Chip\Facades\Chip;

class ChipPaymentGateway implements PaymentGatewayInterface
{
    protected PaymentGateway $gateway;

    public function __construct()
    {
        $this->gateway = PaymentGateway::where('name', 'chip')->firstOrFail();
    }

    public function createPayment(Order $order, array $options = []): array
    {
        try {
            $purchaseData = [
                'purchase' => [
                    'total_amount' => $order->total,
                    'currency' => $order->currency ?? 'MYR',
                    'timezone' => 'Asia/Kuala_Lumpur',
                    'reference' => $order->order_number,
                    'products' => $this->formatOrderItems($order),
                    'client' => [
                        'first_name' => $order->user->name ?? 'Guest',
                        'email' => $order->user->email ?? $order->checkout_form_data['email'] ?? '',
                        'phone' => $order->checkout_form_data['phone'] ?? '',
                    ],
                    'success_redirect' => $options['success_url'] ?? route('checkout.success'),
                    'failure_redirect' => $options['failure_url'] ?? route('checkout.failure'),
                    'webhook_url' => route('webhooks.chip'),
                ],
            ];

            $response = Chip::createPurchase($purchaseData);

            if (isset($response['id'])) {
                // Create payment record
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'payment_gateway_id' => $this->gateway->id,
                    'gateway_transaction_id' => $response['id'],
                    'gateway_payment_id' => $response['id'],
                    'gateway_response' => $response,
                    'amount' => $order->total,
                    'status' => 'pending',
                    'method' => 'chip',
                    'currency' => $order->currency ?? 'MYR',
                ]);

                return [
                    'success' => true,
                    'payment' => $payment,
                    'gateway_response' => $response,
                    'redirect_url' => $response['checkout_url'] ?? null,
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to create CHIP payment',
                'gateway_response' => $response,
            ];

        } catch (\Exception $e) {
            Log::error('CHIP Payment Creation Failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function processPayment(Payment $payment, array $options = []): array
    {
        // CHIP doesn't require separate processing - payment is processed on their end
        return $this->verifyPayment($payment->gateway_transaction_id);
    }

    public function verifyPayment(string $gatewayTransactionId): array
    {
        try {
            $response = Chip::getPurchase($gatewayTransactionId);

            return [
                'success' => true,
                'status' => $response['status'] ?? 'unknown',
                'gateway_response' => $response,
            ];

        } catch (\Exception $e) {
            Log::error('CHIP Payment Verification Failed', [
                'transaction_id' => $gatewayTransactionId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function refundPayment(Payment $payment, ?int $amount = null): array
    {
        try {
            $refundAmount = $amount ?? $payment->amount;

            $response = Chip::refundPurchase($payment->gateway_transaction_id, $refundAmount);

            if (isset($response['id'])) {
                $payment->update([
                    'status' => 'refunded',
                    'refunded_at' => now(),
                ]);

                return [
                    'success' => true,
                    'gateway_response' => $response,
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to process refund',
                'gateway_response' => $response,
            ];

        } catch (\Exception $e) {
            Log::error('CHIP Refund Failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function handleWebhook(array $payload): array
    {
        try {
            $purchaseId = $payload['id'] ?? null;

            if (! $purchaseId) {
                return ['success' => false, 'error' => 'No purchase ID in webhook'];
            }

            $payment = Payment::where('gateway_transaction_id', $purchaseId)->first();

            if (! $payment) {
                return ['success' => false, 'error' => 'Payment not found'];
            }

            $status = $payload['status'] ?? 'unknown';

            $payment->update([
                'status' => $this->mapChipStatus($status),
                'gateway_response' => $payload,
                'paid_at' => $status === 'successful' ? now() : null,
                'failed_at' => in_array($status, ['failed', 'cancelled']) ? now() : null,
            ]);

            // Update order status
            if ($status === 'successful') {
                $payment->order->update(['status' => 'paid']);
            } elseif (in_array($status, ['failed', 'cancelled'])) {
                $payment->order->update(['status' => 'payment_failed']);
            }

            return ['success' => true, 'payment' => $payment];

        } catch (\Exception $e) {
            Log::error('CHIP Webhook Failed', [
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getSupportedCurrencies(): array
    {
        return ['MYR']; // CHIP only supports Malaysian Ringgit
    }

    public function getConfig(): array
    {
        return $this->gateway->config ?? [];
    }

    public function isAvailable(): bool
    {
        $cacheKey = self::getCacheKey();

        return Cache::remember($cacheKey, now()->addMinutes(30), function () {
            return $this->gateway->is_active &&
                   ! empty($this->gateway->config['api_key']) &&
                   ! empty($this->gateway->config['brand_id']);
        });
    }

    /**
     * Get the cache key for this gateway's availability
     */
    public static function getCacheKey(): string
    {
        return 'payment_gateway_availability:chip';
    }

    /**
     * Bust the availability cache for this gateway
     */
    public static function bustCache(): void
    {
        Cache::forget(self::getCacheKey());
    }

    public function getName(): string
    {
        return 'chip';
    }

    public function getDisplayName(): string
    {
        return 'CHIP Payment Gateway';
    }

    protected function formatOrderItems(Order $order): array
    {
        $items = [];

        // Use order items if available, fallback to cart_items for backward compatibility
        if ($order->orderItems->isNotEmpty()) {
            foreach ($order->orderItems as $orderItem) {
                $items[] = [
                    'name' => $orderItem->product->name ?? 'Product',
                    'price' => $orderItem->total_price,
                    'quantity' => 1, // CHIP expects total price, so quantity is 1
                ];
            }
        } elseif (is_array($order->cart_items)) {
            foreach ($order->cart_items as $item) {
                $items[] = [
                    'name' => $item['name'] ?? 'Product',
                    'price' => ($item['price'] ?? 0) * ($item['quantity'] ?? 1),
                    'quantity' => 1, // CHIP expects total price, so quantity is 1
                ];
            }
        }

        return $items;
    }

    protected function mapChipStatus(string $chipStatus): string
    {
        return match ($chipStatus) {
            'successful' => 'completed',
            'pending' => 'pending',
            'failed' => 'failed',
            'cancelled' => 'cancelled',
            default => 'unknown',
        };
    }
}
