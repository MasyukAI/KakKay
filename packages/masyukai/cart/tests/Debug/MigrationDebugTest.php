<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Tests\TestCase;
use MasyukAI\Cart\Services\CartMigrationService;

uses(TestCase::class);

it('debugs migration step by step', function () {
    $cartMigration = new CartMigrationService();
    
    // Setup guest cart exactly like CartMigrationTest
    Cart::setInstance('guest_123')->add('product-1', 'Test Product 1', 10.00, 2);
    Cart::setInstance('guest_123')->add('product-2', 'Test Product 2', 15.00, 1);
    
    // Check initial state
    $guestCount = Cart::setInstance('guest_123')->count();
    $userCount = Cart::setInstance('user_1')->count();
    
    dump("Before migration - Guest: $guestCount, User: $userCount");
    expect($guestCount)->toBe(3);
    expect($userCount)->toBe(0);
    
    // Perform actual migration
    $result = $cartMigration->migrateGuestCartToUser('guest_123', 1);
    dump("Migration result: " . ($result ? 'true' : 'false'));
    
    // Check final state
    $guestCountAfter = Cart::setInstance('guest_123')->count();
    $userCountAfter = Cart::setInstance('user_1')->count();
    
    dump("After migration - Guest: $guestCountAfter, User: $userCountAfter");
    
    // Check actual items in user cart
    $userItems = Cart::setInstance('user_1')->content();
    dump("User cart items after migration:");
    foreach ($userItems as $item) {
        dump("- {$item->id}: {$item->name} (qty: {$item->quantity})");
    }
    
    expect($result)->toBeTrue();
    expect($userCountAfter)->toBe(3);
    expect($guestCountAfter)->toBe(0);
});
