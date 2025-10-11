<?php

declare(strict_types=1);

use AIArmada\Cart\Conditions\CartCondition;
use AIArmada\Cart\Facades\Cart;
use AIArmada\Chip\Services\WebhookService;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Mock WebhookService to always verify signatures
    $this->mock(WebhookService::class, function ($mock) {
        $mock->shouldReceive('getPublicKey')
            ->with('wh_test')
            ->andReturn('pem-key');
        $mock->shouldReceive('verifySignature')
            ->andReturn(true);
    });
});

test('webhook creates order on first call', function () {
    // Create test data
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 2999]);

    // Authenticate as the user
    $this->actingAs($user);

    // Set up cart with items
    Cart::add(
        (string) $product->id,
        $product->name,
        $product->price,
        1
    );

    $cart = Cart::getCurrentCart();
    $cartId = $cart->getId();

    // Create proper cart snapshot
    $cartSnapshot = [
        'items' => $cart->getItems()->toArray(),
        'conditions' => $cart->getConditions()->toArray(),
        'subtotal' => $cart->getRawSubtotal(),
        'total' => $cart->getRawTotal(),
    ];

    $customerData = [
        'email' => $user->email,
        'name' => 'John Doe',
        'phone' => '+60123456789',
        'street1' => '123 Test Street',
        'city' => 'Kuala Lumpur',
        'state' => 'Kuala Lumpur',
        'postcode' => '50000',
        'country' => 'MY',
    ];

    // Set up payment intent with complete data
    Cart::setMetadata('payment_intent', [
        'purchase_id' => 'test-purchase-123',
        'amount' => 2999,
        'currency' => 'MYR',
        'status' => 'created',
        'cart_snapshot' => $cartSnapshot,
        'customer_data' => $customerData,
        'cart_version' => $cart->getVersion(),
    ]);

    // Mock PaymentService at container level - must be done BEFORE the request
    $paymentServiceMock = $this->partialMock(PaymentService::class, function ($mock) use ($cartId) {
        $mock->shouldReceive('validatePaymentWebhook')
            ->andReturn(true);
        $mock->shouldReceive('getPurchaseStatus')
            ->with('test-purchase-123')
            ->andReturn([
                'id' => 'test-purchase-123',
                'reference' => $cartId,
                'status' => 'paid',
            ]);
    });

    // Prepare webhook payload
    $webhookPayload = [
        'event' => 'purchase.paid',
        'data' => [
            'id' => 'test-purchase-123',
            'amount' => 2999,
            'currency' => 'MYR',
            'status' => 'paid',
            'reference' => $cartId,
            'payment' => [
                'id' => 'payment-123',
            ],
            'transaction_data' => [
                'payment_method' => 'fpx_b2c',
            ],
        ],
    ];

    // Call webhook endpoint
    $response = $this->postJson('/webhooks/chip/wh_test', $webhookPayload);

    // Assertions
    $response->assertOk();

    // Verify order was created
    expect(Order::count())->toBe(1);

    $order = Order::first();
    expect($order)->not->toBeNull();
    expect($order->user_id)->toBe($user->id);

    // Verify payment was created
    expect(Payment::count())->toBe(1);

    $payment = Payment::first();
    expect($payment)->not->toBeNull();
    expect($payment->gateway_payment_id)->toBe('test-purchase-123');
    expect($payment->status)->toBe('completed');
});

