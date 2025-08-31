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
    
    echo "Current instance after setInstance: " . Cart::instance() . "\n";
    
    $result = Cart::add('product_1', 'Product 1', 10.99, 2);
    echo "Add result: " . get_class($result) . "\n";
    echo "Count after add: " . Cart::count() . "\n";
    
    // Switch to user cart and verify it's empty
    $userCart = Cart::setInstance('user_1');
    expect($userCart)->toBeInstanceOf(\MasyukAI\Cart\CartManager::class);
    echo "User cart count: " . Cart::count() . "\n";
    
    // Now manually do what mergeCartInstances does
    Cart::setInstance('guest_123');
    echo "Source cart instance: " . Cart::instance() . "\n";
    echo "Source cart content count: " . Cart::content()->count() . "\n";
    
    $sourceItems = Cart::content();
    
    foreach ($sourceItems as $sourceItem) {
        echo "Processing item: {$sourceItem->id} - {$sourceItem->name} - Qty: {$sourceItem->quantity}\n";
        
        // Switch to target cart
        Cart::setInstance('user_1');
        echo "Switched to user cart. Instance: " . Cart::instance() . "\n";
        echo "Target cart count before add: " . Cart::count() . "\n";
        
        // Add item to target cart
        $newItem = Cart::add(
            $sourceItem->id,
            $sourceItem->name,
            $sourceItem->price,
            $sourceItem->quantity,
            $sourceItem->attributes->toArray()
        );
        
        echo "Added item. New item class: " . get_class($newItem) . "\n";
        echo "Target cart count after add: " . Cart::count() . "\n";
        echo "Target cart content: " . Cart::content()->count() . " items\n";
    }
    
    echo "Final user cart count: " . Cart::setInstance('user_1')->count() . "\n";
    echo "Final guest cart count: " . Cart::setInstance('guest_123')->count() . "\n";
    
    expect(Cart::setInstance('user_1')->count())->toBeGreaterThan(0);
});
