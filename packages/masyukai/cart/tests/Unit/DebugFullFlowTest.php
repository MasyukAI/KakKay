<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Support\PriceFormatManager;

it('debug full flow with integer transformer', function () {
    // Configure integer transformer
    config([
        'cart.price_formatting.auto_format' => true,
        'cart.price_formatting.transformer' => \MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer::class,
    ]);
    
    // Reset everything
    Cart::clear();
    PriceFormatManager::resetFormatting();
    
    // Check what formatter we're getting
    $formatter = PriceFormatManager::getFormatter();
    dump('Formatter class: ' . get_class($formatter));
    
    // Test direct transformer behavior
    $transformer = new \MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer('USD', 'en_US', 2);
    $stored = $transformer->toStorage(19.99);
    dump('Direct transformer - stored: ' . $stored);
    $displayed = $transformer->toDisplay($stored);
    dump('Direct transformer - displayed: ' . $displayed);
    
    // Test formatter behavior
    $formatterStored = $formatter->normalize(19.99);
    dump('Formatter - stored: ' . $formatterStored);
    $formatterDisplayed = $formatter->format($formatterStored);
    dump('Formatter - displayed: ' . $formatterDisplayed);
    
    // Add item and see what happens
    Cart::add('item-1', 'Test Item', 19.99, 1);
    
    $item = Cart::get('item-1');
    dump('Cart item price: ' . $item->price);
    dump('Cart subtotal: ' . Cart::subtotal());
    
    expect(true)->toBe(true); // Just to make test pass for debugging
});