test('webhook does not create duplicate order on second call', function () {
    // Create test data
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 2999]);

    // Authenticate as the user
    $this->actingAs($user);

    // Set up cart with items
    Cart::add(
        (string) $product->id,
        $product->name,
        $product->price,
        1
    );

    $cart = Cart::getCurrentCart();
    $cartId = $cart->getId();

    // Create proper cart snapshot
    $cartSnapshot = [
        'items' => $cart->getItems()->toArray(),
        'conditions' => $cart->getConditions()->toArray(),
        'subtotal' => $cart->getRawSubtotal(),
        'total' => $cart->getRawTotal(),
    ];

    $customerData = [
        'email' => $user->email,
        'name' => 'Jane Doe',
        'phone' => '+60123456789',
        'street1' => '456 Test Avenue',
        'city' => 'Kuala Lumpur',
        'state' => 'Kuala Lumpur',
        'postcode' => '50000',
        'country' => 'MY',
    ];

    // Set up payment intent with complete data
    Cart::setMetadata('payment_intent', [
        'purchase_id' => 'test-purchase-456',
        'amount' => 2999,
        'currency' => 'MYR',
        'status' => 'created',
        'cart_snapshot' => $cartSnapshot,
        'customer_data' => $customerData,
        'cart_version' => $cart->getVersion(),
    ]);

    // Mock PaymentService at container level - must be done BEFORE the request
    $this->partialMock(PaymentService::class, function ($mock) use ($cartId) {
        $mock->shouldReceive('validatePaymentWebhook')
            ->andReturn(true);
        $mock->shouldReceive('getPurchaseStatus')
            ->with('test-purchase-456')
            ->andReturn([
                'id' => 'test-purchase-456',
                'reference' => $cartId,
                'status' => 'paid',
            ]);
    });

    // Prepare webhook payload
    $webhookPayload = [
        'event' => 'purchase.paid',
        'data' => [
            'id' => 'test-purchase-456',
            'amount' => 2999,
            'currency' => 'MYR',
            'status' => 'paid',
            'reference' => $cartId,
            'payment' => [
                'id' => 'payment-456',
            ],
            'transaction_data' => [
                'payment_method' => 'fpx_b2c',
            ],
        ],
    ];

    // Call webhook endpoint FIRST time
    $response1 = $this->postJson('/webhooks/chip/wh_test', $webhookPayload);
    $response1->assertOk();

    // Verify order was created
    expect(Order::count())->toBe(1);
    $firstOrderId = Order::first()->id;

    // Call webhook endpoint SECOND time (duplicate)
    $response2 = $this->postJson('/webhooks/chip/wh_test', $webhookPayload);
    $response2->assertOk();

    // Verify NO duplicate order was created
    expect(Order::count())->toBe(1, 'Duplicate webhook should not create second order');

    // Verify it's the same order
    $order = Order::first();
    expect($order->id)->toBe($firstOrderId, 'Should return the same order, not create new one');

    // Verify only one payment exists
    expect(Payment::count())->toBe(1, 'Should not create duplicate payment');
});

test('webhook idempotency works across multiple calls', function () {
    // Create test data
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 5999]);

    // Authenticate as the user
    $this->actingAs($user);

    // Set up cart with items
    Cart::add(
        (string) $product->id,
        $product->name,
        $product->price,
        2
    );

    // Remove any default conditions (like shipping)
    Cart::clearConditions();

    // Add shipping condition for standard delivery (500 cents)
    $shipping = new CartCondition(
        'Shipping',
        'shipping',
        'total',
        500
    );
    Cart::addCondition($shipping);

    $cart = Cart::getCurrentCart();
    $cartId = $cart->getId();

    // Create proper cart snapshot
    $cartSnapshot = [
        'items' => $cart->getItems()->toArray(),
        'conditions' => $cart->getConditions()->toArray(),
        'totals' => [
            'subtotal' => $cart->getRawSubtotal(),
            'total' => $cart->getRawTotal(),
        ],
    ];

    $customerData = [
        'email' => $user->email,
        'name' => 'Bob Smith',
        'phone' => '+60123456789',
        'street1' => '789 Test Boulevard',
        'city' => 'Petaling Jaya',
        'state' => 'Selangor',
        'postcode' => '46000',
        'country' => 'MY',
    ];

    // Set up payment intent with complete data
    Cart::setMetadata('payment_intent', [
        'purchase_id' => 'test-purchase-789',
        'amount' => 11998,
        'currency' => 'MYR',
        'status' => 'created',
        'cart_snapshot' => $cartSnapshot,
        'customer_data' => $customerData,
        'cart_version' => $cart->getVersion(),
    ]);

    // Mock PaymentService at container level - must be done BEFORE the request
    $this->partialMock(PaymentService::class, function ($mock) use ($cartId) {
        $mock->shouldReceive('validatePaymentWebhook')
            ->andReturn(true);
        $mock->shouldReceive('getPurchaseStatus')
            ->with('test-purchase-789')
            ->andReturn([
                'id' => 'test-purchase-789',
                'reference' => $cartId,
                'status' => 'paid',
            ]);
    });

    // Prepare webhook payload
    $webhookPayload = [
        'event' => 'purchase.paid',
        'data' => [
            'id' => 'test-purchase-789',
            'amount' => 11998,
            'currency' => 'MYR',
            'status' => 'paid',
            'reference' => $cartId,
            'payment' => [
                'id' => 'payment-789',
            ],
            'transaction_data' => [
                'payment_method' => 'ewallet',
            ],
        ],
    ];

    // Call webhook 5 times
    foreach (range(1, 5) as $attempt) {
        $response = $this->postJson('/webhooks/chip/wh_test', $webhookPayload);
        $response->assertOk();

        // Always should have exactly 1 order
        expect(Order::count())->toBe(1, "After attempt {$attempt}, should still have exactly 1 order");
        expect(Payment::count())->toBe(1, "After attempt {$attempt}, should still have exactly 1 payment");
    }

    // Verify final state
    $order = Order::first();
    expect($order)->not->toBeNull();
    // Note: Order total includes shipping calculated by ShippingService (500 cents)
    expect($order->total)->toBe(12498); // 11998 items + 500 shipping

    $payment = Payment::first();
    expect($payment)->not->toBeNull();
    // Payment amount should match the order total
    expect($payment->amount)->toBe(12498);
    expect($payment->gateway_payment_id)->toBe('test-purchase-789');
});

