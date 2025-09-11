<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Storage\SessionStorage;
use MasyukAI\Cart\Support\CartMoney;

it('debug cart total calculation', function () {
    // Enable formatting for this test
    CartMoney::enableFormatting();

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

    // Verify immutability - state should be unchanged after dump operations
    expect($cart->getItems()->count())->toBe($initialItemsCount);
    expect($cart->subtotal()->getAmount())->toBe($initialSubtotal->getAmount());
    expect($cart->total()->getAmount())->toBe($initialTotal->getAmount());

    // Add condition
    $condition = new CartCondition('tax', 'tax', 'subtotal', '+10%');
    $cart->addCondition($condition);

    // Capture state after condition
    $afterConditionItemsCount = $cart->getItems()->count();
    $afterConditionSubtotal = $cart->subtotal();
    $afterConditionTotal = $cart->total();

    // Verify immutability - state should be unchanged after dump operations
    expect($cart->getItems()->count())->toBe($afterConditionItemsCount);
    expect($cart->subtotal()->getAmount())->toBe($afterConditionSubtotal->getAmount());
    expect($cart->total()->getAmount())->toBe($afterConditionTotal->getAmount());

    // Test exact types and values - the cart returns formatted strings by default
    expect((string) $cart->total())->toBe('$220.00') // Should be 200 + 10% = 220, formatted as $220.00
        ->and((string) $cart->subtotal())->toBe('$200.00') // Subtotal stays 200, formatted as $200.00
        ->and((string) $cart->subtotalWithoutConditions())->toBe('$200.00'); // Same as subtotal

    // Verify exact return types - should be formatted strings by default when auto_format is enabled
    expect($cart->getRawTotal())->toBeFloat()
        ->and($cart->getRawSubtotal())->toBeFloat()
        ->and($cart->getRawSubTotalWithoutConditions())->toBeFloat();
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
    CartMoney::disableFormatting();

    expect($cart->total()->getAmount())->toBeFloat()
        ->and($cart->subtotal()->getAmount())->toBeFloat()
        ->and($cart->total()->getAmount())->toBe(218.89) // (99.99 + 49.50 * 2) * 1.10 = 198.99 * 1.10
        ->and($cart->subtotal()->getAmount())->toBe(198.99); // 99.99 + 49.50 * 2

    // Test with formatting ENABLED
    CartMoney::enableFormatting();

    expect((string) $cart->total())->toBeString()
        ->and((string) $cart->subtotal())->toBeString()
        ->and((string) $cart->total())->toBe('$218.89')
        ->and((string) $cart->subtotal())->toBe('$198.99');

    // Reset formatting state
    CartMoney::disableFormatting();
});
