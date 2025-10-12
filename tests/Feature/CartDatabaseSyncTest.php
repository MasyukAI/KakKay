<?php

declare(strict_types=1);

use AIArmada\Cart\Events\CartCreated;
use AIArmada\Cart\Facades\Cart;
use AIArmada\FilamentCart\Models\Cart as CartModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

test('empty cart does not create database record on homepage visit', function () {
    // Visit homepage (instantiates cart but doesn't add items)
    $this->get('/');

    // Cart should not exist in database because no items were added
    expect(CartModel::query()->count())->toBe(0);
});

test('cart is created in database when item is added', function () {
    // Add item to cart (this should trigger CartCreated and sync to database)
    Cart::add('test-product', 'Test Product', 99.99, 1);

    // Cart should now exist in database
    expect(CartModel::query()->count())->toBe(1);

    $cart = CartModel::query()->first();
    expect($cart)->not->toBeNull();
    expect($cart->items)->toHaveCount(1);
});

test('homepage visit does not update cart updated_at timestamp', function () {
    // Add item to cart to create it
    Cart::add('test-product', 'Test Product', 99.99, 1);

    // Get the initial updated_at timestamp
    $cart = CartModel::query()->first();
    $initialUpdatedAt = $cart->updated_at;

    // Wait a moment to ensure timestamp would change if updated
    sleep(2);

    // Visit homepage (just instantiates cart, doesn't modify it)
    $this->get('/');

    // Verify updated_at has not changed
    $cart->refresh();
    expect($cart->updated_at->equalTo($initialUpdatedAt))->toBeTrue();
});

test('cart created event only fires once when first item is added', function () {
    Event::fake();

    // Add first item - CartCreated should fire
    Cart::add('product-1', 'Product 1', 10.00, 1);

    Event::assertDispatched(CartCreated::class, 1);

    // Add second item - CartCreated should NOT fire again
    Cart::add('product-2', 'Product 2', 20.00, 1);

    // Still only 1 CartCreated event
    Event::assertDispatched(CartCreated::class, 1);
});
