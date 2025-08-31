<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;

it('diagnoses cart behavior', function () {
    Cart::clear();
    
    // Step 1: Add items and check quantities
    Cart::add('product-1', 'Product 1', 15.00, 1);
    Cart::add('product-2', 'Product 2', 25.00, 2);
    Cart::add('product-3', 'Product 3', 35.00, 3);
    
    expect(Cart::getContent())->toHaveCount(3);
    expect(Cart::getTotalQuantity())->toBe(6); // 1 + 2 + 3
    
    // Step 2: Update product-2 quantity to 5
    $updateResult = Cart::update('product-2', ['quantity' => 5]);
    dump('After update - Total quantity: ' . Cart::getTotalQuantity());
    dump('Product-2 quantity: ' . Cart::get('product-2')->quantity);
    
    // Check what we actually have
    foreach (Cart::getContent() as $item) {
        dump("Item {$item->id}: quantity = {$item->quantity}");
    }
    
    expect(true)->toBeTrue(); // Just to pass the test while we debug
});

it('checks cart toArray structure', function () {
    Cart::clear();
    Cart::add('test-item', 'Test Item', 50.00, 2);
    
    $cartArray = Cart::toArray();
    dump('Cart toArray structure:', $cartArray);
    
    expect(true)->toBeTrue(); // Just to pass the test while we debug
});
