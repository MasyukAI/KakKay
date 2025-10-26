<?php

declare(strict_types=1);

namespace App\Services;

use AIArmada\Cart\Facades\Cart;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

final class OrderService
{
    private ShippingService $shippingService;

    public function __construct(?ShippingService $shippingService = null)
    {
        $this->shippingService = $shippingService ?? new ShippingService;
    }

    /**
     * Create order record with optimized code generation
     *
     * @param  array<string, mixed>  $customerData
     * @param  array<array<string, mixed>>  $cartItems
     * @param  array<string, mixed>|null  $cartSnapshot
     */
    public function createOrder(
        User $user,
        Address $address,
        array $customerData,
        array $cartItems,
        ?array $cartSnapshot = null
    ): Order {
        $totals = $this->resolveTotals($customerData, $cartSnapshot);

        // Create order with optimized code generation and retry on uniqueness violations
        $order = $this->createOrderWithRetry([
            'user_id' => $user->id,
            'address_id' => $address->id,
            'status' => 'pending',
            'cart_items' => $cartItems, // Keep for backward compatibility
            'delivery_method' => $customerData['delivery_method'] ?? 'standard',
            'checkout_form_data' => $customerData,
            'total' => $totals['total'],
        ]);

        // Create order items
        $this->createOrderItems($order, $cartItems);

        return $order;
    }

    /**
     * Create order with retry logic for unique order_number generation
     *
     * @param  array<string, mixed>  $attributes
     */
    public function createOrderWithRetry(array $attributes, int $maxRetries = 3): Order
    {
        $retries = 0;

        do {
            try {
                // Generate order number using optimized approach (no pre-check)
                $attributes['order_number'] = CodeGeneratorService::generateOrderCode();

                return Order::create($attributes);

            } catch (QueryException $e) {
                // Check if it's a PostgreSQL unique constraint violation (23505)
                if ($e->getCode() === '23505' && $retries < $maxRetries) {
                    $retries++;

                    Log::info('Order number collision detected, retrying', [
                        'attempt' => $retries,
                        'order_number' => $attributes['order_number'] ?? 'unknown',
                    ]);

                    // Remove order_number to force regeneration
                    unset($attributes['order_number']);

                    continue;
                }

                // Re-throw if not a uniqueness violation or max retries reached
                throw $e;
            }
        } while ($retries < $maxRetries);

        throw new Exception("Unable to create order with unique order number after {$maxRetries} attempts");
    }

    /**
     * Create order items from cart items
     *
     * @param  array<array<string, mixed>>  $cartItems
     */
    public function createOrderItems(Order $order, array $cartItems): void
    {
        // Collect all product IDs first
        $productIds = collect($cartItems)
            ->map(fn ($item) => $item['id'] ?? $item['product_id'] ?? null)
            ->filter()
            ->unique()
            ->toArray();

        // Single query to load all products (fixes N+1 issue)
        $products = Product::whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        foreach ($cartItems as $item) {
            // Find product by ID
            $productId = $item['id'] ?? $item['product_id'] ?? null;
            $product = $productId ? ($products[$productId] ?? null) : null;

            // Fallback: try finding by name if ID lookup failed
            if (! $product && isset($item['name'])) {
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
                'name' => $product->name,
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'], // Price at time of purchase
                'total' => $item['price'] * $item['quantity'],
            ]);
        }
    }

    /**
     * Calculate order totals using cart calculations
     *
     * @param  array<string, mixed>  $customerData
     * @return array{subtotal: int, shipping: int, tax: int, total: int}
     */
    public function calculateOrderTotals(array $customerData): array
    {
        // Use cart's built-in calculation methods instead of manual calculation
        $subtotal = (int) Cart::getRawSubtotal(); // Already includes item-level conditions

        // Get shipping from cart conditions (preferred) or calculate fallback
        $shipping = 0;
        $shippingValue = Cart::getShippingValue();
        if ($shippingValue !== null) {
            // Cart::getShippingValue() returns cents already, just cast to int
            $shipping = (int) $shippingValue;
        } else {
            // Fall back to calculating shipping if no condition exists
            $shipping = $this->shippingService->calculateShipping($customerData['delivery_method'] ?? 'standard');
        }

        $tax = 0; // No tax applied (could use Cart::getCondition('tax') if needed)

        // Use cart's total calculation method - this includes all cart-level conditions
        $total = (int) Cart::getRawTotal();

        // If no cart-level conditions exist, total will equal subtotal, so add shipping
        if ($total === $subtotal) {
            $total = $subtotal + $shipping + $tax;
        }

        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $total,
        ];
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Order $order, string $status): Order
    {
        $order->update(['status' => $status]);

        Log::info('Order status updated', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'old_status' => $order->getOriginal('status'),
            'new_status' => $status,
        ]);

        return $order->fresh();
    }

    /**
     * Get order by order number
     */
    public function getOrderByNumber(string $orderNumber): ?Order
    {
        return Order::where('order_number', $orderNumber)->first();
    }

    /**
     * Get orders for a user
     */
    /** @phpstan-ignore-next-line */
    public function getUserOrders(User $user, int $limit = 10): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Order::where('user_id', $user->id)
            ->with(['orderItems.product', 'address'])
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
    }

    /**
     * Resolve monetary totals for an order, preferring cart snapshot data when available.
     *
     * @param  array<string, mixed>  $customerData
     * @param  array<string, mixed>|null  $cartSnapshot
     * @return array{subtotal:int, shipping:int, tax:int, total:int}
     */
    private function resolveTotals(array $customerData, ?array $cartSnapshot = null): array
    {
        if ($cartSnapshot !== null) {
            $snapshotTotals = $cartSnapshot['totals'] ?? [];

            $subtotalWithConditions = (int) ($snapshotTotals['subtotal']
                ?? $snapshotTotals['subtotal_without_conditions']
                ?? 0);

            $total = (int) ($snapshotTotals['total'] ?? $subtotalWithConditions);

            $shipping = max(0, $total - $subtotalWithConditions);

            return [
                'subtotal' => $subtotalWithConditions,
                'shipping' => $shipping,
                'tax' => 0,
                'total' => $total,
            ];
        }

        $subtotal = (int) Cart::getRawSubtotal();

        $shippingValue = Cart::getShippingValue();
        $shipping = $shippingValue !== null
            ? (int) $shippingValue
            : $this->shippingService->calculateShipping($customerData['delivery_method'] ?? 'standard');

        $tax = 0; // No tax applied (could use Cart::getCondition('tax') if needed)

        $total = (int) Cart::getRawTotal();

        if ($total === $subtotal) {
            $total = $subtotal + $shipping + $tax;
        }

        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $total,
        ];
    }
}
