<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Payment;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MasyukAI\Cart\Cart;
use Psr\Log\LoggerInterface;

final class PaymentService
{
    private PaymentGatewayInterface $gateway;

    private CodeGeneratorService $codeGenerator;

    private $paymentCodeGenerator;

    private ?LoggerInterface $logger;

    public function __construct(
        PaymentGatewayInterface $gateway,
        CodeGeneratorService $codeGenerator,
        ?LoggerInterface $logger = null,
        ?callable $paymentCodeGenerator = null
    ) {
        $this->gateway = $gateway;
        $this->codeGenerator = $codeGenerator;
        $this->logger = $logger;
        $this->paymentCodeGenerator = $paymentCodeGenerator ?? [CodeGeneratorService::class, 'generatePaymentCode'];
    }

    /**
     * Create payment with retry logic for unique reference generation
     */
    public function createPaymentWithRetry(array $attributes, int $maxRetries = 3): Payment
    {
        $retries = 0;

        do {
            try {
                // Generate payment reference using injected callable (for testability)
                $attributes['reference'] = call_user_func($this->paymentCodeGenerator);

                return Payment::create($attributes);

            } catch (QueryException $e) {
                // Check if it's a PostgreSQL unique constraint violation (23505)
                if ($e->getCode() === '23505' && $retries < $maxRetries) {
                    $retries++;

                    if ($this->logger) {
                        $this->logger->info('Payment reference collision detected, retrying', [
                            'attempt' => $retries,
                            'reference' => $attributes['reference'] ?? 'unknown',
                        ]);
                    }

                    // Remove reference to force regeneration
                    unset($attributes['reference']);

                    continue;
                }

                // Re-throw if not a uniqueness violation or max retries reached
                throw $e;
            }
        } while ($retries < $maxRetries);

        throw new Exception("Unable to create payment with unique reference after {$maxRetries} attempts");
    }

