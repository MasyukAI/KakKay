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
    expect(Cart::count())->toBe(2);
    
    // Test assignment vs non-assignment
    echo "Test 1 - Direct call:\n";
    Cart::setInstance('user_1');
    echo "Instance after direct call: " . Cart::instance() . "\n";
    
    echo "Test 2 - Assignment:\n";
    $result = Cart::setInstance('guest_123');
    echo "Instance after assignment: " . Cart::instance() . "\n";
    echo "Result type: " . get_class($result) . "\n";
    
    echo "Test 3 - Chaining:\n";
    Cart::setInstance('user_1')->count();
    echo "Instance after chaining: " . Cart::instance() . "\n";
    
    echo "Test 4 - Testing the merge logic approach:\n";
    
    // Correct approach - don't assign the result
    Cart::setInstance('guest_123');
    echo "Guest instance: " . Cart::instance() . "\n";
    $sourceItems = Cart::getItems();
    echo "Source items count: " . $sourceItems->count() . "\n";
    
    Cart::setInstance('user_1');
    echo "User instance: " . Cart::instance() . "\n";
    echo "User cart count before: " . Cart::count() . "\n";
    
    foreach ($sourceItems as $sourceItem) {
        $newItem = Cart::add(
            $sourceItem->id,
            $sourceItem->name,
            $sourceItem->price,
            $sourceItem->quantity,
            $sourceItem->attributes->toArray()
        );
        echo "Added item, user cart count now: " . Cart::count() . "\n";
    }
    
    expect(Cart::count())->toBeGreaterThan(0);
});
