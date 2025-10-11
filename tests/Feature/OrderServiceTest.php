<?php

declare(strict_types=1);

use AIArmada\Cart\Facades\Cart;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('order service can create order with optimized code generation', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['addressable_id' => $user->id, 'addressable_type' => User::class]);
    $product = Product::factory()->create(['price' => 2000]); // $20.00 in cents

    $cartItems = [
        [
            'id' => $product->id,
            'name' => $product->name,
            'price' => 2000,
            'quantity' => 2,
        ],
    ];

    $customerData = [
        'delivery_method' => 'standard',
    ];

    $orderService = new OrderService;

    $order = $orderService->createOrder($user, $address, $customerData, $cartItems);

    expect($order)->toBeInstanceOf(Order::class);
    expect($order->user_id)->toBe($user->id);
    expect($order->address_id)->toBe($address->id);
    expect($order->status)->toBe('pending');
    expect($order->delivery_method)->toBe('standard');
    expect($order->order_number)->toMatch('/^ORD\d{2}-[A-Z0-9]{6}$/');
    expect($order->order_number)->toStartWith('ORD'.now()->format('y'));

    // Check that order items were created
    expect($order->orderItems)->toHaveCount(1);
    expect($order->orderItems->first()->product_id)->toBe($product->id);
    expect($order->orderItems->first()->quantity)->toBe(2);
    expect($order->orderItems->first()->unit_price)->toBe(2000);
});

test('order service uses cart snapshot totals when provided', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create([
        'addressable_id' => $user->id,
        'addressable_type' => User::class,
    ]);
    $product = Product::factory()->create(['price' => 2000]);

    $cartItems = [[
        'id' => $product->id,
        'name' => $product->name,
        'price' => 2000,
        'quantity' => 1,
    ]];
    $customerData = [
        'name' => 'Snapshot User',
        'email' => 'snapshot@example.com',
        'phone' => '123456789',
        'street1' => '123 Snapshot Lane',
        'state' => 'Selangor',
        'country' => 'MY',
        'postcode' => '40100',
        'delivery_method' => 'standard',
    ];

    Cart::shouldReceive('getRawSubtotal')->never();
    Cart::shouldReceive('getShippingValue')->never();
    Cart::shouldReceive('getRawTotal')->never();

    $cartSnapshot = [
        'totals' => [
            'subtotal' => 2000,
            'subtotal_without_conditions' => 2000,
            'total' => 2500,
        ],
        'conditions' => [],
    ];

    $orderService = new OrderService;

    $order = $orderService->createOrder($user, $address, $customerData, $cartItems, $cartSnapshot);

    expect($order->total)->toBe(2500);
});

test('order service handles duplicate order numbers gracefully', function () {
    // Create an existing order
    $existingOrder = Order::factory()->create(['order_number' => 'ORD25-TEST01']);

    $user = User::factory()->create();
    $address = Address::factory()->create(['addressable_id' => $user->id, 'addressable_type' => User::class]);

    $orderService = new OrderService;

    // Create multiple orders rapidly
    $orderNumbers = [];
    for ($i = 0; $i < 3; $i++) {
        $order = $orderService->createOrderWithRetry([
            'user_id' => $user->id,
            'address_id' => $address->id,
            'status' => 'pending',
            'cart_items' => [],
            'delivery_method' => 'standard',
            'checkout_form_data' => [],
            'total' => 1000,
        ]);

        $orderNumbers[] = $order->order_number;
    }

    // All order numbers should be unique
    expect(count(array_unique($orderNumbers)))->toBe(3);

    // None should match the existing order
    foreach ($orderNumbers as $orderNumber) {
        expect($orderNumber)->not->toBe($existingOrder->order_number);
    }
});

test('order service can calculate order totals', function () {
    // Mock Cart facade
    Cart::shouldReceive('getRawSubtotal')->andReturn(4000.0); // $40.00
    Cart::shouldReceive('getShippingValue')->andReturn(500.0); // Already in cents: $5.00 = 500 cents
    Cart::shouldReceive('getRawTotal')->andReturn(4500.0); // $45.00

    $orderService = new OrderService;

    $totals = $orderService->calculateOrderTotals(['delivery_method' => 'express']);

    expect($totals)->toHaveKey('subtotal');
    expect($totals)->toHaveKey('shipping');
    expect($totals)->toHaveKey('tax');
    expect($totals)->toHaveKey('total');

    expect($totals['subtotal'])->toBe(4000);
    expect($totals['shipping'])->toBe(500); // Cart returns 500.0, we cast to int
    expect($totals['tax'])->toBe(0);
    expect($totals['total'])->toBe(4500);
});

test('order service can update order status', function () {
    $order = Order::factory()->create(['status' => 'pending']);
    $orderService = new OrderService;

    $updatedOrder = $orderService->updateOrderStatus($order, 'processing');

    expect($updatedOrder->status)->toBe('processing');
    expect($updatedOrder->id)->toBe($order->id);
});

test('order service can get order by number', function () {
    $order = Order::factory()->create(['order_number' => 'ORD25-ABC123']);
    $orderService = new OrderService;

    $foundOrder = $orderService->getOrderByNumber('ORD25-ABC123');

    expect($foundOrder)->not->toBeNull();
    expect($foundOrder->id)->toBe($order->id);
    expect($foundOrder->order_number)->toBe('ORD25-ABC123');

    // Test with non-existent order number
    $notFound = $orderService->getOrderByNumber('ORD25-NOTFOUND');
    expect($notFound)->toBeNull();
});

test('order service can get user orders with pagination', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // Create orders for the user
    Order::factory()->count(15)->create(['user_id' => $user->id]);

    // Create orders for another user (should not be included)
    Order::factory()->count(5)->create(['user_id' => $otherUser->id]);

    $orderService = new OrderService;

    $userOrders = $orderService->getUserOrders($user, 10);

    expect($userOrders)->toHaveCount(10); // Limited to 10 per page
    expect($userOrders->total())->toBe(15); // Total user orders

    // All orders should belong to the user
    foreach ($userOrders as $order) {
        expect($order->user_id)->toBe($user->id);
    }
});

test('order service creates order items correctly', function () {
    $order = Order::factory()->create();
    $product1 = Product::factory()->create(['name' => 'Product 1']);
    $product2 = Product::factory()->create(['name' => 'Product 2']);

    $cartItems = [
        [
            'id' => $product1->id,
            'name' => $product1->name,
            'price' => 1500,
            'quantity' => 2,
        ],
        [
            'product_id' => $product2->id,
            'name' => $product2->name,
            'price' => 2500,
            'quantity' => 1,
        ],
        [
            'name' => 'Non-existent Product',
            'price' => 1000,
            'quantity' => 1,
        ],
    ];

    $orderService = new OrderService;
    $orderService->createOrderItems($order, $cartItems);

    $orderItems = OrderItem::where('order_id', $order->id)->get();

    // Should create 2 items (excluding the non-existent product)
    expect($orderItems)->toHaveCount(2);

    // Check first item
    $item1 = $orderItems->where('product_id', $product1->id)->first();
    expect($item1)->not->toBeNull();
    expect($item1->quantity)->toBe(2);
    expect($item1->unit_price)->toBe(1500);

    // Check second item
    $item2 = $orderItems->where('product_id', $product2->id)->first();
    expect($item2)->not->toBeNull();
    expect($item2->quantity)->toBe(1);
    expect($item2->unit_price)->toBe(2500);
});
