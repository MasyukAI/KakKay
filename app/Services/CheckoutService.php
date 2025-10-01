<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Facades\Cart as CartFacade;

class CheckoutService
{
    public function __construct(
        protected PaymentService $paymentService,
        protected OrderService $orderService
    ) {}

    /**
     * Process checkout with cart metadata-based payment intents
     */
    public function processCheckout(array $customerData): array
    {
        try {
            $cart = CartFacade::getCurrentCart();

            // Validate cart has items
            if ($cart->isEmpty()) {
                return [
                    'success' => false,
                    'error' => 'Cart is empty',
                ];
            }

            // Check for existing valid payment intent
            $validation = $this->paymentService->validateCartPaymentIntent($cart);

            if ($validation['is_valid']) {
                // Reuse existing valid payment intent
                return [
                    'success' => true,
                    'purchase_id' => $validation['intent']['purchase_id'],
                    'checkout_url' => $validation['intent']['checkout_url'],
                    'reused_intent' => true,
                ];
            }

            // Clear invalid/expired payment intent
            if (! $validation['is_valid'] && isset($validation['intent'])) {
                $this->paymentService->clearPaymentIntent($cart);
            }

            // Create new payment intent
            return $this->paymentService->createPaymentIntent($cart, $customerData);

        } catch (\Exception $e) {
            Log::error('Enhanced checkout failed', [
                'error' => $e->getMessage(),
                'customer_data' => $customerData,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle successful payment webhook and create order
     */
    public function handlePaymentSuccess(string $purchaseId, array $webhookData): ?Order
    {
        try {
            // Find cart with this payment intent
            $cart = $this->findCartByPurchaseId($purchaseId);
            if (! $cart) {
                Log::error('Cart not found for successful payment', [
                    'purchase_id' => $purchaseId,
                ]);

                return null;
            }

            $paymentIntent = $cart->getMetadata('payment_intent');
            if (! $paymentIntent) {
                Log::error('Payment intent not found in cart metadata', [
                    'purchase_id' => $purchaseId,
                ]);

                return null;
            }

            // Validate webhook data against payment intent
            if (! $this->paymentService->validatePaymentWebhook($paymentIntent, $webhookData)) {
                return null;
            }

            return DB::transaction(function () use ($cart, $paymentIntent, $webhookData, $purchaseId) {
                // Create order from cart snapshot
                $order = $this->createOrderFromCartSnapshot(
                    $paymentIntent['cart_snapshot'],
                    $paymentIntent['customer_data']
                );

                // Create payment record
                $payment = $this->createPaymentRecord($order, $paymentIntent, $webhookData);

                // Update payment intent status
                $this->paymentService->updatePaymentIntentStatus($cart, 'completed', [
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                    'completed_at' => now()->toISOString(),
                ]);

                // Clear cart after successful order creation
                $cart->clear();

                Log::info('Order created successfully from cart payment intent', [
                    'order_id' => $order->id,
                    'purchase_id' => $purchaseId,
                    'amount' => $paymentIntent['amount'],
                ]);

                return $order;
            });

        } catch (\Exception $e) {
            Log::error('Failed to handle payment success', [
                'purchase_id' => $purchaseId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create order from cart snapshot stored in payment intent
     */
    protected function createOrderFromCartSnapshot(array $cartSnapshot, array $customerData): Order
    {
        // Create or find user
        $user = $this->createOrFindUser($customerData);

        // Create address record
        $address = $this->createAddress($user, $customerData);

        // Create order using OrderService with cart snapshot
        return $this->orderService->createOrder($user, $address, $customerData, $cartSnapshot);
    }

    /**
     * Create payment record for completed order
     */
    protected function createPaymentRecord(Order $order, array $paymentIntent, array $webhookData): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'amount' => $paymentIntent['amount'],
            'status' => 'completed',
            'method' => 'chip',
            'currency' => 'MYR',
            'gateway_payment_id' => $paymentIntent['purchase_id'],
            'gateway_response' => $webhookData,
            'completed_at' => now(),
        ]);
    }

    /**
     * Find cart by purchase ID - temporary implementation
     * TODO: Implement proper cart lookup by iterating through user's carts
     */
    protected function findCartByPurchaseId(string $purchaseId): ?Cart
    {
        // For now, we'll use session-based approach since we don't have
        // direct database access to search cart metadata
        // In a production scenario, you'd want to implement a proper lookup

        // Try current session cart first
        $cart = CartFacade::getCurrentCart();
        $intent = $cart->getMetadata('payment_intent');

        if ($intent && $intent['purchase_id'] === $purchaseId) {
            return $cart;
        }

        return null;
    }

    /**
     * Create or find user record
     */
    protected function createOrFindUser(array $customerData): User
    {
        $user = User::where('email', $customerData['email'])->first();

        if (! $user) {
            $user = User::create([
                'name' => $customerData['name'] ?? null,
                'email' => $customerData['email'],
                'phone' => $customerData['phone'] ?? null,
                'is_guest' => true,
            ]);
        }

        return $user;
    }

    /**
     * Create address record
     */
    protected function createAddress(User $user, array $customerData): Address
    {
        return Address::create([
            'user_id' => $user->id,
            'name' => $customerData['name'],
            'company' => $customerData['company'] ?? '',
            'street1' => $customerData['street1'],
            'street2' => $customerData['street2'] ?? '',
            'city' => $customerData['city'] ?? '',
            'state' => $customerData['state'],
            'country' => $customerData['country'],
            'postcode' => $customerData['postcode'],
            'phone' => $customerData['phone'],
        ]);
    }

    /**
     * Get cart change validation for UI display
     */
    public function getCartChangeStatus(): array
    {
        $cart = CartFacade::getCurrentCart();
        $validation = $this->paymentService->validateCartPaymentIntent($cart);

        return [
            'has_active_intent' => isset($validation['intent']),
            'cart_changed' => $validation['cart_changed'] ?? false,
            'amount_changed' => $validation['amount_changed'] ?? false,
            'expired' => $validation['expired'] ?? false,
            'intent' => $validation['intent'] ?? null,
        ];
    }
}
