<?php

declare(strict_types=1);

use App\Models\Product;
use MasyukAI\Cart\Facades\Cart;

beforeEach(function () {
    Cart::clear();
});

it('completes full purchase flow with webhook', function () {
    // Add product to cart
    $product = Product::first();
    expect($product)->not->toBeNull();

    Cart::add(
        id: (string) $product->id,
        name: $product->name,
        price: $product->price,
        quantity: 1
    );

    // Visit checkout page
    $page = visit('https://local.kakkay.my/checkout');

    // Fill checkout form
    $page->fill('data.name', 'Test Customer')
        ->fill('data.email', 'test@example.com')
        ->fill('data.email_confirmation', 'test@example.com')
        ->fill('data.phone', '60123456789')
        ->select('data.country', 'Malaysia')
        ->fill('data.state', 'Selangor')
        ->fill('data.city', 'Kuala Lumpur')
        ->fill('data.postcode', '50000')
        ->fill('data.street1', '123 Test Street')
        ->fill('data.street2', 'Test Area');

    // Submit checkout
    $page->click('Bayar Sekarang');

    // Wait for redirect to CHIP
    $page->wait(3);

    // Should be on CHIP payment page
    expect($page->url())->toContain('gate.chip-in.asia');

    // Click "Test Success" button on CHIP's test page
    $page->click('Test Success');

    // Wait for webhook to process
    sleep(5);

    // Verify order was created
    $this->assertDatabaseHas('orders', [
        'status' => 'paid',
    ]);

    expect(true)->toBeTrue();
});
