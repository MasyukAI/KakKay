<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;

it('can debug cart manager instance switching behavior', function () {
    expect(Cart::instance())->toBe('default');

    // Test setting instance
    $result = Cart::setInstance('test_123');
    expect($result)->toBeInstanceOf(\MasyukAI\Cart\CartManager::class);

    // Check if instance changed globally
    expect(Cart::instance())->toBe('test_123');

    // Test chaining - setInstance should return CartManager allowing method calls
    expect(Cart::setInstance('user_1')->count())->toBe(0);

    // Check that instance is still set
    expect(Cart::instance())->toBe('user_1');

    // Add item and check
    Cart::add('test', 'Test Product', 10.99, 1);
    expect(Cart::count())->toBe(1);

    // Switch instance and verify different cart
    Cart::setInstance('user_2');
    expect(Cart::instance())->toBe('user_2');
    expect(Cart::count())->toBe(0); // Should be empty

    // Switch back
    Cart::setInstance('user_1');
    expect(Cart::instance())->toBe('user_1');
    expect(Cart::count())->toBe(1); // Should have our item back
});
