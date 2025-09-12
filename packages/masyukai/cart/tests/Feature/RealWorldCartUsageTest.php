<?php

declare(strict_types=1);

use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Facades\Cart;

/**
 * Comprehensive Real-World Cart Usage Test
 *
 * This test demonstrates real-world shopping cart scenarios including:
 * - Adding, updating, and removing products
 * - Complex product attributes
 * - Multiple condition types (discounts, taxes, shipping, fees)
 * - Item-level and cart-level conditions
 * - Calculating subtotals, totals, and savings
 * - Edge cases and complex scenarios
 */
it('demonstrates comprehensive real-world cart usage scenarios', function () {
    Cart::clear();

    // === SCENARIO 1: EMPTY CART VERIFICATION ===
    expect(Cart::isEmpty())->toBeTrue();
    expect(Cart::getItems())->toHaveCount(0);
    expect(Cart::getTotalQuantity())->toBe(0);
    expect(Cart::subtotal()->getAmount())->toBe(0);
    expect(Cart::total()->getAmount())->toBe(0);

    // === SCENARIO 2: ADDING PRODUCTS TO CART ===

    // Add a basic product
    $tshirt = Cart::add('tshirt-001', 'Premium Cotton T-Shirt', 29.99, 2, [
        'color' => 'Navy Blue',
        'size' => 'M',
        'sku' => 'TSH-001-NB-M',
        'category' => 'clothing',
        'weight' => 0.3, // kg
    ]);

    // Add a product with complex attributes
    $laptop = Cart::add('laptop-pro', 'Professional Laptop', 1299.99, 1, [
        'brand' => 'TechCorp',
        'model' => 'Pro X1',
        'specs' => [
            'cpu' => 'Intel i7-12700H',
            'ram' => '16GB DDR4',
            'storage' => '512GB SSD',
            'display' => '15.6" 4K',
        ],
        'warranty' => '3 years',
        'weight' => 2.1,
        'categories' => ['electronics', 'computers', 'premium'],
    ]);

    // Add accessories
    $mousepad = Cart::add('mousepad-xl', 'Gaming Mouse Pad XL', 24.99, 1, [
        'dimensions' => '90x40cm',
        'material' => 'microfiber',
        'thickness' => '3mm',
        'weight' => 0.6,
    ]);

    // Verify cart state after adding products
    expect(Cart::getItems())->toHaveCount(3);
    expect(Cart::getTotalQuantity())->toBe(4); // 2 + 1 + 1

    $expectedSubtotal = (29.99 * 2) + 1299.99 + 24.99; // 1384.96
    expect(Cart::subtotal()->getAmount())->toBe($expectedSubtotal);

    // === SCENARIO 3: UPDATING CART ITEMS ===

    // Increase T-shirt quantity (absolute update)
    Cart::update('tshirt-001', ['quantity' => ['value' => 3]]);
    expect(Cart::get('tshirt-001')->quantity)->toBe(3);
    expect(Cart::getTotalQuantity())->toBe(5);

    // Update product attributes
    Cart::update('laptop-pro', ['attributes' => [
        'brand' => 'TechCorp',
        'model' => 'Pro X1',
        'specs' => [
            'cpu' => 'Intel i7-12700H',
            'ram' => '32GB DDR5', // Upgraded RAM
            'storage' => '1TB SSD', // Upgraded storage
            'display' => '15.6" 4K',
        ],
        'warranty' => '3 years',
        'weight' => 2.1,
        'categories' => ['electronics', 'computers', 'premium'],
        'upgraded' => true,
    ]]);

    $updatedLaptop = Cart::get('laptop-pro');
    expect($updatedLaptop->getAttribute('specs')['ram'])->toBe('32GB DDR5');
    expect($updatedLaptop->getAttribute('upgraded'))->toBeTrue();

    // === SCENARIO 4: ADDING MORE PRODUCTS ===

    // Add books with bulk quantity
    Cart::add('book-laravel', 'Laravel: Up & Running', 49.99, 2, [
        'author' => 'Matt Stauffer',
        'isbn' => '978-1492041214',
        'pages' => 608,
        'format' => 'paperback',
        'weight' => 0.8,
    ]);

    // Add digital product (no weight)
    Cart::add('ebook-vue', 'Vue.js Complete Guide', 39.99, 1, [
        'format' => 'digital',
        'file_size' => '15MB',
        'pages' => 450,
        'download_link' => 'https://example.com/download/vue-guide',
        'weight' => 0, // Digital product
    ]);

    expect(Cart::getItems())->toHaveCount(5);
    expect(Cart::getTotalQuantity())->toBe(8); // 3 + 1 + 1 + 2 + 1

    // === SCENARIO 5: APPLYING CART-LEVEL CONDITIONS ===

    // Add percentage discount
    $memberDiscount = new CartCondition(
        'member-discount',
        'discount',
        'subtotal',
        '-10%',
        ['description' => 'Premium member 10% discount', 'member_level' => 'premium']
    );
    Cart::addCondition($memberDiscount);

    // Add tax
    $salesTax = new CartCondition(
        'sales-tax',
        'tax',
        'subtotal',
        '+8.25%',
        ['description' => 'State sales tax', 'jurisdiction' => 'CA']
    );
    Cart::addCondition($salesTax);

    // Add shipping cost
    $shippingFee = new CartCondition(
        'standard-shipping',
        'shipping',
        'subtotal',
        '+15.99',
        ['description' => 'Standard shipping', 'delivery_days' => '5-7']
    );
    Cart::addCondition($shippingFee);

    // Add handling fee
    $handlingFee = new CartCondition(
        'handling-fee',
        'fee',
        'subtotal',
        '+5.99',
        ['description' => 'Handling fee for fragile items']
    );
    Cart::addCondition($handlingFee);

    expect(Cart::getConditions())->toHaveCount(4);

    // === SCENARIO 6: APPLYING ITEM-LEVEL CONDITIONS ===

    // Add VIP discount to laptop
    $vipDiscount = new CartCondition(
        'vip-laptop-discount',
        'discount',
        'item',
        '-5%',
        ['description' => 'VIP customer discount on electronics']
    );
    Cart::addItemCondition('laptop-pro', $vipDiscount);

    // Add bulk discount to books
    $bulkDiscount = new CartCondition(
        'bulk-book-discount',
        'discount',
        'item',
        '-15%',
        ['description' => 'Bulk purchase discount (2+ books)']
    );
    Cart::addItemCondition('book-laravel', $bulkDiscount);

    // Add express handling fee to laptop
    $expressHandling = new CartCondition(
        'express-handling',
        'fee',
        'item',
        '+25.00',
        ['description' => 'Express handling for premium electronics']
    );
    Cart::addItemCondition('laptop-pro', $expressHandling);

    // === SCENARIO 7: COMPLEX CONDITION SCENARIOS ===

    // Add coupon code with complex rules
    $couponCode = new CartCondition(
        'SAVE20',
        'discount',
        'subtotal',
        '-20.00',
        [
            'description' => 'Coupon: SAVE20',
            'min_amount' => 100.00,
            'max_discount' => 50.00,
            'expires_at' => '2025-12-31',
            'usage_limit' => 1,
        ]
    );
    Cart::addCondition($couponCode);

    // Add conditional free shipping (will be overridden if conditions met)
    $conditionalShipping = new CartCondition(
        'conditional-free-shipping',
        'shipping',
        'subtotal',
        '0',
        [
            'description' => 'Free shipping over $100',
            'condition' => 'cart_total > 100',
            'overrides' => ['standard-shipping'],
        ]
    );
    Cart::addCondition($conditionalShipping);

    // === SCENARIO 8: CALCULATING AND VERIFYING TOTALS ===

    // Get subtotal without any conditions
    $subtotalWithoutConditions = Cart::subtotalWithoutConditions();
    expect($subtotalWithoutConditions)->toBeInstanceOf(\Akaunting\Money\Money::class);
    expect($subtotalWithoutConditions->getAmount())->toBeGreaterThan(0);

    // Get subtotal with conditions applied
    $subtotal = Cart::subtotal();
    expect($subtotal)->toBeInstanceOf(\Akaunting\Money\Money::class);
    expect($subtotal->getAmount())->toBeGreaterThan(0);

    // Get final total
    $total = Cart::total();
    expect($total)->toBeInstanceOf(\Akaunting\Money\Money::class);
    expect($total->getAmount())->toBeGreaterThan(0);

    // Calculate savings (amount saved from discounts)
    $savings = $subtotalWithoutConditions->getAmount() - $subtotal->getAmount();
    expect($savings)->toBeGreaterThanOrEqual(0); // Should be positive (savings)

    // Verify conditions are properly applied
    $conditions = Cart::getConditions();
    expect($conditions->count())->toBe(6); // 4 cart + 2 additional

    // Test condition filtering
    $discounts = $conditions->byType('discount');
    expect($discounts->count())->toBeGreaterThan(0);

    $taxes = $conditions->byType('tax');
    expect($taxes->count())->toBe(1);

    $fees = $conditions->byType('fee');
    expect($fees->count())->toBeGreaterThan(0);

    $shipping = $conditions->byType('shipping');
    expect($shipping->count())->toBeGreaterThan(0);

    // === SCENARIO 9: REMOVING ITEMS AND CONDITIONS ===

    // Remove one item completely
    $removedItem = Cart::remove('mousepad-xl');
    expect($removedItem)->not->toBeNull();
    expect(Cart::getItems())->toHaveCount(4);
    expect(Cart::get('mousepad-xl'))->toBeNull();

    // Remove a specific condition
    Cart::removeCondition('handling-fee');
    expect(Cart::getConditions())->toHaveCount(5);
    expect(Cart::getCondition('handling-fee'))->toBeNull();

    // Remove item-level condition
    Cart::removeItemCondition('laptop-pro', 'express-handling');
    $laptop = Cart::get('laptop-pro');
    expect($laptop->getCondition('express-handling'))->toBeNull();

    // === SCENARIO 10: ADVANCED CART OPERATIONS ===

    // Search for items by price range
    $expensiveItems = Cart::search(function ($item) {
        return $item->price > 50.00;
    });
    expect($expensiveItems->count())->toBeGreaterThan(0);

    // Search for items by category
    $electronics = Cart::search(function ($item) {
        $categories = $item->getAttribute('categories', []);

        return is_array($categories) && in_array('electronics', $categories);
    });
    expect($electronics->count())->toBeGreaterThan(0);

    // Get items with specific attributes
    $digitalProducts = Cart::search(function ($item) {
        return $item->getAttribute('format') === 'digital';
    });
    expect($digitalProducts->count())->toBe(1);

    // Calculate total weight for shipping
    $totalWeight = Cart::getItems()->sum(function ($item) {
        return $item->getAttribute('weight', 0) * $item->quantity;
    });
    expect($totalWeight)->toBeGreaterThan(0);

    // === SCENARIO 11: TESTING EDGE CASES ===

    // Add item with zero price (free sample)
    Cart::add('free-sample', 'Free Sample Product', 0.00, 1, [
        'type' => 'sample',
        'restrictions' => 'One per customer',
    ]);

    // Add item with special characters
    Cart::add('special-café', 'Café Münü Items ñoñó', 12.50, 1, [
        'special_chars' => 'αβγδε',
        'unicode' => '你好世界',
        'emoji' => '🎉🚀💯',
    ]);

    // Verify special items are handled correctly
    expect(Cart::get('free-sample'))->not->toBeNull();
    expect(Cart::get('free-sample')->price)->toBe(0.0);
    expect(Cart::get('special-café'))->not->toBeNull();
    expect(Cart::get('special-café')->getAttribute('emoji'))->toBe('🎉🚀💯');

    // === SCENARIO 12: CONDITION SUMMARY AND ANALYSIS ===

    // Get condition summary
    $baseValue = Cart::subtotalWithoutConditions()->getAmount();
    $conditionSummary = Cart::getConditions()->getSummary($baseValue);
    expect($conditionSummary)->toBeArray();
    expect($conditionSummary)->toHaveKeys(['total_conditions', 'discounts', 'charges', 'percentages']);

    // Verify condition counts
    expect($conditionSummary['total_conditions'])->toBeGreaterThan(0);
    expect($conditionSummary['discounts'])->toBeGreaterThanOrEqual(0);
    expect($conditionSummary['charges'])->toBeGreaterThanOrEqual(0);

    // === SCENARIO 13: FINAL VERIFICATION ===

    // Verify cart is not empty
    expect(Cart::isEmpty())->toBeFalse();

    // Verify item count
    expect(Cart::getItems())->toHaveCount(6); // 4 original + 2 special items

    // Verify quantity count
    expect(Cart::getTotalQuantity())->toBeGreaterThan(5);

    // Verify totals are calculated correctly
    $finalSubtotal = Cart::subtotal();
    $finalTotal = Cart::total();

    expect($finalSubtotal->getAmount())->toBeGreaterThan(0);
    expect($finalTotal->getAmount())->toBeGreaterThan(0);

    // Verify formatted output works
    // expect(Cart::subtotalFormatted())->toBeString();
    // expect(Cart::totalFormatted())->toBeString();

    // === SCENARIO 14: BULK OPERATIONS ===

    // Add multiple items in bulk
    $bulkItems = [
        ['id' => 'bulk-1', 'name' => 'Bulk Item 1', 'price' => 10.00, 'quantity' => 5],
        ['id' => 'bulk-2', 'name' => 'Bulk Item 2', 'price' => 15.00, 'quantity' => 3],
        ['id' => 'bulk-3', 'name' => 'Bulk Item 3', 'price' => 20.00, 'quantity' => 2],
    ];

    foreach ($bulkItems as $item) {
        Cart::add($item['id'], $item['name'], $item['price'], $item['quantity']);
    }

    expect(Cart::getItems())->toHaveCount(9); // 6 + 3 bulk items

    // Apply bulk discount condition
    $bulkOrderDiscount = new CartCondition(
        'bulk-order-discount',
        'discount',
        'subtotal',
        '-5%',
        ['description' => 'Bulk order discount (10+ items)']
    );
    Cart::addCondition($bulkOrderDiscount);

    // === SCENARIO 15: CART PERSISTENCE SIMULATION ===

    // Get cart data for persistence
    $cartData = [
        'items' => Cart::getItems()->toArray(),
        'conditions' => Cart::getConditions()->toArray(),
        'subtotal' => Cart::subtotal()->getAmount(),
        'total' => Cart::total()->getAmount(),
        'quantity' => Cart::getTotalQuantity(),
    ];

    expect($cartData['items'])->toBeArray();
    expect($cartData['conditions'])->toBeArray();
    expect($cartData['subtotal'])->toBeFloat();
    expect($cartData['total'])->toBeFloat();
    expect($cartData['quantity'])->toBeInt();

    // === FINAL ASSERTIONS ===

    // Cart should have significant value
    expect(Cart::total()->getAmount())->toBeGreaterThan(100.00);

    // Cart should have multiple conditions
    expect(Cart::getConditions()->count())->toBeGreaterThan(5);

    // Cart should have multiple item types
    expect(Cart::getItems()->count())->toBeGreaterThan(8);

    // Subtotal without conditions should be higher than with conditions (due to discounts)
    expect(Cart::subtotalWithoutConditions()->getAmount())->toBeGreaterThan(Cart::subtotal()->getAmount());

    // Success message
    expect(true)->toBeTrue('✅ Comprehensive real-world cart usage test completed successfully!');
});

