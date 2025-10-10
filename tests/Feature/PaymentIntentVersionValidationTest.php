<?php

declare(strict_types=1);

use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use MasyukAI\Cart\Facades\Cart as CartFacade;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Mock the payment gateway
    $this->mock(App\Contracts\PaymentGatewayInterface::class, function ($mock) {
        $mock->shouldReceive('createPurchase')
            ->andReturn([
                'success' => true,
                'purchase_id' => 'test_purchase_123',
                'checkout_url' => 'https://payment.example.com/test',
                'gateway_response' => ['test' => 'response'],
            ]);
    });

    // Register unified cart sync event listener (mimic FilamentCartServiceProvider)
    Illuminate\Support\Facades\Event::listen(
        [
            MasyukAI\Cart\Events\CartCreated::class,
            MasyukAI\Cart\Events\CartCleared::class,
            MasyukAI\Cart\Events\ItemAdded::class,
            MasyukAI\Cart\Events\ItemUpdated::class,
            MasyukAI\Cart\Events\ItemRemoved::class,
            MasyukAI\Cart\Events\CartConditionAdded::class,
            MasyukAI\Cart\Events\CartConditionRemoved::class,
            MasyukAI\Cart\Events\ItemConditionAdded::class,
            MasyukAI\Cart\Events\ItemConditionRemoved::class,
        ],
        MasyukAI\FilamentCart\Listeners\SyncCartOnEvent::class
    );
});

test('payment intent stores correct cart version that matches database version', function () {
    // Clear and set up cart
    CartFacade::clear();
    CartFacade::add('test-1', 'Test Product', 100.00, 1);

    $cart = CartFacade::getCurrentCart();
    $paymentService = app(PaymentService::class);

    // Create payment intent
    $result = $paymentService->createPaymentIntent($cart, [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '123456789',
    ]);

    expect($result['success'])->toBeTrue();

    // Verify payment intent was stored
    $intent = $cart->getMetadata('payment_intent');
    expect($intent)->not->toBeNull();
    expect($intent['purchase_id'])->toBe('test_purchase_123');

    // Get current DB version
    $currentDbVersion = DB::table('carts')
        ->where('identifier', $cart->getIdentifier())
        ->where('instance', $cart->instance())
        ->value('version');

    // Verify stored version matches current DB version
    expect($intent['cart_version'])->toBe($currentDbVersion);
});

test('payment intent validation detects cart modifications after intent creation', function () {
    // Clear and set up cart
    CartFacade::clear();
    CartFacade::add('test-1', 'Test Product', 100.00, 1);

    $cart = CartFacade::getCurrentCart();
    $paymentService = app(PaymentService::class);

    // Create payment intent
    $paymentService->createPaymentIntent($cart, [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '123456789',
    ]);

    // Validate immediately - should be valid
    $validation = $paymentService->validateCartPaymentIntent($cart);
    expect($validation['is_valid'])->toBeTrue();
    expect($validation['cart_changed'])->toBeFalse();
    expect($validation['has_active_intent'])->toBeTrue();

    // Now modify the cart by adding an item
    CartFacade::add('test-2', 'Another Product', 50.00, 1);

    // Get fresh cart reference
    $cart = CartFacade::getCurrentCart();

    // Validate again - should be invalid due to cart change
    $validation = $paymentService->validateCartPaymentIntent($cart);
    expect($validation['is_valid'])->toBeFalse();
    expect($validation['cart_changed'])->toBeTrue();
    expect($validation['has_active_intent'])->toBeTrue();
});

test('payment intent validation detects cart modifications by updating quantity', function () {
    // Clear and set up cart
    CartFacade::clear();
    $item = CartFacade::add('test-1', 'Test Product', 100.00, 1);

    $cart = CartFacade::getCurrentCart();
    $paymentService = app(PaymentService::class);

    // Create payment intent
    $paymentService->createPaymentIntent($cart, [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '123456789',
    ]);

    // Validate immediately - should be valid
    $validation = $paymentService->validateCartPaymentIntent($cart);
    expect($validation['is_valid'])->toBeTrue();
    expect($validation['cart_changed'])->toBeFalse();
    expect($validation['has_active_intent'])->toBeTrue();

    // Modify cart by updating quantity
    CartFacade::update($item->id, ['quantity' => 5]);

    // Get fresh cart reference
    $cart = CartFacade::getCurrentCart();

    // Validate again - should be invalid
    $validation = $paymentService->validateCartPaymentIntent($cart);
    expect($validation['is_valid'])->toBeFalse();
    expect($validation['cart_changed'])->toBeTrue();
    expect($validation['has_active_intent'])->toBeTrue();
});

test('payment intent validation detects cart modifications by removing item', function () {
    // Clear and set up cart
    CartFacade::clear();
    $item = CartFacade::add('test-1', 'Test Product', 100.00, 1);
    CartFacade::add('test-2', 'Another Product', 50.00, 1);

    $cart = CartFacade::getCurrentCart();
    $paymentService = app(PaymentService::class);

    // Create payment intent
    $paymentService->createPaymentIntent($cart, [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '123456789',
    ]);

    // Validate immediately - should be valid
    $validation = $paymentService->validateCartPaymentIntent($cart);
    expect($validation['is_valid'])->toBeTrue();

    // Remove an item
    CartFacade::remove($item->id);

    // Get fresh cart reference
    $cart = CartFacade::getCurrentCart();

    // Validate again - should be invalid
    $validation = $paymentService->validateCartPaymentIntent($cart);
    expect($validation['is_valid'])->toBeFalse();
    expect($validation['cart_changed'])->toBeTrue();
    expect($validation['has_active_intent'])->toBeTrue();
});

test('payment intent validation passes when cart is not modified', function () {
    // Clear and set up cart
    CartFacade::clear();
    CartFacade::add('test-1', 'Test Product', 100.00, 2);

    $cart = CartFacade::getCurrentCart();
    $paymentService = app(PaymentService::class);

    // Create payment intent
    $paymentService->createPaymentIntent($cart, [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '123456789',
    ]);

    // Validate multiple times without modifying cart
    for ($i = 0; $i < 3; $i++) {
        $cart = CartFacade::getCurrentCart();
        $validation = $paymentService->validateCartPaymentIntent($cart);

        expect($validation['is_valid'])->toBeTrue();
        expect($validation['cart_changed'])->toBeFalse();
    }
});

test('payment intent validation detects amount changes', function () {
    // Clear and set up cart
    CartFacade::clear();
    $item = CartFacade::add('test-1', 'Test Product', 100.00, 1);

    $cart = CartFacade::getCurrentCart();
    $paymentService = app(PaymentService::class);

    // Create payment intent with original amount
    $paymentService->createPaymentIntent($cart, [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '123456789',
    ]);

    // Change quantity to change the total
    CartFacade::update($item->id, ['quantity' => 3]);

    // Get fresh cart reference
    $cart = CartFacade::getCurrentCart();

    // Validate - should be invalid due to cart change (which includes amount change)
    $validation = $paymentService->validateCartPaymentIntent($cart);
    expect($validation['is_valid'])->toBeFalse();
    expect($validation['cart_changed'])->toBeTrue();
    // Note: amount_changed is implicit in cart_changed via version tracking
});