    /**
     * Process payment with the gateway
     */
    public function processPayment(array $customerData, array $cartItems): array
    {
        try {
            $gatewayResult = $this->gateway->createPurchase($customerData, $cartItems);

            if (! $gatewayResult['success']) {
                throw new Exception($gatewayResult['error'] ?? 'Payment processing failed');
            }

            return $gatewayResult;

        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('Payment gateway processing failed', [
                    'error' => $e->getMessage(),
                    'customer_data' => $customerData,
                ]);
            }

            throw $e;
        }
    }

    /**
     * Get purchase status from payment gateway
     */
    public function getPurchaseStatus(string $purchaseId): ?array
    {
        try {
            return $this->gateway->getPurchaseStatus($purchaseId);
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to get purchase status', [
                    'purchase_id' => $purchaseId,
                    'error' => $e->getMessage(),
                ]);
            }

            return null;
        }
    }

    /**
     * Get available payment methods
     */
    public function getAvailablePaymentMethods(): array
    {
        return $this->gateway->getAvailablePaymentMethods();
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(Payment $payment, string $status): Payment
    {
        $payment->update(['status' => $status]);

        if ($this->logger) {
            $this->logger->info('Payment status updated', [
                'payment_id' => $payment->id,
                'payment_reference' => $payment->reference,
                'old_status' => $payment->getOriginal('status'),
                'new_status' => $status,
            ]);
        }

        return $payment->fresh();
    }

    /**
     * Get payment by reference
     */
    public function getPaymentByReference(string $reference): ?Payment
    {
        return Payment::where('reference', $reference)->first();
    }

    /**
     * Get payments by order
     */
    public function getPaymentsByOrderId(int $orderId): \Illuminate\Database\Eloquent\Collection
    {
        return Payment::where('order_id', $orderId)->orderBy('created_at', 'desc')->get();
    }

    /**
     * Calculate total payment amount from cart items
     */
    public function calculatePaymentAmount(array $cartItems, ?float $shippingCost = null): int
    {
        $itemsTotal = collect($cartItems)->sum(fn ($item) => $item['price'] * $item['quantity']);

        if ($shippingCost !== null) {
            $shippingInCents = (int) ($shippingCost * 100);

            return $itemsTotal + $shippingInCents;
        }

        return $itemsTotal;
    }

    /**
     * Check if payment is successful
     */
    public function isPaymentSuccessful(Payment $payment): bool
    {
        return in_array($payment->status, ['completed', 'paid', 'success']);
    }

    /**
     * Check if payment is pending
     */
    public function isPaymentPending(Payment $payment): bool
    {
        return in_array($payment->status, ['pending', 'processing']);
    }

    /**
     * Check if payment failed
     */
    public function isPaymentFailed(Payment $payment): bool
    {
        return in_array($payment->status, ['failed', 'cancelled', 'expired']);
    }

    /**
     * Create a payment intent and store it in cart metadata
     */
    public function createPaymentIntent(Cart $cart, array $customerData): array
    {
        $cartItems = $cart->getItems()->toArray();
        $cartVersion = $this->getCartVersion($cart) + 1; // Add +1 to account for metadata update

        // Create payment with gateway
        $result = $this->gateway->createPurchase($customerData, $cartItems);

        if ($result['success']) {
            // Store intent in cart metadata
            $cart->setMetadata('payment_intent', [
                'purchase_id' => $result['purchase_id'],
                'amount' => $cart->total()->getAmount(), // Calculate after potential condition changes
                'cart_version' => $cartVersion,
                'cart_snapshot' => $cartItems,
                'customer_data' => $customerData,
                'created_at' => now()->toISOString(),
                'expires_at' => now()->addMinutes(30)->toISOString(),
                'status' => 'created',
                'checkout_url' => $result['checkout_url'],
            ]);

            Log::info('Payment intent created and stored in cart', [
                'purchase_id' => $result['purchase_id'],
                'cart_total' => $cart->total()->getAmount(),
                'cart_version' => $cartVersion,
            ]);
        }

        return $result;
    }

    /**
     * Validate if cart payment intent is still valid
     */
    public function validateCartPaymentIntent(Cart $cart): array
    {
        $intent = $cart->getMetadata('payment_intent');
        if (! $intent) {
            return [
                'is_valid' => false,
                'reason' => 'no_intent',
                'cart_changed' => false,
                'expired' => false,
            ];
        }

        $currentVersion = $this->getCartVersion($cart);
        $currentTotal = $cart->total()->getAmount(); // Use amount (cents) for consistency
        $expired = now()->isAfter(\Carbon\Carbon::parse($intent['expires_at']));

        return [
            'is_valid' => $intent['cart_version'] === $currentVersion &&
                         ! $expired &&
                         $intent['status'] === 'created', // Skip amount check for now
            'cart_changed' => $intent['cart_version'] !== $currentVersion,
            'amount_changed' => $intent['amount'] !== $currentTotal,
            'expired' => $expired,
            'status' => $intent['status'] ?? 'unknown',
            'intent' => $intent,
        ];
    }

    /**
     * Clear payment intent from cart metadata
     */
    public function clearPaymentIntent(Cart $cart): void
    {
        $intent = $cart->getMetadata('payment_intent');
        if ($intent) {
            Log::info('Clearing payment intent from cart', [
                'purchase_id' => $intent['purchase_id'] ?? 'unknown',
            ]);
        }

        $cart->removeMetadata('payment_intent');
    }

    /**
     * Update payment intent status
     */
    public function updatePaymentIntentStatus(Cart $cart, string $status, array $additionalData = []): void
    {
        $intent = $cart->getMetadata('payment_intent');
        if (! $intent) {
            return;
        }

        $updatedIntent = array_merge($intent, [
            'status' => $status,
            'updated_at' => now()->toISOString(),
        ], $additionalData);

        $cart->setMetadata('payment_intent', $updatedIntent);

        Log::info('Payment intent status updated', [
            'purchase_id' => $intent['purchase_id'],
            'status' => $status,
        ]);
    }

    /**
     * Validate payment webhook data against cart intent
     */
    public function validatePaymentWebhook(array $paymentIntent, array $webhookData): bool
    {
        // Validate purchase ID matches
        if ($paymentIntent['purchase_id'] !== $webhookData['purchase_id']) {
            Log::error('Webhook purchase ID mismatch', [
                'intent_purchase_id' => $paymentIntent['purchase_id'],
                'webhook_purchase_id' => $webhookData['purchase_id'],
            ]);

            return false;
        }

        // Validate payment amount matches cart total
        if ($paymentIntent['amount'] !== $webhookData['amount']) {
            Log::error('Webhook amount mismatch', [
                'intent_amount' => $paymentIntent['amount'],
                'webhook_amount' => $webhookData['amount'],
            ]);

            return false;
        }

        // Validate payment intent is in correct status
        if ($paymentIntent['status'] !== 'created') {
            Log::error('Payment intent not in created status', [
                'purchase_id' => $paymentIntent['purchase_id'],
                'status' => $paymentIntent['status'],
            ]);

            return false;
        }

        return true;
    }

    /**
     * Get payment intent expiry time in minutes
     */
    public function getPaymentIntentExpiryMinutes(): int
    {
        return 30; // Default 30 minutes
    }

    // ==========================================
    // Cart Payment Intent Methods
    // ==========================================

    /**
     * Get the current version of the cart from database
     */
    private function getCartVersion(Cart $cart): int
    {
        return DB::table('carts')
            ->where('identifier', $cart->getIdentifier())
            ->where('instance', $cart->instance())
            ->value('version') ?? 1;
    }
}
