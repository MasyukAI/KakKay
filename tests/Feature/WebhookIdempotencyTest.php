<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Chip\Services\WebhookService;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Mock WebhookService to always verify signatures
    $this->mock(WebhookService::class, function ($mock) {
        $mock->shouldReceive('verifySignature')
            ->andReturn(true);
    });
});

test('webhook creates order on first call', function () {
    // Create test data
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 2999]);

    // Set up cart with payment intent
    Cart::add(
        (string) $product->id,
        $product->name,
        $product->price,
        1
    );

    Cart::setMetadata('payment_intent', [
        'purchase_id' => 'test-purchase-123',
        'amount' => 2999,
        'currency' => 'MYR',
        'status' => 'created',
        'customer_email' => $user->email,
        'customer_name' => 'John Doe',
        'customer_phone' => '+60123456789',
        'customer_address' => [
            'street1' => '123 Test Street',
            'city' => 'Kuala Lumpur',
            'state' => 'Kuala Lumpur',
            'postcode' => '50000',
            'country' => 'MY',
        ],
    ]);

    // Prepare webhook payload
    $webhookPayload = [
        'event' => 'purchase.paid',
        'data' => [
            'id' => 'test-purchase-123',
            'amount' => 2999,
            'currency' => 'MYR',
            'status' => 'paid',
            'reference' => 'ORDER-001',
            'payment' => [
                'id' => 'payment-123',
            ],
            'transaction_data' => [
                'payment_method' => 'fpx_b2c',
            ],
        ],
    ];

    // Call webhook endpoint
    $response = $this->postJson('/webhooks/chip', $webhookPayload);

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

    // Set up cart with payment intent
    Cart::add(
        (string) $product->id,
        $product->name,
        $product->price,
        1
    );

    Cart::setMetadata('payment_intent', [
        'purchase_id' => 'test-purchase-456',
        'amount' => 2999,
        'currency' => 'MYR',
        'status' => 'created',
        'customer_email' => $user->email,
        'customer_name' => 'Jane Doe',
        'customer_phone' => '+60123456789',
        'customer_address' => [
            'street1' => '456 Test Avenue',
            'city' => 'Kuala Lumpur',
            'state' => 'Kuala Lumpur',
            'postcode' => '50000',
            'country' => 'MY',
        ],
    ]);

    // Prepare webhook payload
    $webhookPayload = [
        'event' => 'purchase.paid',
        'data' => [
            'id' => 'test-purchase-456',
            'amount' => 2999,
            'currency' => 'MYR',
            'status' => 'paid',
            'reference' => 'ORDER-002',
            'payment' => [
                'id' => 'payment-456',
            ],
            'transaction_data' => [
                'payment_method' => 'fpx_b2c',
            ],
        ],
    ];

    // Call webhook endpoint FIRST time
    $response1 = $this->postJson('/webhooks/chip', $webhookPayload);
    $response1->assertOk();

    // Verify order was created
    expect(Order::count())->toBe(1);
    $firstOrderId = Order::first()->id;

    // Call webhook endpoint SECOND time (duplicate)
    $response2 = $this->postJson('/webhooks/chip', $webhookPayload);
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

    // Set up cart with payment intent
    Cart::add(
        (string) $product->id,
        $product->name,
        $product->price,
        2
    );

    Cart::setMetadata('payment_intent', [
        'purchase_id' => 'test-purchase-789',
        'amount' => 11998,
        'currency' => 'MYR',
        'status' => 'created',
        'customer_email' => $user->email,
        'customer_name' => 'Bob Smith',
        'customer_phone' => '+60123456789',
        'customer_address' => [
            'street1' => '789 Test Boulevard',
            'city' => 'Petaling Jaya',
            'state' => 'Selangor',
            'postcode' => '46000',
            'country' => 'MY',
        ],
    ]);

    // Prepare webhook payload
    $webhookPayload = [
        'event' => 'purchase.paid',
        'data' => [
            'id' => 'test-purchase-789',
            'amount' => 11998,
            'currency' => 'MYR',
            'status' => 'paid',
            'reference' => 'ORDER-003',
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
        $response = $this->postJson('/webhooks/chip', $webhookPayload);
        $response->assertOk();

        // Always should have exactly 1 order
        expect(Order::count())->toBe(1, "After attempt {$attempt}, should still have exactly 1 order");
        expect(Payment::count())->toBe(1, "After attempt {$attempt}, should still have exactly 1 payment");
    }

    // Verify final state
    $order = Order::first();
    expect($order)->not->toBeNull();
    expect($order->total)->toBe(11998);

    $payment = Payment::first();
    expect($payment)->not->toBeNull();
    expect($payment->amount)->toBe(11998);
    expect($payment->gateway_payment_id)->toBe('test-purchase-789');
});

test('webhook idempotency check uses correct database query', function () {
    // Create test data
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 1999]);

    // Set up cart
    Cart::add((string) $product->id, $product->name, $product->price, 1);

    Cart::setMetadata('payment_intent', [
        'purchase_id' => 'test-purchase-query',
        'amount' => 1999,
        'currency' => 'MYR',
        'status' => 'created',
        'customer_email' => $user->email,
        'customer_name' => 'Query Test',
        'customer_phone' => '+60123456789',
        'customer_address' => [
            'street1' => '123 Query Street',
            'city' => 'Kuala Lumpur',
            'state' => 'Kuala Lumpur',
            'postcode' => '50000',
            'country' => 'MY',
        ],
    ]);

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
            'reference' => 'ORDER-QUERY',
            'payment' => ['id' => 'payment-query'],
            'transaction_data' => ['payment_method' => 'fpx_b2c'],
        ],
    ];

    // Call webhook
    $this->postJson('/webhooks/chip', $webhookPayload);

    // Get queries
    $queries = DB::getQueryLog();

    // Find the idempotency check query
    $idempotencyQuery = collect($queries)->first(function ($query) {
        return str_contains($query['query'], 'gateway_payment_id')
            && str_contains($query['query'], 'payments');
    });

    // Verify the query exists and uses the correct structure
    expect($idempotencyQuery)->not->toBeNull('Idempotency check query should exist');
    expect($idempotencyQuery['bindings'])->toContain('test-purchase-query', 'Should check for the specific purchase ID');
});
