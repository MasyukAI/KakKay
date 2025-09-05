<?php

declare(strict_types=1);

use MasyukAI\Cart\CartManager;

it('debugs subtotal calculation step by step', function () {
    // Setup integer transformer
    config(['cart.price_formatting.enabled' => true]);
    config(['cart.price_formatting.default_transformer' => 'integer']);
    
    $cartManager = app(CartManager::class);
    $cartManager->setInstance('session1');
    $cartInstance = $cartManager->getCurrentCart();
    $cartInstance->add('1', 'Test Product', 19.99, 1);
    
    $cartItem = $cartInstance->getItems()->first();
    
    // Debug the exact values and transformations
    dump('=== Cart Item Details ===');
    dump('Cart item price (property):', $cartItem->price);
    dump('Cart item quantity:', $cartItem->quantity);
    
    // Get the raw sum before formatting
    $rawSum = $cartItem->getPriceSum();
    dump('Raw price sum from getPriceSum():', $rawSum);
    dump('Type of raw sum:', gettype($rawSum));
    
    // Debug formatter behavior
    $formatter = \MasyukAI\Cart\Support\PriceFormatManager::getFormatter();
    dump('=== Formatter Debug ===');
    dump('Formatter class:', get_class($formatter));
    
    // Test the exact formatter flow
    dump('=== Formatter Flow ===');
    dump('format(' . $rawSum . '):', $formatter->format($rawSum));
    
    // Now test the cart subtotal
    dump('=== Cart Subtotal ===');
    dump('Cart subtotal():', $cartManager->subtotal());
    
    // Enable formatting and test again
    dump('=== With Formatting Enabled ===');
    $cartManager->formatted();
    dump('Cart subtotal() after formatted():', $cartManager->subtotal());
    
    expect(true)->toBeTrue();
});
