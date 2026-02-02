<?php

declare(strict_types=1);

use AIArmada\Checkout\Models\CheckoutSession;
use AIArmada\Checkout\States\Completed;
use AIArmada\Orders\States\Created;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Telescope\Telescope;

uses(RefreshDatabase::class);

test('checkout success page loads using checkout session data', function (): void {
    Telescope::stopRecording();
    config(['telescope.enabled' => false]);

    $order = Order::create([
        'order_number' => Order::generateOrderNumber(),
        'status' => Created::class,
        'grand_total' => 1200,
        'currency' => 'MYR',
    ]);

    $session = CheckoutSession::create([
        'cart_id' => 'cart_test_123',
        'order_id' => $order->id,
        'status' => Completed::class,
        'cart_snapshot' => [
            'items' => [
                [
                    'id' => 'product-1',
                    'name' => 'Test Product',
                    'quantity' => 1,
                    'price' => 1200,
                ],
            ],
            'totals' => [
                'subtotal' => 1200,
                'total' => 1200,
            ],
        ],
        'step_states' => [],
        'billing_data' => [
            'name' => 'Test Buyer',
            'email' => 'buyer@example.com',
        ],
        'shipping_data' => [
            'name' => 'Test Buyer',
            'street1' => '123 Test Street',
            'city' => 'Kajang',
            'state' => 'Selangor',
            'country' => 'MY',
            'postcode' => '43000',
        ],
        'subtotal' => 1200,
        'grand_total' => 1200,
        'currency' => 'MYR',
    ]);

    $this->get(route('checkout.success', ['session' => $session->id]))
        ->assertOk()
        ->assertViewHas('order', fn ($viewOrder) => $viewOrder->is($order))
        ->assertViewHas('reference', $session->cart_id)
        ->assertViewHas('formattedTotal');
});
