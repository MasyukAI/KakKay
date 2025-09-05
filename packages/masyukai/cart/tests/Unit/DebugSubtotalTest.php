<?php

declare(strict_types=1);

use MasyukAI\Cart\CartManager;

it('debugs subtotal calculation step by step', function () {
    // Setup integer transformer
    config(['cart.price_formatting.enabled' => true]);
    config(['cart.price_formatting.default_transformer' => 'integer']);
    
    $cart = app(CartManager::class)->session('session1');
    $cartInstance = $cart->get();
    $cartInstance->add(1, 'Test Product', 19.99, 1);
    
    $cartItem = $cartInstance->items()->first();
    
    // Debug the exact values and transformations
    dump('=== Cart Item Details ===');
    dump('Cart item price (property):', $cartItem->price);
    dump('Cart item quantity:', $cartItem->quantity);
    
    // Get the raw sum before formatting
    $rawSum = $cartItem->getPriceSum();
    dump('Raw price sum from getPriceSum():', $rawSum);
    dump('Type of raw sum:', gettype($rawSum));
    
    // Debug formatter behavior
    $formatter = app('cart.price_formatter');
    dump('=== Formatter Debug ===');
    dump('Formatter class:', get_class($formatter));
    
    $transformer = $formatter->getTransformer();
    dump('Transformer class:', get_class($transformer));
    
    // Test transformer methods directly on the raw sum
    dump('=== Transformer Methods on Raw Sum ===');
    dump('toNumeric(' . $rawSum . '):', $transformer->toNumeric($rawSum));
    dump('toDisplay(' . $rawSum . '):', $transformer->toDisplay($rawSum));
    
    // Test the exact formatter flow
    dump('=== Formatter Flow ===');
    dump('formatPrice(' . $rawSum . ', false):', $formatter->formatPrice($rawSum, false));
    
    // Now test the cart subtotal
    dump('=== Cart Subtotal ===');
    dump('Cart subtotal():', $cart->subtotal());
    
    // Enable formatting and test again
    dump('=== With Formatting Enabled ===');
    $cart->formatted();
    dump('Cart subtotal() after formatted():', $cart->subtotal());
    
    expect(true)->toBeTrue();
});
