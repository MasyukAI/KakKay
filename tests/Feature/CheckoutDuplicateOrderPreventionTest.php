<?php

declare(strict_types=1);

use App\Livewire\Checkout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use MasyukAI\Cart\Facades\Cart;

uses(RefreshDatabase::class);

test('checkout prevents duplicate orders when purchase already exists', function () {
    // Set up cart with items
    Cart::add('1', 'Test Product', 2999, 1);

    // Mock the payment gateway to return a purchase (indicating existing purchase)
    $this->mock(\App\Contracts\PaymentGatewayInterface::class, function ($mock) {
        $mock->shouldReceive('getPurchaseStatus')
            ->andReturn([
                'id' => 'existing-purchase-id',
                'status' => 'pending',
                'checkout_url' => 'https://payment.example.com/existing',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    });

    // Create the component and fill form data
    $component = Livewire::test(Checkout::class);

    $formData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '123456789',
        'country' => 'Malaysia',
        'city' => 'Kuala Lumpur',
        'address' => '123 Test Street',
        'state' => 'Kuala Lumpur',
        'postal_code' => '50000',
        'delivery_method' => 'standard',
    ];

    // Set the form data and call processCheckout
    $component->set('data', $formData)
        ->call('processCheckout')
        ->assertHasErrors(); // Should have validation errors about duplicate order
});

test('checkout proceeds normally when no existing purchase', function () {
    // Set up cart with items
    Cart::add('1', 'Test Product', 2999, 1);

    // Mock the payment gateway to return null (no existing purchase) and mock purchase creation
    $this->mock(\App\Contracts\PaymentGatewayInterface::class, function ($mock) {
        $mock->shouldReceive('getPurchaseStatus')
            ->andReturn(null);
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

    $formData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '123456789',
        'country' => 'Malaysia',
        'city' => 'Kuala Lumpur',
        'address' => '123 Test Street',
        'state' => 'Kuala Lumpur',
        'postal_code' => '50000',
        'delivery_method' => 'standard',
    ];

    // Set the form data and call processCheckout
    $component->set('data', $formData)
        ->call('processCheckout')
        ->assertHasNoErrors()
        ->assertRedirect('https://payment.example.com/pay/test_purchase_123');
});
