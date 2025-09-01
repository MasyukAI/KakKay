<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Tests\TestCase;
use MasyukAI\Cart\Services\CartMigrationService;

uses(TestCase::class);

it('debugs migration step by step', function () {
    $cartMigration = new CartMigrationService();
    
    // IMPORTANT: Cart identifiers vs instance names:
    // - setInstance() sets the INSTANCE NAME (cart type like 'default', 'wishlist')  
    // - Cart IDENTIFIER determines WHO owns the cart (user ID or session ID)
    // - The test below uses 'guest_123' as an identifier, not instance name
    
    // Setup guest cart with identifier 'guest_123' (simulating a guest session)
    // We'll migrate this TO user ID 1
    session(['id' => 'guest_123']); // Simulate guest session
    Cart::add('product-1', 'Test Product 1', 10.00, 2);
    Cart::add('product-2', 'Test Product 2', 15.00, 1);
    
    // Debug: Check what identifier Cart is actually using
    $currentIdentifier = session()->getId();
    dump("Current session identifier: $currentIdentifier");
    
    // Debug: Check what's in storage
    $storage = Cart::storage();
    $actualGuestItems = $storage->getItems($currentIdentifier, 'default');
    dump("Items found in storage for identifier '$currentIdentifier': " . count($actualGuestItems));
    
    // Check initial state - guest has items, user (ID 1) has none
    $guestCount = Cart::count(); // Current session (guest_123)
    
    // Simulate what user cart would look like (empty)
    $storage = Cart::storage();
    $userItems = $storage->getItems('1', 'default'); // User ID 1, default instance
    $userCount = array_sum(array_column($userItems, 'quantity'));
    
    dump("Before migration - Guest: $guestCount, User: $userCount");
    expect($guestCount)->toBe(3);
    expect($userCount)->toBe(0);
    
    // Perform actual migration: use the ACTUAL session identifier, not hardcoded 'guest_123'
    $result = $cartMigration->migrateGuestCartToUser(1, 'default', $currentIdentifier);
    dump("Migration result: " . ($result ? 'true' : 'false'));
    
    // Check final state - guest should be empty, user should have the items
    $guestCountAfter = Cart::count(); // Still on guest session
    
    $userItemsAfter = $storage->getItems('1', 'default'); 
    $userCountAfter = array_sum(array_column($userItemsAfter, 'quantity'));
    
    dump("After migration - Guest: $guestCountAfter, User: $userCountAfter");
    
    // Check actual items in user cart by accessing storage directly
    dump("User cart items after migration:");
    foreach ($userItemsAfter as $itemData) {
        dump("- {$itemData['id']}: {$itemData['name']} (qty: {$itemData['quantity']})");
    }
    
    expect($result)->toBeTrue();
    expect($userCountAfter)->toBe(3);
    expect($guestCountAfter)->toBe(0);
});
