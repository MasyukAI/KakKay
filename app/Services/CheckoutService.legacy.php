<?php

namespace App\Services;

use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MasyukAI\Cart\Facades\Cart;

class CheckoutService
{
    protected ShippingService $shippingService;

    protected OrderService $orderService;

    protected PaymentService $paymentService;

    public function __construct(
        ?PaymentMethodService $paymentMethodService = null,
        ?ShippingService $shippingService = null,
        ?OrderService $orderService = null,
        ?PaymentService $paymentService = null
    ) {
        $this->shippingService = $shippingService ?? new ShippingService;
        $this->orderService = $orderService ?? new OrderService($this->shippingService);
        $this->paymentService = $paymentService ?? app(PaymentService::class);
    }

    /**
     * Process the complete checkout flow: create order and process payment
     */
    public function processCheckout(array $customerData, array $cartItems): array
    {
        try {
            return DB::transaction(function () use ($customerData, $cartItems) {
                // Create or find user record (guest or registered)
                $user = $this->createOrFindUser($customerData);

                // Create address record
                $address = $this->createAddress($user, $customerData);

                // Create order record using OrderService
                $order = $this->orderService->createOrder($user, $address, $customerData, $cartItems);

                // Add reference to customer data
                $customerDataWithReference = array_merge($customerData, [
                    'reference' => $order->order_number, // Use order number as reference
                ]);

                // Process payment with the gateway
                $gatewayResult = $this->paymentService->processPayment($customerDataWithReference, $cartItems);

                // Create payment record with optimized code generation
                $payment = $this->paymentService->createPaymentWithRetry([
                    'order_id' => $order->id,
                    'amount' => $this->calculateCartTotal($cartItems),
                    'status' => 'pending',
                    'method' => 'chip', // Could be dynamic in the future
                    'currency' => 'MYR',
                    'gateway_payment_id' => $gatewayResult['purchase_id'],
                    'gateway_response' => $gatewayResult['gateway_response'],
                ]);

                return [
                    'success' => true,
                    'order' => $order,
                    'payment' => $payment,
                    'purchase_id' => $gatewayResult['purchase_id'],
                    'checkout_url' => $gatewayResult['checkout_url'],
                ];
            });
        } catch (\Exception $e) {
            Log::error('Payment creation failed', [
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
     * Create or find user record (guest or registered)
     */
    protected function createOrFindUser(array $customerData): User
    {
        // First try to find existing user by email
        $user = User::where('email', $customerData['email'])->first();

        if (! $user) {
            // Create new guest user
            $user = User::create([
                'name' => $customerData['name'] ?? null,
                'email' => $customerData['email'],
                'phone' => $customerData['phone'] ?? null,
                'is_guest' => true,
                'password' => null, // Guest users don't have passwords
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
            'addressable_type' => User::class,
            'addressable_id' => $user->id,
            'name' => $customerData['name'] ?? '',
            'company' => $customerData['company'] ?? null,
            'street1' => $customerData['street1'] ?? '',
            'street2' => $customerData['street2'] ?? null,
            'city' => $customerData['city'] ?? '',
            'state' => $customerData['state'] ?? '',
            'postcode' => $customerData['postcode'] ?? '',
            'country' => $customerData['country'] ?? 'Malaysia',
            'phone' => $customerData['phone'] ?? null,
            'type' => $customerData['address_type'] ?? 'shipping',
            'is_primary' => true,
        ]);
    }

    /**
     * Get available shipping methods
     */
    public function getAvailableShippingMethods(): array
    {
        return $this->shippingService->getAvailableShippingMethods();
    }

    /**
     * Get cart subtotal using built-in cart calculations
     */
    public function getCartSubtotal(): int
    {
        return (int) Cart::getRawSubtotal();
    }

    /**
     * Get cart total using built-in cart calculations
     */
    public function getCartTotal(): int
    {
        return (int) Cart::getRawTotal();
    }

    /**
     * Get cart savings using built-in cart calculations
     */
    public function getCartSavings(): int
    {
        $subtotalWithoutConditions = (int) Cart::getRawSubtotalWithoutConditions();
        $subtotalWithConditions = (int) Cart::getRawSubtotal();

        return max(0, $subtotalWithoutConditions - $subtotalWithConditions);
    }

    /**
     * Get shipping cost (from cart conditions or calculated)
     */
    public function getShippingCost(string $method = 'standard'): int
    {
        $shippingValue = Cart::getShippingValue();
        if ($shippingValue !== null) {
            return (int) ($shippingValue * 100); // Convert to cents
        }

        return $this->shippingService->calculateShipping($method);
    }

    /**
     * Get cart summary with all calculations
     */
    public function getCartSummary(): array
    {
        return [
            'items_count' => Cart::count(),
            'total_quantity' => Cart::getTotalQuantity(),
            'subtotal' => $this->getCartSubtotal(),
            'total' => $this->getCartTotal(),
            'savings' => $this->getCartSavings(),
            'shipping_cost' => $this->getShippingCost(),
            'has_conditions' => Cart::getConditions()->isNotEmpty(),
            'shipping_method' => Cart::getShippingMethod(),
        ];
    }

    /**
     * Calculate total amount from cart using built-in cart calculations
     */
    protected function calculateCartTotal(array $cartItems): int
    {
        // Use cart's built-in total calculation which includes all conditions
        $cartTotal = (int) Cart::getRawTotal();

        // If cart total is available and valid, use it
        if ($cartTotal > 0) {
            return $cartTotal;
        }

        // Fallback to manual calculation only if cart total is not available
        $itemsTotal = collect($cartItems)->sum(fn ($item) => $item['price'] * $item['quantity']);

        // Add shipping cost if available
        $shippingValue = Cart::getShippingValue();
        if ($shippingValue !== null) {
            // Convert shipping from dollars to cents
            $shippingInCents = (int) ($shippingValue * 100);

            return $itemsTotal + $shippingInCents;
        }

        return $itemsTotal;
    }

    /**
     * Get purchase status from payment gateway
     */
    public function getPurchaseStatus(string $purchaseId): ?array
    {
        return $this->paymentService->getPurchaseStatus($purchaseId);
    }
}
