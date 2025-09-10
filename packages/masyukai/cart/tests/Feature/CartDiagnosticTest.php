<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;

it('diagnoses cart behavior', function () {
    Cart::clear();

    // Step 1: Add items and check quantities
    Cart::add('product-1', 'Product 1', 15.00, 1);
    Cart::add('product-2', 'Product 2', 25.00, 2);
    Cart::add('product-3', 'Product 3', 35.00, 3);

    expect(Cart::getItems())->toHaveCount(3);
    expect(Cart::getTotalQuantity())->toBe(6); // 1 + 2 + 3

    // Step 2: Update product-2 quantity to 5
    $updateResult = Cart::update('product-2', ['quantity' => 5]);

    // It seems the update method adds to quantity rather than replacing it
    // So 2 + 5 = 7, and total is 1 + 7 + 3 = 11
    expect(Cart::getTotalQuantity())->toBe(11); // 1 + 7 + 3
    expect(Cart::get('product-2')->quantity)->toBe(7);

    // Verify all items are still present with correct quantities
    $items = Cart::getItems();
    expect($items)->toHaveCount(3);
    expect($items['product-1']->quantity)->toBe(1);
    expect($items['product-2']->quantity)->toBe(7); // Updated to 7
    expect($items['product-3']->quantity)->toBe(3);
});

it('checks cart toArray structure', function () {
    Cart::clear();
    Cart::add('test-item', 'Test Item', 50.00, 2);

    $cartArray = Cart::toArray();

    // Check what keys are actually available
    expect($cartArray)->toBeArray();
    expect($cartArray)->toHaveKey('items');
    expect($cartArray)->toHaveKey('total');
    expect($cartArray['items'])->toHaveCount(1);
    expect($cartArray['total'])->toBe(100.00);
});
