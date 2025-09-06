<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;

it('can debug cart manager instance behavior during operations', function () {
    // Clear all carts first
    Cart::setInstance('default')->clear();
    Cart::setInstance('guest_123')->clear();
    Cart::setInstance('user_1')->clear();
    
    // Add items to guest cart
    $guestCart = Cart::setInstance('guest_123');
    expect($guestCart)->toBeInstanceOf(\MasyukAI\Cart\CartManager::class);
    
    // Verify instance is set correctly
    expect(Cart::instance())->toBe('guest_123');
    
    $result = Cart::add('product_1', 'Product 1', 10.99, 2);
    expect($result)->toBeObject();
    expect(Cart::count())->toBe(2);
    
    // Switch to user cart and verify it's empty
    $userCart = Cart::setInstance('user_1');
    expect($userCart)->toBeInstanceOf(\MasyukAI\Cart\CartManager::class);
    expect(Cart::count())->toBe(0);
    
    // Now manually do what mergeCartInstances does
    Cart::setInstance('guest_123');
    expect(Cart::instance())->toBe('guest_123');
    expect(Cart::getItems())->toHaveCount(1);
    
    $sourceItems = Cart::getItems();
    
    foreach ($sourceItems as $sourceItem) {
        expect($sourceItem->id)->toBe('product_1');
        expect($sourceItem->name)->toBe('Product 1');
        expect($sourceItem->quantity)->toBe(2);
        
        // Switch to target cart
        Cart::setInstance('user_1');
        expect(Cart::instance())->toBe('user_1');
        expect(Cart::count())->toBe(0);
        
        // Add item to target cart
        $newItem = Cart::add(
            $sourceItem->id,
            $sourceItem->name,
            $sourceItem->price,
            $sourceItem->quantity,
            $sourceItem->attributes->toArray()
        );
        
        expect($newItem)->toBeObject();
        expect(Cart::count())->toBe(2);
        expect(Cart::getItems())->toHaveCount(1);
    }
    
    // Verify final state
    expect(Cart::setInstance('user_1')->count())->toBe(2);
    expect(Cart::setInstance('guest_123')->count())->toBe(2);
});
