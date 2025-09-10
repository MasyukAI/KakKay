<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;

it('can test facade instance switching behavior', function () {
    // Clear all carts
    Cart::setInstance('default')->clear();
    Cart::setInstance('guest_123')->clear();
    Cart::setInstance('user_1')->clear();

    // Add items to guest cart
    Cart::setInstance('guest_123');
    expect(Cart::instance())->toBe('guest_123');
    Cart::add('product_1', 'Product 1', 10.99, 2);
    Cart::add('product_2', 'Product 2', 15.99, 1);
    expect(Cart::count())->toBe(3); // 2 + 1 = 3 total items

    // Test assignment vs non-assignment
    // Test 1 - Direct call:
    Cart::setInstance('user_1');
    expect(Cart::instance())->toBe('user_1');

    // Test 2 - Assignment:
    $result = Cart::setInstance('guest_123');
    expect(Cart::instance())->toBe('guest_123');
    expect(get_class($result))->toContain('CartManager');

    // Test 3 - Chaining:
    Cart::setInstance('user_1')->count();
    expect(Cart::instance())->toBe('user_1');

    // Test 4 - Testing the merge logic approach:

    // Correct approach - don't assign the result
    Cart::setInstance('guest_123');
    expect(Cart::instance())->toBe('guest_123');
    $sourceItems = Cart::getItems();
    expect($sourceItems->count())->toBe(2);

    Cart::setInstance('user_1');
    expect(Cart::instance())->toBe('user_1');
    $initialUserCount = Cart::count();

    foreach ($sourceItems as $sourceItem) {
        $newItem = Cart::add(
            $sourceItem->id,
            $sourceItem->name,
            $sourceItem->price,
            $sourceItem->quantity,
            $sourceItem->attributes->toArray()
        );
        expect(Cart::count())->toBeGreaterThan($initialUserCount);
        $initialUserCount = Cart::count();
    }

    expect(Cart::count())->toBeGreaterThan(0);
});
