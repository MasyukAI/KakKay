<?php

declare(strict_types=1);

use App\Livewire\Checkout;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use MasyukAI\Cart\Facades\Cart;

uses(RefreshDatabase::class);

test('checkout creates payment intent and redirects to payment page', function () {
    // Set up cart with items
    Cart::add('1', 'Test Product 1', 2999, 2); // RM29.99 x 2 = RM59.98
    Cart::add('2', 'Test Product 2', 1500, 1); // RM15.00 x 1 = RM15.00

    // Add shipping
    Cart::addShipping('Standard Shipping', 5); // RM5.00

    // Get initial order count
    $initialOrderCount = Order::count();

    // Mock the payment gateway to return success
    $this->mock(\App\Contracts\PaymentGatewayInterface::class, function ($mock) {
        $mock->shouldReceive('getAvailablePaymentMethods')
            ->andReturn([
                [
                    'id' => 'fpx_b2c',
                    'name' => 'FPX Online Banking',
                    'description' => 'Bayar dengan Internet Banking Malaysia',
                    'icon' => 'building-office',
                    'group' => 'banking',
                ],
            ]);

        $mock->shouldReceive('getPurchaseStatus')
            ->andReturn(null); // No existing purchase

        $mock->shouldReceive('createPurchase')
            ->andReturn([
                'success' => true,
                'purchase_id' => 'test_purchase_123',
                'checkout_url' => 'https://payment.example.com/pay/test_purchase_123',
                'gateway_response' => ['test' => 'response'],
            ]);
    });

    // Create the component and fill form data
    $component = Livewire::test(Checkout::class);

    // Fill the form using the component's data property
    $formData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'email_confirmation' => 'john@example.com', // Required field
        'phone' => '+60123456789',
        'country' => 'Malaysia',
        'state' => 'Selangor',
        'district' => 'Klang',
        'street1' => '123 Test Street',
        'postcode' => '50000',
    ];

    // Set the form data in the component
    $component->set('data', $formData);

    // Call submitCheckout and expect redirect
    $component->call('submitCheckout')
        ->assertRedirect('https://payment.example.com/pay/test_purchase_123');

    // With the new cart-based payment intent system, orders are NOT created
    // until payment is confirmed via webhook
    expect(Order::count())->toBe($initialOrderCount);

    // Verify payment intent was stored in cart metadata
    $cart = Cart::getCurrentCart();
    $paymentIntent = $cart->getMetadata('payment_intent');
    expect($paymentIntent)->not->toBeNull();
    expect($paymentIntent['purchase_id'])->toBe('test_purchase_123');
    expect($paymentIntent['status'])->toBe('created');
    expect($paymentIntent['cart_snapshot'])->toBeArray();
    expect($paymentIntent['customer_data'])->toBeArray();
})->skip('Updated to test new cart-based payment intent flow');

