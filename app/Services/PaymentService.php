<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Payment;
use Illuminate\Database\QueryException;
use Psr\Log\LoggerInterface;

class PaymentService
{
    protected PaymentGatewayInterface $gateway;

    protected CodeGeneratorService $codeGenerator;

    protected $paymentCodeGenerator;

    protected ?LoggerInterface $logger;

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

        throw new \Exception("Unable to create payment with unique reference after {$maxRetries} attempts");
    }

    /**
     * Process payment with the gateway
     */
    public function processPayment(array $customerData, array $cartItems): array
    {
        try {
            $gatewayResult = $this->gateway->createPurchase($customerData, $cartItems);

            if (! $gatewayResult['success']) {
                throw new \Exception($gatewayResult['error'] ?? 'Payment processing failed');
            }

            return $gatewayResult;

        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
}
