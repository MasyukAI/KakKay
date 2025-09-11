<?php

declare(strict_types=1);

use MasyukAI\Cart\Support\CartMoney;
use MasyukAI\Cart\Facades\Cart;

beforeEach(function () {
    CartMoney::reset();
    config(['cart.display.transformer' => 'MasyukAI\\Cart\\PriceTransformers\\IntegerPriceTransformer']);
    Cart::clear();
});

test('json migration verification works', function () {
    // Test empty cart
    expect(Cart::isEmpty())->toBeTrue();
    expect(Cart::count())->toBe(0);

    // Add item to cart
    Cart::add('test-item', 'Test Item', 19.99, 1);
    expect(Cart::count())->toBe(1);
    
    // Verify cart contains item
    $items = Cart::getItems();
    expect($items->count())->toBe(1);
    
    $item = $items->get('test-item');
    expect($item->name)->toBe('Test Item');
    expect($item->price)->toBe(19.99);
    
    // Test money calculations
    $subtotal = Cart::subtotal();
    expect($subtotal->getAmount())->toBe(19.99);
});
