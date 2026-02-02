<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('checkout route exists and is accessible', function () {
    $response = $this->get('/checkout');

    // Should either show checkout page or redirect to cart
    expect($response->status())->toBeIn([200, 302]);
});

test('checkout redirects to cart when no items', function () {
    $response = $this->get('/checkout');

    $response->assertRedirect('/cart');
});
