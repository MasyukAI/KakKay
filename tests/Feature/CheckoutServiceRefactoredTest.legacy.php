<?php

use App\Contracts\PaymentGatewayInterface;
use App\Models\Payment;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\CodeGeneratorService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MasyukAI\Cart\Facades\Cart;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cart::clear();
});

test('checkout service uses payment service for payment processing', function () {
    $mockPaymentGateway = mock(PaymentGatewayInterface::class);
    $paymentService = new PaymentService(
        $mockPaymentGateway,
        app(CodeGeneratorService::class),
        null,
        fn () => 'PMT24-TEST123'
    );
    $checkoutService = app(CheckoutService::class);

    // Use reflection to inject our mocked payment service
    $reflection = new ReflectionClass($checkoutService);
    $paymentServiceProperty = $reflection->getProperty('paymentService');
    $paymentServiceProperty->setAccessible(true);
    $paymentServiceProperty->setValue($checkoutService, $paymentService);

    // Setup test data
    $user = User::factory()->create();

    // Mock payment gateway to return success response
    $mockPaymentGateway->shouldReceive('createPurchase')
        ->once()
        ->andReturn([
            'success' => true,
            'purchase_id' => 'purchase-123',
            'checkout_url' => 'https://gateway.com/pay/123',
            'gateway_response' => ['status' => 'created'],
        ]);

    // Setup customer and cart data (match CheckoutService expectations)
    $customerData = [
        'name' => $user->name,
        'email' => $user->email,
        'phone' => '+60123456789',
        'address' => '123 Test Street',
        'city' => 'Kuala Lumpur',
        'state' => '10',
        'zip_code' => '50000',
        'country' => 'MY',
        'delivery_method' => 'standard',
    ];

    $cartItems = [
        [
            'id' => 1,
            'name' => 'Test Product',
            'price' => 2000,
            'quantity' => 1,
        ],
    ];

    $result = $checkoutService->processCheckout($customerData, $cartItems);

    expect($result)->toBeArray();
    expect($result['success'])->toBeTrue();
    expect($result['purchase_id'])->toBe('purchase-123');
});

test('checkout service delegates purchase status checks to payment service', function () {
    $mockPaymentGateway = mock(PaymentGatewayInterface::class);
    $paymentService = new PaymentService($mockPaymentGateway, app(CodeGeneratorService::class));
    $checkoutService = app(CheckoutService::class);

    // Use reflection to inject our mocked payment service
    $reflection = new ReflectionClass($checkoutService);
    $paymentServiceProperty = $reflection->getProperty('paymentService');
    $paymentServiceProperty->setAccessible(true);
    $paymentServiceProperty->setValue($checkoutService, $paymentService);

    $purchaseId = 'purchase-123';
    $statusResponse = [
        'status' => 'completed',
        'transaction_id' => 'txn-456',
    ];

    $mockPaymentGateway->shouldReceive('getPurchaseStatus')
        ->with($purchaseId)
        ->once()
        ->andReturn($statusResponse);

    $result = $checkoutService->getPurchaseStatus($purchaseId);

    expect($result)->toBe($statusResponse);
});

test('payment service delegates available payment methods to payment gateway', function () {
    $mockPaymentGateway = mock(PaymentGatewayInterface::class);
    $paymentService = new PaymentService($mockPaymentGateway, app(CodeGeneratorService::class));

    $paymentMethods = [
        'credit_card' => 'Credit Card',
        'chip' => 'CHIP',
    ];

    $mockPaymentGateway->shouldReceive('getAvailablePaymentMethods')
        ->once()
        ->andReturn($paymentMethods);

    $result = $paymentService->getAvailablePaymentMethods();

    expect($result)->toBe($paymentMethods);
});