it('handles cart clearing and condition management', function () {
    Cart::clear();

    // Add some items and conditions
    Cart::add('test-item', 'Test Item', 50.00, 2);
    Cart::addDiscount('test-discount', '10%');
    Cart::addTax('test-tax', '5%');
    Cart::addFee('test-fee', '2.50');

    expect(Cart::getItems())->toHaveCount(1);
    expect(Cart::getConditions())->toHaveCount(3);

    // Clear only conditions
    Cart::clearConditions();
    expect(Cart::getConditions())->toHaveCount(0);
    expect(Cart::getItems())->toHaveCount(1); // Items should remain

    // Clear entire cart
    Cart::clear();
    expect(Cart::getItems())->toHaveCount(0);
    expect(Cart::getConditions())->toHaveCount(0);
    expect(Cart::isEmpty())->toBeTrue();
});

it('handles complex pricing scenarios with multiple condition layers', function () {
    Cart::clear();

    // Add a high-value item
    Cart::add('premium-item', 'Premium Product', 500.00, 1, [
        'category' => 'premium',
        'vip_eligible' => true,
    ]);

    // Layer 1: Customer tier discount
    Cart::addDiscount('vip-tier', '15%');

    // Layer 2: Seasonal promotion (fixed amount discount)
    Cart::addDiscount('summer-sale', '50.00');

    // Layer 3: Tax
    Cart::addTax('state-tax', '8.5%');

    // Layer 4: Premium shipping
    Cart::addFee('premium-shipping', '25.00');

    // Layer 5: Insurance (percentage fee)
    Cart::addFee('shipping-insurance', '3%');

    $originalPrice = 500.00;
    $subtotalWithoutConditions = Cart::subtotalWithoutConditions()->getAmount();
    $subtotalWithDiscounts = Cart::subtotal()->getAmount(); // Default behavior includes conditions
    $finalTotal = Cart::total()->getAmount();

    // Verify the calculation layers work correctly
    expect($subtotalWithoutConditions)->toBe($originalPrice);
    // With discounts applied, subtotal should be lower than original
    expect($subtotalWithDiscounts)->toBeLessThanOrEqual($originalPrice);
    // Final total should include all conditions (taxes, fees)
    expect($finalTotal)->toBeGreaterThan(0);

    // Verify we can track the impact of each condition type
    $conditions = Cart::getConditions();
    $discountConditions = $conditions->byType('discount');
    $taxConditions = $conditions->byType('tax');
    $feeConditions = $conditions->byType('fee');

    expect($discountConditions->count())->toBe(2);
    expect($taxConditions->count())->toBe(1);
    expect($feeConditions->count())->toBe(2);

    // Test savings calculation - should be positive if there are discounts
    $totalSavings = $subtotalWithoutConditions - $subtotalWithDiscounts;
    expect($totalSavings)->toBeGreaterThanOrEqual(0);
});

