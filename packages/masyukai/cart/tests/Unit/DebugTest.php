<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Storage\SessionStorage;
use MasyukAI\Cart\Support\PriceFormatManager;

it('debug cart total calculation', function () {
    // Set up session storage for testing
    $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
    $storage = new SessionStorage($sessionStore);

    $cart = new Cart(
        storage: $storage,
        events: new \Illuminate\Events\Dispatcher,
        instanceName: 'debug',
        eventsEnabled: false
    );

    // Add items
    $cart->add('product-1', 'Product 1', 100.00, 1);
    $cart->add('product-2', 'Product 2', 50.00, 2);

    // Capture initial state
    $initialItemsCount = $cart->getItems()->count();
    $initialSubtotal = $cart->subtotal();
    $initialTotal = $cart->total();

    dump('Items count: '.$cart->getItems()->count());
    dump('Cart subtotal: '.$cart->subtotal());
    dump('Cart subtotal without conditions: '.$cart->subtotalWithoutConditions());
    dump('Cart total: '.$cart->total());

    // Verify immutability - state should be unchanged after dump operations
    expect($cart->getItems()->count())->toBe($initialItemsCount);
    expect($cart->subtotal())->toBe($initialSubtotal);
    expect($cart->total())->toBe($initialTotal);

    // Add condition
    $condition = new CartCondition('tax', 'tax', 'subtotal', '+10%');
    $cart->addCondition($condition);

    // Capture state after condition
    $afterConditionItemsCount = $cart->getItems()->count();
    $afterConditionSubtotal = $cart->subtotal();
    $afterConditionTotal = $cart->total();

    dump('--- After adding cart condition ---');
    dump('Items count after condition: '.$cart->getItems()->count());
    dump('Cart conditions count: '.$cart->getConditions()->count());
    dump('Cart subtotal: '.$cart->subtotal());
    dump('Cart subtotal without conditions: '.$cart->subtotalWithoutConditions());
    dump('Cart total: '.$cart->total());

    // Verify immutability - state should be unchanged after dump operations
    expect($cart->getItems()->count())->toBe($afterConditionItemsCount);
    expect($cart->subtotal())->toBe($afterConditionSubtotal);
    expect($cart->total())->toBe($afterConditionTotal);

    // Test exact types and values
    expect($cart->total())->toBe(220.0) // Should be 200 + 10% = 220
        ->and($cart->subtotal())->toBe(200.0) // Subtotal stays 200
        ->and($cart->subtotalWithoutConditions())->toBe(200.0); // Same as subtotal

    // Verify exact return types
    expect($cart->total())->toBeFloat()
        ->and($cart->subtotal())->toBeFloat()
        ->and($cart->subtotalWithoutConditions())->toBeFloat();
});

it('verifies cart method return types with and without formatting', function () {
    // Set up session storage for testing
    $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
    $storage = new SessionStorage($sessionStore);

    $cart = new Cart(
        storage: $storage,
        events: new \Illuminate\Events\Dispatcher,
        instanceName: 'format_test',
        eventsEnabled: false
    );

    // Add items
    $cart->add('product-1', 'Product 1', 99.99, 1);
    $cart->add('product-2', 'Product 2', 49.50, 2);
    
    // Add condition
    $condition = new CartCondition('tax', 'tax', 'subtotal', '+10%');
    $cart->addCondition($condition);

    // Test with formatting DISABLED (default)
    PriceFormatManager::disableFormatting();
    
    dump('=== FORMATTING DISABLED ===');
    dump('Total type: ' . gettype($cart->total()));
    dump('Total value: ' . $cart->total());
    dump('Subtotal type: ' . gettype($cart->subtotal()));
    dump('Subtotal value: ' . $cart->subtotal());
    
    expect($cart->total())->toBeFloat()
        ->and($cart->subtotal())->toBeFloat()
        ->and($cart->total())->toBe(218.89) // (99.99 + 49.50 * 2) * 1.10 = 198.99 * 1.10
        ->and($cart->subtotal())->toBe(198.99); // 99.99 + 49.50 * 2

    // Test with formatting ENABLED
    PriceFormatManager::enableFormatting();
    
    dump('=== FORMATTING ENABLED ===');
    dump('Total type: ' . gettype($cart->total()));
    dump('Total value: ' . $cart->total());
    dump('Subtotal type: ' . gettype($cart->subtotal()));
    dump('Subtotal value: ' . $cart->subtotal());
    
    expect($cart->total())->toBeString()
        ->and($cart->subtotal())->toBeString()
        ->and($cart->total())->toBe('218.89')
        ->and($cart->subtotal())->toBe('198.99');
        
    // Reset formatting state
    PriceFormatManager::disableFormatting();
});
