<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MasyukAI\Cart\Facades\Cart;

class CheckoutService
{
    protected PaymentMethodService $paymentMethodService;

    protected ShippingService $shippingService;
    
    protected PaymentGatewayInterface $paymentGateway;

    public function __construct(
        ?PaymentGatewayInterface $paymentGateway = null,
        ?PaymentMethodService $paymentMethodService = null,
        ?ShippingService $shippingService = null
    ) {
        $this->paymentGateway = $paymentGateway ?? app()->make(PaymentGatewayInterface::class);
        $this->paymentMethodService = $paymentMethodService ?? new PaymentMethodService;
        $this->shippingService = $shippingService ?? new ShippingService;
    }

    /**
     * Create payment using the configured payment gateway
     */
    public function createPayment(array $customerData, array $cartItems): array
    {
        try {
            return DB::transaction(function () use ($customerData, $cartItems) {
                // Create or find user record (guest or registered)
                $user = $this->createOrFindUser($customerData);

                // Create address record
                $address = $this->createAddress($user, $customerData);

                // Create order record
                $order = $this->createOrder($user, $address, $customerData, $cartItems);

                // Add reference to customer data
                $customerDataWithReference = array_merge($customerData, [
                    'reference' => $order->order_number, // Use order number as reference
                ]);

                // Process payment with the gateway
                $gatewayResult = $this->paymentGateway->createPurchase($customerDataWithReference, $cartItems);

                if (!$gatewayResult['success']) {
                    throw new \Exception($gatewayResult['error'] ?? 'Payment processing failed');
                }

                // Create payment record
                $payment = Payment::create([
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
            'company' => $customerData['company_name'] ?? null,
            'line1' => $customerData['address'] ?? $customerData['street'] ?? '',
            'line2' => $customerData['address2'] ?? null,
            'city' => $customerData['city'] ?? '',
            'state' => $customerData['state'] ?? '',
            'postal_code' => $customerData['zip_code'] ?? $customerData['postal_code'] ?? '',
            'country' => $customerData['country'] ?? 'Malaysia',
            'phone' => $customerData['phone'] ?? null,
            'type' => $customerData['address_type'] ?? null,
            'is_primary' => true,
        ]);
    }

    /**
     * Create order record
     */
    protected function createOrder(User $user, Address $address, array $customerData, array $cartItems): Order
    {
        $subtotal = collect($cartItems)->sum(fn ($item) => $item['price'] * $item['quantity']);
        
        // Check if there's a shipping condition in the cart
        $shippingValue = \MasyukAI\Cart\Facades\Cart::getShippingValue();
        
        $shipping = 0;
        if ($shippingValue !== null) {
            $shipping = (int) ($shippingValue * 100); // Convert to cents
        } else {
            // Fall back to calculating shipping if no condition exists
            $shipping = $this->shippingService->calculateShipping($customerData['delivery_method'] ?? 'standard');
        }
        
        $tax = 0; // No tax applied
        $total = $subtotal + $shipping + $tax;

        $order = Order::create([
            'user_id' => $user->id,
            'address_id' => $address->id,
            'order_number' => Order::generateOrderNumber(),
            'status' => 'pending',
            'cart_items' => $cartItems, // Keep for backward compatibility
            'delivery_method' => $customerData['delivery_method'] ?? 'standard',
            'checkout_form_data' => $customerData,
            'total' => $total,
        ]);

        // Create order items
        $this->createOrderItems($order, $cartItems);

        return $order;
    }

    /**
     * Create order items from cart items
     */
    protected function createOrderItems(Order $order, array $cartItems): void
    {
        foreach ($cartItems as $item) {
            // Find product by ID or name (depending on cart structure)
            $product = null;

            if (isset($item['id'])) {
                $product = Product::find($item['id']);
            } elseif (isset($item['product_id'])) {
                $product = Product::find($item['product_id']);
            } elseif (isset($item['name'])) {
                $product = Product::where('name', $item['name'])->first();
            }

            if (! $product) {
                // Log warning but continue - maybe it's a custom item
                Log::warning('Product not found for order item', [
                    'order_id' => $order->id,
                    'item' => $item,
                ]);

                continue;
            }

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'], // Price at time of purchase
            ]);
        }
    }

    /**
     * Get available payment methods for Malaysia
     */
    public function getAvailablePaymentMethods(): array
    {
        return $this->paymentGateway->getAvailablePaymentMethods();
    }

    /**
     * Get available shipping methods
     */
    public function getAvailableShippingMethods(): array
    {
        return $this->shippingService->getAvailableShippingMethods();
    }

    /**
     * Get payment methods grouped by type
     */
    public function getGroupedPaymentMethods(): array
    {
        return $this->paymentMethodService->getGroupedPaymentMethods();
    }

    /**
     * Check if payment method is available
     */
    public function isPaymentMethodAvailable(string $id): bool
    {
        return $this->paymentMethodService->isPaymentMethodAvailable($id);
    }

    /**
     * Calculate total amount from cart items, including shipping
     */
    protected function calculateCartTotal(array $cartItems): int
    {
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
}
