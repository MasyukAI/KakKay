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
    expect(get_class($formatter))->toContain('PriceFormatterService');
    
    // Test direct transformer behavior
    $transformer = new \MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer('USD', 'en_US', 2);
    $stored = $transformer->toStorage(19.99);
    expect($stored)->toBe(1999);
    $displayed = $transformer->toDisplay($stored);
    expect($displayed)->toBe('19.99');
    
    // Test formatter behavior
    $formatterStored = $formatter->normalize(19.99);
    expect($formatterStored)->toBe(1999);
    $formatterDisplayed = $formatter->format($formatterStored);
    expect($formatterDisplayed)->toBe('19.99');
    
    // Add item and see what happens
    Cart::add('item-1', 'Test Item', 19.99, 1);
    
    $item = Cart::get('item-1');
    expect($item->price)->toBe(1999.0);
    expect(Cart::subtotal())->toBe('19.99');
    
    expect(true)->toBe(true); // Just to make test pass for debugging
});