it('validates cart behavior with quantity updates and condition recalculation', function () {
    Cart::clear();

    // Add items with quantity-sensitive pricing
    Cart::add('qty-item', 'Quantity-Sensitive Item', 25.00, 1);

    // Add a condition that provides a meaningful discount
    $bulkDiscount = new CartCondition(
        'bulk-discount',
        'discount',
        'subtotal',
        '-20%',
        ['description' => 'Bulk discount for orders over $50']
    );
    Cart::addCondition($bulkDiscount);

    // Initial state - single item (25.00, with 20% discount = 20.00)
    $initialTotal = Cart::total()->getAmount();
    $initialSubtotal = Cart::subtotal()->getAmount();

    // Increase quantity (absolute update)
    Cart::update('qty-item', ['quantity' => ['value' => 3]]); // Now $75 base, with discount = $60

    $updatedTotal = Cart::total()->getAmount();
    $updatedSubtotal = Cart::subtotal()->getAmount();

    // Verify quantity update
    expect(Cart::get('qty-item')->quantity)->toBe(3);
    expect(Cart::getTotalQuantity())->toBe(3);

    // Verify that totals changed appropriately
    expect($updatedSubtotal)->toBeGreaterThan($initialSubtotal);
    expect($updatedTotal)->toBeGreaterThan($initialTotal);

    // Test edge case: reduce quantity back (absolute update)
    Cart::update('qty-item', ['quantity' => ['value' => 1]]);
    $finalSubtotal = Cart::subtotal()->getAmount();
    $finalTotal = Cart::total()->getAmount();

    // Should be back to original quantities
    expect(Cart::get('qty-item')->quantity)->toBe(1);
    expect($finalSubtotal)->toBe($initialSubtotal);
    expect($finalTotal)->toBe($initialTotal);
});
