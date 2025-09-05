<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Support\PriceFormatManager;

beforeEach(function () {
    Cart::clear();
    PriceFormatManager::resetFormatting();
});

it('can format prices when auto_format is enabled', function () {
    config(['cart.price_formatting.auto_format' => true]);
    
    Cart::add('item-1', 'Test Item', 19.99, 1);
    
    expect(Cart::subtotal())->toBe('19.99');
    expect(Cart::total())->toBe('19.99');
});

it('returns raw prices when auto_format is disabled', function () {
    config(['cart.price_formatting.auto_format' => false]);
    
    Cart::add('item-1', 'Test Item', 19.99, 1);
    
    expect(Cart::subtotal())->toBe(19.99);
    expect(Cart::total())->toBe(19.99);
});

it('can override formatting with methods', function () {
    config(['cart.price_formatting.auto_format' => false]);
    
    Cart::add('item-1', 'Test Item', 19.99, 1);
    
    // Test fluent API
    expect(Cart::formatted()->subtotal())->toBe('19.99');
    expect(Cart::raw()->subtotal())->toBe(19.99);
});

it('can format with currency', function () {
    config([
        'cart.price_formatting.auto_format' => false,
        'cart.price_formatting.show_currency_symbol' => true,
    ]);
    
    Cart::add('item-1', 'Test Item', 19.99, 1);
    
    expect(Cart::currency('USD')->subtotal())->toContain('19.99');
});

it('formats cart item prices correctly', function () {
    config(['cart.price_formatting.auto_format' => true]);
    
    Cart::add('item-1', 'Test Item', 19.99, 2);
    $item = Cart::get('item-1');
    
    expect($item->getPrice())->toBe('19.99');
    expect($item->subtotal())->toBe('39.98');
});

it('can use integer price transformer', function () {
    config([
        'cart.price_formatting.auto_format' => true,
        'cart.price_formatting.transformer' => \MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer::class,
    ]);
    
    // Reset the formatter to pick up new config
    PriceFormatManager::resetFormatting();
    
    // Add item with decimal price (will be stored as cents)
    Cart::add('item-1', 'Test Item', 19.99, 1);
    
    expect(Cart::subtotal())->toBe('19.99');
});