test('webhook idempotency check uses correct database query', function () {
    // Create test data
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 1999]);

    // Authenticate as the user
    $this->actingAs($user);

    // Set up cart
    Cart::add((string) $product->id, $product->name, $product->price, 1);

    $cart = Cart::getCurrentCart();
    $cartId = $cart->getId();

    // Create proper cart snapshot
    $cartSnapshot = [
        'items' => $cart->getItems()->toArray(),
        'conditions' => $cart->getConditions()->toArray(),
        'subtotal' => $cart->getRawSubtotal(),
        'total' => $cart->getRawTotal(),
    ];

    $customerData = [
        'email' => $user->email,
        'name' => 'Query Test',
        'phone' => '+60123456789',
        'street1' => '123 Query Street',
        'city' => 'Kuala Lumpur',
        'state' => 'Kuala Lumpur',
        'postcode' => '50000',
        'country' => 'MY',
    ];

    Cart::setMetadata('payment_intent', [
        'purchase_id' => 'test-purchase-query',
        'amount' => 1999,
        'currency' => 'MYR',
        'status' => 'created',
        'cart_snapshot' => $cartSnapshot,
        'customer_data' => $customerData,
        'cart_version' => $cart->getVersion(),
    ]);

    // Mock PaymentService at container level
    $this->partialMock(PaymentService::class, function ($mock) use ($cartId) {
        $mock->shouldReceive('validatePaymentWebhook')
            ->andReturn(true);
        $mock->shouldReceive('getPurchaseStatus')
            ->with('test-purchase-query')
            ->andReturn([
                'id' => 'test-purchase-query',
                'reference' => $cartId,
                'status' => 'paid',
            ]);
    });

    // Enable query logging
    DB::enableQueryLog();

    // Prepare webhook payload
    $webhookPayload = [
        'event' => 'purchase.paid',
        'data' => [
            'id' => 'test-purchase-query',
            'amount' => 1999,
            'currency' => 'MYR',
            'status' => 'paid',
            'reference' => $cartId,
            'payment' => ['id' => 'payment-query'],
            'transaction_data' => ['payment_method' => 'fpx_b2c'],
        ],
    ];

    // Call webhook
    $this->postJson('/webhooks/chip/wh_test', $webhookPayload);

    // Get queries
    $queries = DB::getQueryLog();

    // Find the idempotency check query (checking for existing payments with this purchase ID)
    $idempotencyQuery = collect($queries)->first(function ($query) {
        return str_contains($query['query'], 'gateway_payment_id')
            && str_contains($query['query'], 'payments');
    });

    // Verify the query exists and uses the correct structure
    expect($idempotencyQuery)->not->toBeNull('Idempotency check query should exist');

    // Verify that we're checking by the cart reference (UUID) in the payment_intents table
    $cartReferenceQuery = collect($queries)->first(function ($query) use ($cartId) {
        return str_contains($query['query'], 'carts')
            && str_contains($query['query'], 'id')
            && in_array($cartId, $query['bindings'] ?? []);
    });

    expect($cartReferenceQuery)->not->toBeNull('Cart reference lookup query should exist');
});
