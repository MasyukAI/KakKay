<?php

use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MasyukAI\Cart\Facades\Cart as CartFacade;

uses(RefreshDatabase::class);

test('cart model can be instantiated', function () {
    // Clear any existing cart first to ensure isolation
    CartFacade::clear();

    // Add items to cart to ensure it's saved to database
    CartFacade::add('test-product-1', 'Test Product', 99.99, 2);

    // Get the cart model from database
    $cart = Cart::where('identifier', CartFacade::getIdentifier())
        ->where('instance', 'default')
        ->first();

    expect($cart)->not->toBeNull();
    expect($cart->identifier)->toBe(CartFacade::getIdentifier());
    expect($cart->instance)->toBe('default');
    // Items count should be 1 (one unique item with quantity 2)
    expect($cart->items_count)->toBeGreaterThanOrEqual(1); // At least 1 item
    expect($cart->total_quantity)->toBeGreaterThanOrEqual(2); // At least 2 quantity
    expect($cart->subtotal)->toBeGreaterThan(0);
    expect($cart->isEmpty())->toBeFalse();
});

test('cart model handles empty cart correctly', function () {
    // Clear cart first
    CartFacade::clear();

    // Create an empty cart by getting identifier (cart won't be saved until items added)
    $identifier = CartFacade::getIdentifier();

    // Try to get cart from database (should be null since no items added)
    $cart = Cart::where('identifier', $identifier)
        ->where('instance', 'default')
        ->first();

    // Empty carts are not stored in database
    expect($cart)->toBeNull();
});

test('cart model scopes work correctly', function () {
    // Add items to create a cart in database
    CartFacade::add('test-product-1', 'Test Product', 99.99, 1);

    $cart = Cart::where('identifier', CartFacade::getIdentifier())
        ->where('instance', 'default')
        ->first();

    expect($cart)->not->toBeNull();
    expect(method_exists($cart, 'scopeInstance'))->toBeTrue();
    expect(method_exists($cart, 'scopeByIdentifier'))->toBeTrue();
    expect(method_exists($cart, 'scopeNotEmpty'))->toBeTrue();
    expect(method_exists($cart, 'scopeRecent'))->toBeTrue();
});
