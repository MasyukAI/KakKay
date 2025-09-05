<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;

it('debugs exact values in integer transformer flow', function () {
    // Setup integer transformer like the working test
    config([
        'cart.price_formatting.auto_format' => true,
        'cart.price_formatting.transformer' => \MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer::class,
    ]);
    
    // Reset the formatter to pick up new config
    \MasyukAI\Cart\Support\PriceFormatManager::resetFormatting();
    
    // Add item and track each step
    dump('=== Step 1: Adding item with price 19.99 ===');
    Cart::add('item-1', 'Test Item', 19.99, 1);
    
    $item = Cart::getItems()->first();
    dump('Item price property (direct):', $item->price);
    dump('Item getPrice() method:', $item->getPrice());
    dump('Item price property type:', gettype($item->price));
    dump('Item getPrice() method type:', gettype($item->getPrice()));
    
    // Test getPriceSum directly
    $priceSum = $item->getPriceSum();
    dump('=== Step 2: getPriceSum() ===');
    dump('getPriceSum() result:', $priceSum);
    dump('getPriceSum() type:', gettype($priceSum));
    
    // Test the cart subtotal calculation
    $cartSubtotal = Cart::subtotal();
    dump('=== Step 3: Cart::subtotal() ===');
    dump('Cart subtotal (raw):', $cartSubtotal);
    dump('Cart subtotal type:', gettype($cartSubtotal));
    
    // Now enable formatting and test again
    Cart::formatted();
    $formattedSubtotal = Cart::subtotal();
    dump('=== Step 4: Cart::subtotal() with formatting ===');
    dump('Formatted subtotal:', $formattedSubtotal);
    dump('Formatted subtotal type:', gettype($formattedSubtotal));
    
    expect(true)->toBeTrue();
});
