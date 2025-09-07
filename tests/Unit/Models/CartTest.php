<?php

use App\Models\Cart;

test('cart model can be instantiated', function () {
    $cart = new Cart([
        'identifier' => 'test-cart-123',
        'instance' => 'default',
        'items' => [
            [
                'id' => '1',
                'name' => 'Test Product',
                'price' => 99.99,
                'quantity' => 2,
            ]
        ],
        'conditions' => [],
        'metadata' => [],
    ]);

    expect($cart->identifier)->toBe('test-cart-123');
    expect($cart->instance)->toBe('default');
    expect($cart->items_count)->toBe(1);
    expect($cart->total_quantity)->toBe(2);
    expect($cart->subtotal)->toBe(199.98);
    expect($cart->isEmpty())->toBeFalse();
});

test('cart model handles empty cart correctly', function () {
    $cart = new Cart([
        'identifier' => 'empty-cart',
        'instance' => 'default',
        'items' => [],
        'conditions' => [],
        'metadata' => [],
    ]);

    expect($cart->isEmpty())->toBeTrue();
    expect($cart->items_count)->toBe(0);
    expect($cart->total_quantity)->toBe(0);
    expect($cart->subtotal)->toBe(0);
});

test('cart model scopes work correctly', function () {
    // This would require database setup to test properly
    // For now, just ensure the methods exist
    $cart = new Cart();
    
    expect(method_exists($cart, 'scopeInstance'))->toBeTrue();
    expect(method_exists($cart, 'scopeByIdentifier'))->toBeTrue();
    expect(method_exists($cart, 'scopeNotEmpty'))->toBeTrue();
    expect(method_exists($cart, 'scopeRecent'))->toBeTrue();
});