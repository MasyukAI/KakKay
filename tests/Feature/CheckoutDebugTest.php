<?php

declare(strict_types=1);

use App\Livewire\Checkout;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use MasyukAI\Cart\Facades\Cart;

uses(RefreshDatabase::class);

test('debug checkout component creation', function () {
    // Create test products first
    $product1 = \App\Models\Product::factory()->create([
        'name' => 'Test Product 1',
        'price' => 2999, // RM29.99 in cents
        'is_active' => true,
    ]);

    $product2 = \App\Models\Product::factory()->create([
        'name' => 'Test Product 2',
        'price' => 1500, // RM15.00 in cents
        'is_active' => true,
    ]);

    // Set up cart with actual product IDs
    Cart::add($product1->id, $product1->name, $product1->price, 2); // RM29.99 x 2 = RM59.98
    Cart::add($product2->id, $product2->name, $product2->price, 1); // RM15.00 x 1 = RM15.00

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

    // Create the component to see if it loads correctly
    $component = Livewire::test(Checkout::class);

    // Check if cart items are loaded
    expect($component->get('cartItems'))->toBeArray();
    expect(count($component->get('cartItems')))->toBeGreaterThan(0);

    // Fill form with complete required data
    $formData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'email_confirmation' => 'john@example.com', // Required field
        'phone' => '+60123456789',
        'country' => 'Malaysia', // Include country explicitly
        'state' => '10', // Selangor
        'district' => '1001', // Klang (valid district for Selangor)
        'address' => '123 Test Street',
        'postal_code' => '50000',
    ];

    $component->set('data', $formData);

    // Check what happens when we call processCheckout
    $orderCountBefore = Order::count();

    // Let's also check if the form validates first
    $formState = $component->form->getState();
    dump('Form state before processCheckout:', $formState);

    // Also check the raw data property
    dump('Raw data property:', $component->get('data'));

    // Attempt to process checkout
    try {
        $component->call('processCheckout');
        dump('processCheckout called successfully');

        // Check if there are any error messages
        if (session()->has('error')) {
            dump('Session error:', session('error'));
        }

        // Check component errors
        $errors = $component->get('errors');
        if (! empty($errors)) {
            dump('Component errors:', $errors);
        }

    } catch (\Exception $e) {
        dump('Exception during processCheckout:', $e->getMessage());
        dump('Exception trace:', $e->getTraceAsString());
    }

    $orderCountAfter = Order::count();

    dump('Orders before: '.$orderCountBefore);
    dump('Orders after: '.$orderCountAfter);

    // If we reach here, check if an order was created
    expect($orderCountAfter)->toBeGreaterThan($orderCountBefore);
});
