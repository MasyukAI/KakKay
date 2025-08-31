<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Services\CartMigrationService;

it('can debug detailed migration process step by step', function () {
    $cartMigration = app(CartMigrationService::class);
    
    // Clear all carts first
    Cart::setInstance('default')->clear();
    Cart::setInstance('guest_123')->clear();
    Cart::setInstance('user_1')->clear();
    
    // Add items to guest cart
    Cart::setInstance('guest_123');
    expect(Cart::instance())->toBe('guest_123');
    
    Cart::add('product_1', 'Product 1', 10.99, 2);
    Cart::add('product_2', 'Product 2', 5.99, 1);
    
    expect(Cart::count())->toBe(3);
    expect(Cart::content())->toHaveCount(2);
    
    // Verify user cart is empty
    Cart::setInstance('user_1');
    expect(Cart::instance())->toBe('user_1');
    expect(Cart::count())->toBe(0);
    expect(Cart::content())->toHaveCount(0);
    
    echo "Before migration:\n";
    echo "Guest cart count: " . Cart::setInstance('guest_123')->count() . "\n";
    echo "User cart count: " . Cart::setInstance('user_1')->count() . "\n";
    
    // Test the mergeCartInstances method directly (protected, so we'll test via migration)
    $result = $cartMigration->migrateGuestCartToUser('guest_123', 1);
    
    echo "Migration result: " . ($result ? 'true' : 'false') . "\n";
    echo "After migration:\n";
    echo "Guest cart count: " . Cart::setInstance('guest_123')->count() . "\n";
    echo "User cart count: " . Cart::setInstance('user_1')->count() . "\n";
    
    // Check contents
    $userContent = Cart::setInstance('user_1')->content();
    echo "User cart content count: " . $userContent->count() . "\n";
    
    foreach ($userContent as $item) {
        echo "Item: {$item->id} - {$item->name} - Qty: {$item->quantity}\n";
    }
    
    expect($result)->toBeTrue();
    expect(Cart::setInstance('user_1')->count())->toBe(3);
    expect(Cart::setInstance('guest_123')->count())->toBe(0);
    
    $userItems = Cart::setInstance('user_1')->content();
    expect($userItems)->toHaveCount(2);
});
