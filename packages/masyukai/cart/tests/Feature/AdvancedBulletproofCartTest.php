<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;

it('performs stress testing with large datasets', function () {
    Cart::clear();
    
    // Add 1000 unique products
    for ($i = 1; $i <= 1000; $i++) {
        Cart::add("product-{$i}", "Product {$i}", 10.00 + ($i * 0.01), rand(1, 5));
    }
    
    expect(Cart::getContent())->toHaveCount(1000);
    expect(Cart::getTotalQuantity())->toBeGreaterThan(1000);
    expect(Cart::getSubTotal())->toBeGreaterThan(10000.00);
    
    // Test search performance on large dataset
    $expensiveItems = Cart::search(function ($item) {
        return $item->price > 15.00;
    });
    
    expect($expensiveItems->count())->toBeGreaterThan(400);
    
    // Test batch operations
    for ($i = 1; $i <= 100; $i++) {
        Cart::remove("product-{$i}");
    }
    
    expect(Cart::getContent())->toHaveCount(900);
    
    Cart::clear();
    expect(Cart::isEmpty())->toBeTrue();
});

it('handles complex condition chains bulletproof', function () {
    Cart::clear();
    
    // Add products
    Cart::add('luxury-item', 'Luxury Item', 1000.00, 1);
    Cart::add('regular-item', 'Regular Item', 100.00, 2);
    
    // Apply multiple overlapping conditions
    $conditions = [
        new \MasyukAI\Cart\Conditions\CartCondition('vip-discount', 'discount', 'subtotal', '-10%', ['priority' => 1]),
        new \MasyukAI\Cart\Conditions\CartCondition('bulk-discount', 'discount', 'subtotal', '-5%', ['priority' => 2]),
        new \MasyukAI\Cart\Conditions\CartCondition('vat', 'tax', 'subtotal', '+20%', ['priority' => 3]),
        new \MasyukAI\Cart\Conditions\CartCondition('service-fee', 'fee', 'subtotal', '+25.00', ['priority' => 4]),
        new \MasyukAI\Cart\Conditions\CartCondition('express-shipping', 'shipping', 'subtotal', '+50.00', ['priority' => 5]),
    ];
    
    foreach ($conditions as $condition) {
        Cart::condition($condition);
    }
    
    expect(Cart::getConditions())->toHaveCount(5);
    
    $total = Cart::getTotal();
    expect($total)->toBeFloat();
    expect($total)->toBeGreaterThan(0);
    
    // Test removing specific conditions
    Cart::removeCondition('bulk-discount');
    expect(Cart::getConditions())->toHaveCount(4);
    
    $newTotal = Cart::getTotal();
    expect($newTotal)->toBeFloat();
    expect($newTotal)->toBeGreaterThan(0);
    
    // Clear all conditions
    Cart::clearConditions();
    expect(Cart::getConditions())->toHaveCount(0);
    expect(Cart::getTotal())->toBe(Cart::getSubTotal());
});

it('handles edge cases and invalid operations gracefully', function () {
    Cart::clear();
    
    // Test with extreme values
    Cart::add('extreme-price', 'Extreme Price Item', 999999.99, 1);
    Cart::add('extreme-quantity', 'Extreme Quantity Item', 0.01, 10000);
    
    expect(Cart::getContent())->toHaveCount(2);
    expect(Cart::getTotalQuantity())->toBe(10001);
    
    // Test with special characters in names and IDs
    $specialChars = [
        'emoji-🚀' => 'Product with emoji 🚀',
        'unicode-测试' => 'Product with 中文 characters',
        'special-@#$%' => 'Product with @#$% symbols',
        'spaces and tabs' => "Product\twith\nspecial\rwhitespace",
    ];
    
    foreach ($specialChars as $id => $name) {
        Cart::add($id, $name, 10.00, 1);
    }
    
    expect(Cart::getContent())->toHaveCount(6);
    
    // Test retrieving special character items
    expect(Cart::get('emoji-🚀'))->not->toBeNull();
    expect(Cart::get('unicode-测试'))->not->toBeNull();
    expect(Cart::get('special-@#$%'))->not->toBeNull();
    expect(Cart::get('spaces and tabs'))->not->toBeNull();
    
    // Test operations on non-existent items
    expect(Cart::get('non-existent-item'))->toBeNull();
    expect(Cart::update('non-existent-item', ['quantity' => 5]))->toBeNull();
    
    // These should not throw errors
    Cart::remove('non-existent-item');
    Cart::removeCondition('non-existent-condition');
    
    // Test with zero quantities (update adds to existing quantity)
    Cart::add('temporary-item', 'Temporary Item', 10.00, 1);
    expect(Cart::get('temporary-item'))->not->toBeNull();
    
    // Update adds to existing quantity, so 1 + 0 = 1
    Cart::update('temporary-item', ['quantity' => 0]);
    $tempItem = Cart::get('temporary-item');
    expect($tempItem)->not->toBeNull();
    expect($tempItem->quantity)->toBe(1); // Still 1 because 1 + 0 = 1
});

