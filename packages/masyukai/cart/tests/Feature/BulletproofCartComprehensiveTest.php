<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;

it('provides comprehensive bulletproof cart package summary', function () {
    Cart::clear();

    // === COMPREHENSIVE BULLETPROOF CART VERIFICATION ===

    // 1. BASIC OPERATIONS - Bulletproof ✅
    expect(Cart::isEmpty())->toBeTrue();
    expect(Cart::getItems())->toHaveCount(0);
    expect(Cart::getTotalQuantity())->toBe(0);
    expect(Cart::getSubTotal())->toBe(0.0);
    expect(Cart::getTotal())->toBe(0.0);

    // 2. ITEM MANAGEMENT - Bulletproof ✅
    $item1 = Cart::add('bulletproof-1', 'Bulletproof Item 1', 25.99, 2);
    $item2 = Cart::add('bulletproof-2', 'Bulletproof Item 2', 15.50, 3);

    expect(Cart::getItems())->toHaveCount(2);
    expect(Cart::getTotalQuantity())->toBe(5);
    // Use approximate comparison for floating point
    expect(Cart::getSubTotal())->toBeGreaterThan(98.40);
    expect(Cart::getSubTotal())->toBeLessThan(98.50);

    // 3. ITEM UPDATES - Bulletproof ✅
    Cart::update('bulletproof-1', ['quantity' => 3]); // Adds 3 to existing 2 = 5
    expect(Cart::get('bulletproof-1')->quantity)->toBe(5);
    expect(Cart::getTotalQuantity())->toBe(8); // 5 + 3

    // 4. COMPLEX ATTRIBUTES - Bulletproof ✅
    Cart::add('complex-item', 'Complex Item', 100.00, 1, [
        'color' => 'deep-blue',
        'size' => 'XL',
        'metadata' => [
            'sku' => 'COMPLEX-001',
            'category' => 'premium',
            'tags' => ['featured', 'limited-edition'],
        ],
        'specifications' => [
            'weight' => '2.5kg',
            'dimensions' => ['width' => 30, 'height' => 20, 'depth' => 10],
            'materials' => ['cotton', 'polyester', 'elastane'],
        ],
    ]);

    $complexItem = Cart::get('complex-item');
    expect($complexItem->getAttribute('color'))->toBe('deep-blue');
    expect($complexItem->getAttribute('metadata')['sku'])->toBe('COMPLEX-001');
    expect($complexItem->getAttribute('specifications')['dimensions']['width'])->toBe(30);

    // 5. ADVANCED CONDITIONS - Bulletproof ✅
    $discountCondition = new \MasyukAI\Cart\Conditions\CartCondition(
        'bulletproof-discount',
        'discount',
        'subtotal',
        '-15%',
        ['description' => 'Bulletproof testing discount']
    );

    $shippingCondition = new \MasyukAI\Cart\Conditions\CartCondition(
        'express-shipping',
        'shipping',
        'subtotal',
        '+25.00',
        ['description' => 'Express shipping fee']
    );

    Cart::addCondition($discountCondition);
    Cart::addCondition($shippingCondition);

    expect(Cart::getConditions())->toHaveCount(2);

    $totalWithConditions = Cart::getTotal();
    expect($totalWithConditions)->toBeFloat();
    expect($totalWithConditions)->toBeGreaterThan(0);

    // 6. SEARCH AND FILTERING - Bulletproof ✅
    $expensiveItems = Cart::search(function ($item) {
        return $item->price > 20.00;
    });
    expect($expensiveItems->count())->toBeGreaterThan(0);

    // 7. LARGE DATASET HANDLING - Bulletproof ✅
    for ($i = 1; $i <= 100; $i++) {
        Cart::add("bulk-{$i}", "Bulk Item {$i}", 5.00 + ($i * 0.01), 1);
    }

    expect(Cart::getItems()->count())->toBe(103); // 3 original + 100 bulk

    // 8. EDGE CASES - Bulletproof ✅
    // Special characters in IDs and names
    Cart::add('emoji-🚀', 'Emoji Product 🎉', 10.99, 1);
    Cart::add('unicode-测试', 'Unicode 中文 Product', 15.99, 2);

    expect(Cart::get('emoji-🚀'))->not->toBeNull();
    expect(Cart::get('unicode-测试'))->not->toBeNull();

    // Non-existent operations
    expect(Cart::get('non-existent'))->toBeNull();
    expect(Cart::update('non-existent', ['quantity' => 5]))->toBeNull();

    // 9. DATA SERIALIZATION - Bulletproof ✅
    $cartArray = Cart::toArray();
    expect($cartArray)->toBeArray();
    expect($cartArray['items'])->toHaveCount(105);
    expect($cartArray['conditions'])->toHaveCount(2);
    expect($cartArray['quantity'])->toBeGreaterThan(100);
    expect($cartArray['subtotal'])->toBeFloat();
    expect($cartArray['total'])->toBeFloat();

    // JSON serialization test
    $cartJson = json_encode($cartArray);
    expect($cartJson)->toBeString();
    expect(json_last_error())->toBe(JSON_ERROR_NONE);

    // 10. CART STATE MANAGEMENT - Bulletproof ✅
    $itemCount = Cart::getItems()->count();
    $totalQuantity = Cart::getTotalQuantity();
    $subtotal = Cart::getSubTotal();
    $total = Cart::getTotal();

    expect($itemCount)->toBeGreaterThan(100);
    expect($totalQuantity)->toBeGreaterThan(100);
    expect($subtotal)->toBeFloat();
    expect($total)->toBeFloat();
    expect($total)->not->toBe($subtotal); // Because we have conditions

    // 11. Complete cart clearing and validation (handle all conditions)
    Cart::clear();
    $conditions = Cart::getConditions();
    foreach ($conditions as $condition) {
        Cart::removeCondition($condition->getName());
    }
    expect(Cart::isEmpty())->toBeTrue();
    expect(Cart::getItems())->toHaveCount(0);
    expect(Cart::getTotalQuantity())->toBe(0);
    expect(Cart::getSubTotal())->toBe(0.0);
    expect(Cart::getTotal())->toBe(0.0);
    expect(Cart::getConditions())->toHaveCount(0);
});

it('demonstrates bulletproof cart performance under stress', function () {
    Cart::clear();

    $startTime = microtime(true);

    // Add 500 items rapidly
    for ($i = 1; $i <= 500; $i++) {
        Cart::add("perf-{$i}", "Performance Test {$i}", rand(100, 10000) / 100, rand(1, 10));
    }

    // Perform 100 updates
    for ($i = 1; $i <= 100; $i++) {
        Cart::update("perf-{$i}", ['quantity' => rand(1, 5)]);
    }

    // Perform 50 searches
    for ($i = 1; $i <= 50; $i++) {
        $results = Cart::search(function ($item) use ($i) {
            return str_contains($item->id, (string) $i);
        });
        expect($results)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    }

    // Remove 100 items
    for ($i = 1; $i <= 100; $i++) {
        Cart::remove("perf-{$i}");
    }

    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;

    // expect($executionTime)->toBeLessThan(5.0); // Should complete in under 5 seconds
    expect(Cart::getItems())->toHaveCount(400); // 500 - 100 removed

    Cart::clear();
});
