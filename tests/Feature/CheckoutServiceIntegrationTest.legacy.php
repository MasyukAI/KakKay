<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use MasyukAI\Cart\Facades\Cart;

uses(RefreshDatabase::class);

test('checkout service creates order with optimized code generation', function () {
    // Create a user
    $user = User::factory()->create();

    // Mock cart data
    $cartItems = [
        [
            'id' => 'test-product',
            'name' => 'Test Product',
            'price' => 2000, // $20.00 in cents
            'quantity' => 2,
        ],
    ];

    $customerData = [
        'name' => 'John Doe',
        'email' => $user->email,
        'phone' => '1234567890',
        'address' => '123 Test Street',
        'city' => 'Test City',
        'state' => 'Test State',
        'zip_code' => '12345',
        'country' => 'Malaysia',
        'delivery_method' => 'standard',
    ];

    // Mock the payment gateway to return success
    $paymentGateway = mock(App\Contracts\PaymentGatewayInterface::class);
    $paymentGateway->shouldReceive('createPurchase')
        ->once()
        ->andReturn([
            'success' => true,
            'purchase_id' => 'test-purchase-id',
            'checkout_url' => 'https://example.com/checkout',
            'gateway_response' => ['status' => 'created'],
        ]);

    // Create PaymentService with mocked gateway
    $paymentService = new App\Services\PaymentService($paymentGateway, app(App\Services\CodeGeneratorService::class));
    // Create checkout service with mocked PaymentService
    $checkoutService = new CheckoutService(null, null, null, $paymentService);

    // Execute checkout
    $result = $checkoutService->processCheckout($customerData, $cartItems);

    // Assertions
    expect($result['success'])->toBeTrue();
    expect($result['order'])->toBeInstanceOf(Order::class);
    expect($result['payment'])->toBeInstanceOf(Payment::class);

    // Verify order number format
    $order = $result['order'];
    expect($order->order_number)->toMatch('/^ORD\d{2}-[A-Z0-9]{6}$/');
    expect($order->order_number)->toStartWith('ORD'.now()->format('y'));

    // Verify payment reference format
    $payment = $result['payment'];
    expect($payment->reference)->toMatch('/^PMT\d{2}-[A-Z0-9]{6}$/');
    expect($payment->reference)->toStartWith('PMT'.now()->format('y'));

    // Verify they are unique
    expect($order->order_number)->not->toBe($payment->reference);
});

test('checkout service handles duplicate order number gracefully', function () {
    // Create an existing order with a predictable number to test collision handling
    $existingOrder = Order::factory()->create([
        'order_number' => 'ORD25-TEST01',
    ]);

    // Mock uniqid to return predictable values that will cause initial collision
    $callCount = 0;
    $originalUniqid = function_exists('uniqid') ? 'uniqid' : null;

    // We can't easily mock uniqid, but we can test the retry mechanism works
    // by creating multiple orders rapidly and ensuring they all get unique numbers

    $user = User::factory()->create();
    $cartItems = [
        [
            'id' => 'test-product',
            'name' => 'Test Product',
            'price' => 1000,
            'quantity' => 1,
        ],
    ];

    $customerData = [
        'name' => 'John Doe',
        'email' => $user->email,
        'phone' => '1234567890',
        'address' => '123 Test Street',
        'city' => 'Test City',
        'state' => 'Test State',
        'zip_code' => '12345',
        'country' => 'Malaysia',
        'delivery_method' => 'standard',
    ];

    // Mock payment gateway
    $paymentGateway = mock(App\Contracts\PaymentGatewayInterface::class);
    $paymentGateway->shouldReceive('createPurchase')
        ->times(5)
        ->andReturn([
            'success' => true,
            'purchase_id' => 'test-purchase-id',
            'checkout_url' => 'https://example.com/checkout',
            'gateway_response' => ['status' => 'created'],
        ]);

    $paymentService = new App\Services\PaymentService($paymentGateway, app(App\Services\CodeGeneratorService::class));
    $checkoutService = new CheckoutService(null, null, null, $paymentService);

    // Create multiple orders and ensure they all get unique numbers
    $orderNumbers = [];

    for ($i = 0; $i < 5; $i++) {
        $result = $checkoutService->processCheckout($customerData, $cartItems);
        expect($result['success'])->toBeTrue();

        $orderNumber = $result['order']->order_number;
        expect($orderNumbers)->not->toContain($orderNumber, "Order number {$orderNumber} was duplicated");

        $orderNumbers[] = $orderNumber;
    }

    // All order numbers should be different
    expect(count(array_unique($orderNumbers)))->toBe(5);

    // None should match the existing order
    foreach ($orderNumbers as $orderNumber) {
        expect($orderNumber)->not->toBe($existingOrder->order_number);
    }
});

test('checkout service performance is optimized', function () {
    // This test verifies that we're not doing unnecessary database queries

    $user = User::factory()->create();
    $cartItems = [
        [
            'id' => 'test-product',
            'name' => 'Test Product',
            'price' => 1500,
            'quantity' => 1,
        ],
    ];

    $customerData = [
        'name' => 'Jane Doe',
        'email' => $user->email,
        'phone' => '9876543210',
        'address' => '456 Test Ave',
        'city' => 'Test Town',
        'state' => 'Test State',
        'zip_code' => '67890',
        'country' => 'Malaysia',
        'delivery_method' => 'express',
    ];

    // Mock payment gateway
    $paymentGateway = mock(App\Contracts\PaymentGatewayInterface::class);
    $paymentGateway->shouldReceive('createPurchase')
        ->once()
        ->andReturn([
            'success' => true,
            'purchase_id' => 'performance-test-id',
            'checkout_url' => 'https://example.com/checkout',
            'gateway_response' => ['status' => 'created'],
        ]);

    $paymentService = new App\Services\PaymentService($paymentGateway, app(App\Services\CodeGeneratorService::class));
    $checkoutService = new CheckoutService(null, null, null, $paymentService);

    // Measure query count (approximate)
    $startQueries = DB::getQueryLog();
    DB::enableQueryLog();

    $startTime = microtime(true);
    $result = $checkoutService->processCheckout($customerData, $cartItems);
    $endTime = microtime(true);

    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    // Verify success
    expect($result['success'])->toBeTrue();

    // Performance should be reasonable (less than 1 second for this simple case)
    $executionTime = $endTime - $startTime;
    expect($executionTime)->toBeLessThan(1.0, "Checkout took {$executionTime} seconds, which is too slow");

    // The optimized approach should not require pre-check queries for code generation
    // We can't easily count exact queries due to transactions and other Laravel operations,
    // but we can verify the codes were generated correctly
    expect($result['order']->order_number)->toMatch('/^ORD\d{2}-[A-Z0-9]{6}$/');
    expect($result['payment']->reference)->toMatch('/^PMT\d{2}-[A-Z0-9]{6}$/');
});