it('maintains data integrity under concurrent-like operations', function () {
    Cart::clear();
    
    // Simulate rapid operations that might cause race conditions
    $baseItem = Cart::add('concurrent-test', 'Concurrent Test Item', 25.50, 3);
    $originalTotal = Cart::getSubTotal();
    
    // Perform rapid updates
    for ($i = 1; $i <= 50; $i++) {
        Cart::update('concurrent-test', ['name' => "Updated Name {$i}"]);
        Cart::update('concurrent-test', ['price' => 25.50 + ($i * 0.1)]);
        Cart::update('concurrent-test', ['quantity' => 1]); // Add 1 to quantity each time
    }
    
    $finalItem = Cart::get('concurrent-test');
    expect($finalItem)->not->toBeNull();
    expect($finalItem->id)->toBe('concurrent-test');
    expect($finalItem->name)->toBe('Updated Name 50');
    expect($finalItem->price)->toBe(30.50); // 25.50 + (50 * 0.1)
    expect($finalItem->quantity)->toBe(53); // 3 + 50
    
    // Verify cart totals are consistent
    $calculatedTotal = $finalItem->price * $finalItem->quantity;
    expect(Cart::getSubTotal())->toBe($calculatedTotal);
    
    // Test with attributes during rapid updates
    for ($i = 1; $i <= 25; $i++) {
        Cart::update('concurrent-test', [
            'attributes' => [
                'color' => "color-{$i}",
                'size' => "size-{$i}",
                'batch' => $i,
            ]
        ]);
    }
    
    $finalItem = Cart::get('concurrent-test');
    expect($finalItem->getAttribute('color'))->toBe('color-25');
    expect($finalItem->getAttribute('size'))->toBe('size-25');
    expect($finalItem->getAttribute('batch'))->toBe(25);
});

it('handles precision and floating point calculations bulletproof', function () {
    Cart::clear();
    
    // Test with prices that are known to cause floating point issues
    $problematicPrices = [
        0.1 + 0.2, // Classic floating point issue
        1.0 / 3.0, // Repeating decimal
        0.3333333333333333,
        9.999999999999998,
        0.0000000000000001,
        999999999999.99,
    ];
    
    foreach ($problematicPrices as $index => $price) {
        Cart::add("precision-{$index}", "Precision Test {$index}", $price, 1);
    }
    
    expect(Cart::getContent())->toHaveCount(6);
    
    // Ensure all calculations are stable
    $total = Cart::getSubTotal();
    expect($total)->toBeFloat();
    expect(is_finite($total))->toBeTrue(); // Check if number is finite
    expect($total)->toBeGreaterThan(0);
    
    // Test with percentage-based conditions on problematic prices
    $taxCondition = new \MasyukAI\Cart\Conditions\CartCondition(
        'precision-tax',
        'tax',
        'subtotal',
        '15.555%' // Problematic percentage
    );
    
    Cart::condition($taxCondition);
    
    $totalWithTax = Cart::getTotal();
    expect($totalWithTax)->toBeFloat();
    expect(is_finite($totalWithTax))->toBeTrue(); // Check if number is finite
    expect($totalWithTax)->toBeGreaterThan($total);
    
    // Test quantity updates with integer quantities only (CartItem requires int)
    Cart::update('precision-0', ['quantity' => 3]); // Use integer instead of float
    expect(Cart::get('precision-0')->quantity)->toBe(4); // Original 1 + 3 = 4
});

it('provides comprehensive cart state serialization', function () {
    Cart::clear();
    
    // Build a complex cart state
    Cart::add('serialization-1', 'Serialization Test 1', 15.99, 2, [
        'color' => 'red',
        'size' => 'large',
        'metadata' => ['sku' => 'SKU001', 'category' => 'electronics'],
    ]);
    
    Cart::add('serialization-2', 'Serialization Test 2', 25.50, 1, [
        'color' => 'blue',
        'warranty' => true,
        'specs' => ['weight' => '2.5kg', 'dimensions' => '30x20x10cm'],
    ]);
    
    // Add conditions
    Cart::condition(new \MasyukAI\Cart\Conditions\CartCondition('bulk-discount', 'discount', 'subtotal', '-10%'));
    Cart::condition(new \MasyukAI\Cart\Conditions\CartCondition('express-shipping', 'shipping', 'subtotal', '+15.00'));
    
    // Test toArray serialization
    $cartArray = Cart::toArray();
    
    expect($cartArray)->toBeArray();
    expect($cartArray['items'])->toHaveCount(2);
    expect($cartArray['conditions'])->toHaveCount(2);
    expect($cartArray['subtotal'])->toBeFloat();
    expect($cartArray['total'])->toBeFloat();
    expect($cartArray['quantity'])->toBe(3);
    expect($cartArray['is_empty'])->toBeFalse();
    
    // Verify item serialization includes all data
    $item1 = $cartArray['items']['serialization-1'];
    expect($item1['attributes'])->toHaveKey('color');
    expect($item1['attributes'])->toHaveKey('size');
    expect($item1['attributes'])->toHaveKey('metadata');
    expect($item1['attributes']['metadata'])->toHaveKey('sku');
    
    // Test JSON serialization
    $cartJson = json_encode($cartArray);
    expect($cartJson)->toBeString();
    expect(json_last_error())->toBe(JSON_ERROR_NONE);
    
    // Test deserialization integrity
    $decodedCart = json_decode($cartJson, true);
    expect($decodedCart)->toBeArray();
    expect($decodedCart['items'])->toHaveCount(2);
    expect($decodedCart['total'])->toBe($cartArray['total']);
});
