<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Address;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Facades\Cart as CartFacade;

final class CheckoutService
{
    public function __construct(
        private PaymentService $paymentService,
        private OrderService $orderService
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

        } catch (Exception $e) {
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
     * Implements idempotency to prevent duplicate orders
     */
    public function handlePaymentSuccess(string $purchaseId, array $webhookData): ?Order
    {
        try {
            // Check if order already exists for this purchase (idempotency)
            $existingOrder = Order::whereHas('payments', function ($query) use ($purchaseId) {
                $query->where('gateway_payment_id', $purchaseId);
            })->first();

            if ($existingOrder) {
                Log::info('Order already exists for purchase, skipping duplicate creation', [
                    'order_id' => $existingOrder->id,
                    'order_number' => $existingOrder->order_number,
                    'purchase_id' => $purchaseId,
                ]);

                return $existingOrder;
            }

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

            $order = DB::transaction(function () use ($paymentIntent, $webhookData, $purchaseId) {
                // Create order from cart snapshot
                $order = $this->createOrderFromCartSnapshot(
                    $paymentIntent['cart_snapshot'],
                    $paymentIntent['customer_data']
                );

                // Create payment record
                $payment = $this->createPaymentRecord($order, $paymentIntent, $webhookData);

                Log::info('Order created successfully from cart payment intent', [
                    'order_id' => $order->id,
                    'purchase_id' => $purchaseId,
                    'amount' => $paymentIntent['amount'],
                ]);

                return $order;
            });

            // Clear cart AFTER transaction commits successfully
            // This ensures cart is cleared even if there are minor issues later
            // And cart remains available if transaction fails
            try {
                $cart->clear();
                Log::info('Cart cleared successfully after order creation', [
                    'order_id' => $order->id,
                    'purchase_id' => $purchaseId,
                ]);
            } catch (Exception $e) {
                Log::error('Failed to clear cart after order creation', [
                    'order_id' => $order->id,
                    'purchase_id' => $purchaseId,
                    'error' => $e->getMessage(),
                ]);
                // Don't fail the order creation if cart clearing fails
            }

            return $order;

        } catch (Exception $e) {
            Log::error('Failed to handle payment success', [
                'purchase_id' => $purchaseId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get cart change validation for UI display
     */
    public function getCartChangeStatus(): array
    {
        $cart = CartFacade::getCurrentCart();
        $validation = $this->paymentService->validateCartPaymentIntent($cart);

        return [
            'is_valid' => $validation['is_valid'],
            'cart_changed' => $validation['cart_changed'] ?? false,
            'intent' => $validation['intent'] ?? null,
        ];
    }

    /**
     * Create order from cart snapshot stored in payment intent
     *
     * Cart snapshot structure:
     * [
     *   'items' => [...],
     *   'conditions' => [...],
     *   'totals' => ['subtotal' => 0, 'total' => 0, 'savings' => 0]
     * ]
     */
    private function createOrderFromCartSnapshot(array $cartSnapshot, array $customerData): Order
    {
        // Create or find user
        $user = $this->createOrFindUser($customerData);

        // Create address record
        $address = $this->createAddress($user, $customerData);

        // Extract items from snapshot (handles both old and new format)
        $cartItems = $cartSnapshot['items'] ?? $cartSnapshot;

        // Create order using OrderService with cart items
        return $this->orderService->createOrder($user, $address, $customerData, $cartItems);
    }

    /**
     * Create payment record for completed order
     */
    private function createPaymentRecord(Order $order, array $paymentIntent, array $webhookData): Payment
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
     * Find cart by reference (cart ID) using direct primary key lookup
     * Much faster than JSONB query - uses primary key index
     */
    private function findCartByPurchaseId(string $purchaseId): ?Cart
    {
        // First, get the purchase from CHIP to extract the reference (cart ID)
        $purchaseStatus = $this->paymentService->getPurchaseStatus($purchaseId);

        if (! $purchaseStatus || ! isset($purchaseStatus['reference'])) {
            Log::warning('Purchase status missing or no reference found', [
                'purchase_id' => $purchaseId,
            ]);

            return null;
        }

        $cartId = $purchaseStatus['reference'];

        // Direct primary key lookup - blazing fast!
        $cartData = DB::table('carts')->where('id', $cartId)->first();

        if (! $cartData) {
            Log::warning('Cart not found for reference', [
                'purchase_id' => $purchaseId,
                'cart_id' => $cartId,
            ]);

            return null;
        }

        // Get CartManager and reconstruct Cart instance from database
        $cartManager = app(\MasyukAI\Cart\CartManager::class);

        return $cartManager->getCartInstance(
            $cartData->instance,
            $cartData->identifier
        );
    }

    /**
     * Create or find user record
     */
    private function createOrFindUser(array $customerData): User
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
     * Create address record using polymorphic relationship
     */
    private function createAddress(User $user, array $customerData): Address
    {
        return Address::create([
            // Polymorphic relationship fields (addresses table uses addressable)
            'addressable_type' => User::class,
            'addressable_id' => $user->id,

            // Address fields matching database schema
            'name' => $customerData['name'],
            'company' => $customerData['company'] ?? null,
            'street1' => $customerData['street1'],
            'street2' => $customerData['street2'] ?? null,
            'city' => $customerData['city'] ?? null,
            'state' => $customerData['state'],
            'country' => $customerData['country'],
            'postcode' => $customerData['postcode'],
            'phone' => $customerData['phone'],
            'type' => $customerData['type'] ?? 'billing',
            'is_primary' => true,
        ]);
    }
}
