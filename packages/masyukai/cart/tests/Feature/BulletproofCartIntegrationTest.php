<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;

it('can access cart facade properly', function () {
    // Clear any existing cart data
    Cart::clear();
    
    // Test basic cart operations
    expect(Cart::isEmpty())->toBeTrue();
    expect(Cart::count())->toBe(0);
    expect(Cart::getTotal())->toBe(0.0);
    
    // Add an item to the cart
    $item = Cart::add('test-product', 'Test Product', 10.99, 2);
    
    expect(Cart::isEmpty())->toBeFalse();
    expect(Cart::getContent())->toHaveCount(1); // Number of unique items
    expect(Cart::getTotalQuantity())->toBe(2); // Total quantity across all items
    expect(Cart::getSubTotal())->toBe(21.98);
    
    // Test item properties
    expect($item->id)->toBe('test-product');
    expect($item->name)->toBe('Test Product');
    expect($item->price)->toBe(10.99);
    expect($item->quantity)->toBe(2);
});

it('can perform comprehensive cart operations', function () {
    Cart::clear();
    
    // Test multiple items
    Cart::add('product-1', 'Product 1', 15.00, 1);
    Cart::add('product-2', 'Product 2', 25.00, 2);
    Cart::add('product-3', 'Product 3', 35.00, 3);
    
    expect(Cart::getContent())->toHaveCount(3); // Number of unique items
    expect(Cart::getTotalQuantity())->toBe(6);
    expect(Cart::getSubTotal())->toBe(170.00); // 15 + 50 + 105
    
    // Test updating quantities (this adds to existing quantity)
    Cart::update('product-2', ['quantity' => 5]);
    
    // After update: product-1 (1) + product-2 (2+5=7) + product-3 (3) = 11
    expect(Cart::getTotalQuantity())->toBe(11); // 1 + 7 + 3
    expect(Cart::getSubTotal())->toBe(295.00); // 15 + 175 + 105
    
    // Test removing items
    Cart::remove('product-1');
    expect(Cart::getContent())->toHaveCount(2); // Number of unique items
    expect(Cart::getTotalQuantity())->toBe(10); // 7 + 3 (after removing product-1)
    expect(Cart::getSubTotal())->toBe(280.00); // 175 + 105
    
    // Test searching
    $expensiveItems = Cart::search(function ($item) {
        return $item->price > 30.00;
    });
    expect($expensiveItems)->toHaveCount(1);
    
    // Test clearing
    Cart::clear();
    expect(Cart::isEmpty())->toBeTrue();
});

it('handles validation and edge cases', function () {
    Cart::clear();
    
    // Test that duplicate IDs update quantity
    Cart::add('duplicate-test', 'Duplicate Test', 10.00, 1);
    Cart::add('duplicate-test', 'Duplicate Test', 10.00, 2);
    
    expect(Cart::getContent())->toHaveCount(1); // Number of unique items
    expect(Cart::get('duplicate-test')->quantity)->toBe(3);
    
    // Test updating non-existent item
    $result = Cart::update('non-existent', ['quantity' => 5]);
    expect($result)->toBeNull();
    
    // Test removing non-existent item
    Cart::remove('non-existent'); // Should not throw an error
    expect(Cart::getContent())->toHaveCount(1); // Still has the duplicate-test item
    
    // Test getting non-existent item
    expect(Cart::get('non-existent'))->toBeNull();
});

it('works with cart conditions', function () {
    Cart::clear();
    
    // Add items
    Cart::add('taxable-item', 'Taxable Item', 100.00, 1);
    
    // Create and apply conditions
    $taxCondition = new \MasyukAI\Cart\Conditions\CartCondition(
        name: 'VAT',
        type: 'tax',
        target: 'subtotal', 
        value: '10%'
    );
    
    Cart::condition($taxCondition);
    
    expect(Cart::getConditions())->toHaveCount(1);
    expect(Cart::getTotal())->toBe(110.00); // 100 + 10% tax
    
    // Remove condition
    Cart::removeCondition('VAT');
    expect(Cart::getConditions())->toHaveCount(0);
    expect(Cart::getTotal())->toBe(100.00);
});

it('persists data correctly', function () {
    Cart::clear();
    
    // Add items
    Cart::add('persistent-item', 'Persistent Item', 50.00, 2);
    
    // Get content to verify persistence
    $content = Cart::getContent();
    expect($content)->toHaveCount(1);
    expect($content->first()->id)->toBe('persistent-item');
    expect($content->first()->quantity)->toBe(2);
    
    // Test toArray functionality
    $cartArray = Cart::toArray();
    expect($cartArray)->toHaveKey('items');
    expect($cartArray['items'])->toHaveCount(1);
    expect($cartArray['quantity'])->toBe(2); // Uses 'quantity' not 'total_quantity'
    expect($cartArray['subtotal'])->toBe(100.00);
});