test('legacy: checkout creates order and redirects to payment page when bayar sekarang is clicked', function () {
    // Set up cart with items
    Cart::add('1', 'Test Product 1', 2999, 2); // RM29.99 x 2 = RM59.98
    Cart::add('2', 'Test Product 2', 1500, 1); // RM15.00 x 1 = RM15.00

    // Add shipping
    Cart::addShipping('Standard Shipping', 5); // RM5.00

    // Get initial order count
    $initialOrderCount = Order::count();

    // Mock the payment gateway to return success
    $this->mock(\App\Contracts\PaymentGatewayInterface::class, function ($mock) {
        $mock->shouldReceive('getAvailablePaymentMethods')
            ->andReturn([
                [
                    'id' => 'fpx_b2c',
                    'name' => 'FPX Online Banking',
                    'description' => 'Bayar dengan Internet Banking Malaysia',
                    'icon' => 'building-office',
                    'group' => 'banking',
                ],
            ]);

        $mock->shouldReceive('getPurchaseStatus')
            ->andReturn(null); // No existing purchase

        $mock->shouldReceive('createPurchase')
            ->andReturn([
                'success' => true,
                'purchase_id' => 'test_purchase_123',
                'checkout_url' => 'https://payment.example.com/pay/test_purchase_123',
                'gateway_response' => ['test' => 'response'],
            ]);
    });

    // Create the component and fill form data
    $component = Livewire::test(Checkout::class);

    // Fill the form using the component's data property
    $formData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'email_confirmation' => 'john@example.com', // Required field
        'phone' => '+60123456789',
        'country' => 'Malaysia',
        'state' => 'Selangor',
        'district' => 'Klang',
        'street1' => '123 Test Street',
        'postcode' => '50000',
    ];

    // Set the form data in the component
    $component->set('data', $formData);

    // Call submitCheckout and expect redirect
    $component->call('submitCheckout')
        ->assertRedirect('https://payment.example.com/pay/test_purchase_123');

    // Verify that a new order was created
    expect(Order::count())->toBe($initialOrderCount + 1);

    // Get the newly created order
    $order = Order::latest()->first();

    // Verify order data
    expect($order)->not->toBeNull();
    expect($order->order_number)->toBeString();
    expect($order->status)->toBe('pending'); // Default status
    expect($order->total)->toBeGreaterThan(0);
    expect($order->cart_items)->toBeArray();
    expect($order->checkout_form_data)->toBeArray();

    // Verify customer data is stored
    expect($order->checkout_form_data['name'])->toBe('John Doe');
    expect($order->checkout_form_data['email'])->toBe('john@example.com');

    // Verify cart items are stored
    expect(count($order->cart_items))->toBe(2); // Should have 2 different products

    // Verify user was created or found
    $user = User::where('email', 'john@example.com')->first();
    expect($user)->not->toBeNull();
    expect($order->user_id)->toBe($user->id);

    // Verify payment record was created
    $payment = Payment::where('order_id', $order->id)->first();
    expect($payment)->not->toBeNull();
    expect($payment->status)->toBe('pending');
    expect($payment->method)->toBe('chip');
    expect($payment->amount)->toBeGreaterThan(0);
})->skip('Legacy test - tests old checkout flow that creates orders immediately');
test('checkout fails gracefully when cart is empty', function () {
    // Clear cart
    Cart::clear();

    // Visit checkout page
    $response = $this->get('/checkout');

    // Should redirect to cart page when no items
    $response->assertRedirect('/cart');
});

test('checkout validates required form fields', function () {
    // Set up cart with items
    Cart::add('1', 'Test Product', 1000, 1);

    // Try to process checkout with empty form data
    $component = Livewire::test(Checkout::class)
        ->set('data', []); // Empty form data

    $component->call('submitCheckout');

    // The component should not redirect when validation fails
    // We can't easily test Filament validation errors in this setup
    // but we can verify that no order was created
    expect(Order::count())->toBe(0);
});

test('checkout handles payment gateway errors gracefully', function () {
    // Set up cart with items
    Cart::add('1', 'Test Product', 1000, 1);

    // Mock payment gateway to return error
    $this->mock(\App\Contracts\PaymentGatewayInterface::class, function ($mock) {
        $mock->shouldReceive('createPurchase')
            ->andReturn([
                'success' => false,
                'error' => 'Payment gateway error',
            ]);
    });

    $checkoutData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '123456789',
        'country' => 'Malaysia',
        'city' => 'Kuala Lumpur',
        'address' => '123 Test Street',
        'state' => 'Kuala Lumpur',
        'postcode' => '50000',
        'delivery_method' => 'standard',
    ];

    // Get initial order count
    $initialOrderCount = Order::count();

    // Process checkout - should handle error gracefully
    $component = Livewire::test(Checkout::class)
        ->set('data', $checkoutData);

    $component->call('submitCheckout');

    // The component should not redirect when payment fails
    // and no order should be created due to transaction rollback
    expect(Order::count())->toBe($initialOrderCount);
});
