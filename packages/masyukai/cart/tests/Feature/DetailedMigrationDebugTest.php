<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Services\CartMigrationService;

it('can debug detailed migration process step by step', function (): void {
    $cartMigration = new \MasyukAI\Cart\Services\CartMigrationService();

    // Clear any existing data
    Cart::clear();
    
    // Add items to cart (current session will be guest)
    Cart::add('product_1', 'Product 1', 10.99, 2);
    Cart::add('product_2', 'Product 2', 5.99, 1);
    
    expect(Cart::count())->toBe(3);
    expect(Cart::content()['items'])->toHaveCount(2);
    
    // Store current session ID for migration
    $currentSessionId = session()->getId();
    
    // Test migration from current session to user 1
    $result = $cartMigration->migrateGuestCartToUser(1, 'default', $currentSessionId);
    
    // Check if migration worked by counting items under user context
    // After migration, original session cart should be empty, and user cart should have items
    $userCartCount = Cart::storage()->getItems('1', 'default');
    $userItemCount = array_sum(array_column($userCartCount, 'quantity'));
    
    expect($result)->toBeTrue();
    expect($userItemCount)->toBe(3);
    expect(Cart::count())->toBe(0); // Guest cart should be empty after migration
});
