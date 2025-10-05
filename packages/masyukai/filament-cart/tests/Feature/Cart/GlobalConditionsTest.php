<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart as CartFacade;
use MasyukAI\FilamentCart\Models\CartCondition as SnapshotCondition;
use MasyukAI\FilamentCart\Models\Condition;

beforeEach(fn () => CartFacade::clear());

afterEach(fn () => CartFacade::clear());

it('applies active global conditions to new carts', function (): void {
    Condition::factory()->create([
        'name' => 'global-tax',
        'display_name' => 'Global Tax',
        'type' => 'tax',
        'target' => 'total',
        'value' => '10%',
        'is_global' => true,
        'is_active' => true,
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 1);

    expect(CartFacade::getConditions()->has('global-tax'))->toBeTrue();

    $snapshot = SnapshotCondition::first();
    expect($snapshot)->not->toBeNull();
    expect($snapshot->is_global)->toBeTrue();
});

it('ignores inactive global conditions', function (): void {
    Condition::factory()->create([
        'name' => 'inactive-fee',
        'type' => 'fee',
        'target' => 'total',
        'value' => '+500',
        'is_global' => true,
        'is_active' => false,
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 1);

    expect(CartFacade::getConditions()->has('inactive-fee'))->toBeFalse();
});

it('ignores non-global conditions', function (): void {
    Condition::factory()->create([
        'name' => 'local-discount',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-10%',
        'is_global' => false,
        'is_active' => true,
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 1);

    expect(CartFacade::getConditions()->has('local-discount'))->toBeFalse();
});

it('re-evaluates rules whenever items are added', function (): void {
    Condition::factory()->create([
        'name' => 'bulk-discount',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-20%',
        'is_global' => true,
        'is_active' => true,
        'rules' => ['min_items' => '2'],
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 1);
    expect(CartFacade::getConditions()->has('bulk-discount'))->toBeFalse();

    CartFacade::add('sku-002', 'Product', 1000, 1);
    expect(CartFacade::getConditions()->has('bulk-discount'))->toBeTrue();
});

it('respects the enable_global_conditions flag', function (): void {
    config(['filament-cart.enable_global_conditions' => false]);

    Condition::factory()->create([
        'name' => 'disabled-tax',
        'type' => 'tax',
        'target' => 'total',
        'value' => '10%',
        'is_global' => true,
        'is_active' => true,
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 1);

    expect(CartFacade::getConditions()->has('disabled-tax'))->toBeFalse();

    config(['filament-cart.enable_global_conditions' => true]);
});

// Multiple Conditions Tests
it('applies multiple global conditions together', function (): void {
    Condition::factory()->create([
        'name' => 'global-tax',
        'type' => 'tax',
        'target' => 'total',
        'value' => '10%',
        'is_global' => true,
        'is_active' => true,
    ]);

    Condition::factory()->create([
        'name' => 'processing-fee',
        'type' => 'fee',
        'target' => 'total',
        'value' => '+200',
        'is_global' => true,
        'is_active' => true,
    ]);

    Condition::factory()->create([
        'name' => 'loyalty-discount',
        'type' => 'discount',
        'target' => 'subtotal',
        'value' => '-5%',
        'is_global' => true,
        'is_active' => true,
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 1);

    expect(CartFacade::getConditions()->has('global-tax'))->toBeTrue();
    expect(CartFacade::getConditions()->has('processing-fee'))->toBeTrue();
    expect(CartFacade::getConditions()->has('loyalty-discount'))->toBeTrue();
    expect(CartFacade::getConditions()->count())->toBe(3);
});

it('handles conditions with different targets correctly', function (): void {
    Condition::factory()->create([
        'name' => 'subtotal-discount',
        'type' => 'discount',
        'target' => 'subtotal',
        'value' => '-10%',
        'is_global' => true,
        'is_active' => true,
    ]);

    Condition::factory()->create([
        'name' => 'total-tax',
        'type' => 'tax',
        'target' => 'total',
        'value' => '8%',
        'is_global' => true,
        'is_active' => true,
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 2);

    $subtotalCondition = CartFacade::getConditions()->get('subtotal-discount');
    $totalCondition = CartFacade::getConditions()->get('total-tax');

    expect($subtotalCondition->getTarget())->toBe('subtotal');
    expect($totalCondition->getTarget())->toBe('total');
});

// Dynamic Condition Tests - Item Updates
it('prevents conditions from being applied when rules do not match', function (): void {
    Condition::factory()->create([
        'name' => 'three-item-discount',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-15%',
        'is_global' => true,
        'is_active' => true,
        'rules' => ['min_items' => '3'],
    ]);

    // Add 2 distinct items - rule requires 3
    CartFacade::add('sku-001', 'Product', 1000, 1);
    CartFacade::add('sku-002', 'Product', 1000, 1);
    expect(CartFacade::getConditions()->has('three-item-discount'))->toBeFalse();

    // Add 3rd item - now rule matches
    CartFacade::add('sku-003', 'Product', 1000, 1);
    expect(CartFacade::getConditions()->has('three-item-discount'))->toBeTrue();
});

it('applies conditions when item count reaches threshold via adding items', function (): void {
    Condition::factory()->create([
        'name' => 'bulk-discount',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-20%',
        'is_global' => true,
        'is_active' => true,
        'rules' => ['min_items' => '4'],
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 1);
    CartFacade::add('sku-002', 'Product', 1000, 1);
    CartFacade::add('sku-003', 'Product', 1000, 1);
    expect(CartFacade::getConditions()->has('bulk-discount'))->toBeFalse();

    CartFacade::add('sku-004', 'Product', 1000, 1);
    expect(CartFacade::getConditions()->has('bulk-discount'))->toBeTrue();
});

// Dynamic Condition Tests - Total-based Rules
it('applies conditions based on minimum total', function (): void {
    Condition::factory()->create([
        'name' => 'free-shipping',
        'type' => 'shipping',
        'target' => 'total',
        'value' => '-1000',
        'is_global' => true,
        'is_active' => true,
        'rules' => ['min_total' => '5000'],
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 3);
    expect(CartFacade::getConditions()->has('free-shipping'))->toBeFalse();

    CartFacade::add('sku-002', 'Product', 1000, 2);
    expect(CartFacade::getConditions()->has('free-shipping'))->toBeTrue();
});

it('prevents conditions from applying when total below minimum threshold', function (): void {
    Condition::factory()->create([
        'name' => 'premium-discount',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-25%',
        'is_global' => true,
        'is_active' => true,
        'rules' => ['min_total' => '10000'],
    ]);

    CartFacade::add('sku-001', 'Product', 2000, 3);
    expect(CartFacade::getConditions()->has('premium-discount'))->toBeFalse();

    CartFacade::add('sku-002', 'Product', 2000, 2);
    expect(CartFacade::getConditions()->has('premium-discount'))->toBeTrue();
});

it('prevents conditions when total exceeds maximum', function (): void {
    Condition::factory()->create([
        'name' => 'small-order-fee',
        'type' => 'fee',
        'target' => 'total',
        'value' => '+500',
        'is_global' => true,
        'is_active' => true,
        'rules' => ['max_total' => '3000'],
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 2);
    expect(CartFacade::getConditions()->has('small-order-fee'))->toBeTrue();

    CartFacade::clear();
    CartFacade::add('sku-001', 'Product', 1000, 4);
    expect(CartFacade::getConditions()->has('small-order-fee'))->toBeFalse();
});

it('handles total range conditions correctly', function (): void {
    Condition::factory()->create([
        'name' => 'mid-tier-bonus',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-10%',
        'is_global' => true,
        'is_active' => true,
        'rules' => [
            'min_total' => '3000',
            'max_total' => '8000',
        ],
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 2);
    expect(CartFacade::getConditions()->has('mid-tier-bonus'))->toBeFalse();

    CartFacade::clear();
    CartFacade::add('sku-001', 'Product', 1000, 5);
    expect(CartFacade::getConditions()->has('mid-tier-bonus'))->toBeTrue();

    CartFacade::clear();
    CartFacade::add('sku-001', 'Product', 1000, 10);
    expect(CartFacade::getConditions()->has('mid-tier-bonus'))->toBeFalse();
});

// Dynamic Condition Tests - Multiple Rules
it('requires all rules to match for condition to apply', function (): void {
    Condition::factory()->create([
        'name' => 'exclusive-deal',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-30%',
        'is_global' => true,
        'is_active' => true,
        'rules' => [
            'min_items' => '3',
            'min_total' => '5000',
        ],
    ]);

    CartFacade::add('sku-001', 'Product', 600, 4);
    expect(CartFacade::getConditions()->has('exclusive-deal'))->toBeFalse();

    CartFacade::add('sku-002', 'Product', 600, 4);
    expect(CartFacade::getConditions()->has('exclusive-deal'))->toBeFalse();

    CartFacade::add('sku-003', 'Product', 600, 4);
    expect(CartFacade::getConditions()->has('exclusive-deal'))->toBeTrue();
});

it('does not apply condition when one of multiple rules fails', function (): void {
    Condition::factory()->create([
        'name' => 'combo-discount',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-20%',
        'is_global' => true,
        'is_active' => true,
        'rules' => [
            'min_items' => '2',
            'min_total' => '5000',
        ],
    ]);

    CartFacade::add('sku-001', 'Product', 2000, 1);
    CartFacade::add('sku-002', 'Product', 2000, 1);
    expect(CartFacade::getConditions()->has('combo-discount'))->toBeFalse();

    CartFacade::add('sku-003', 'Product', 2000, 1);
    expect(CartFacade::getConditions()->has('combo-discount'))->toBeTrue();
});

// Dynamic Condition Tests - Specific Items
it('applies conditions when specific items are in cart', function (): void {
    Condition::factory()->create([
        'name' => 'featured-product-bonus',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-15%',
        'is_global' => true,
        'is_active' => true,
        'rules' => ['specific_items' => ['premium-001', 'premium-002']],
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 1);
    expect(CartFacade::getConditions()->has('featured-product-bonus'))->toBeFalse();

    CartFacade::add('premium-001', 'Premium Product', 2000, 1, ['sku' => 'premium-001']);
    expect(CartFacade::getConditions()->has('featured-product-bonus'))->toBeTrue();
});

it('does not apply condition without specific required items', function (): void {
    Condition::factory()->create([
        'name' => 'bundle-discount',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-25%',
        'is_global' => true,
        'is_active' => true,
        'rules' => ['specific_items' => ['bundle-a', 'bundle-b']],
    ]);

    CartFacade::add('other-001', 'Other Product', 1000, 1);
    CartFacade::add('other-002', 'Another Product', 1000, 1);
    expect(CartFacade::getConditions()->has('bundle-discount'))->toBeFalse();

    CartFacade::add('bundle-a', 'Bundle A', 1000, 1, ['sku' => 'bundle-a']);
    expect(CartFacade::getConditions()->has('bundle-discount'))->toBeTrue();
});

// Dynamic Condition Tests - Item Categories
it('applies conditions based on product categories', function (): void {
    Condition::factory()->create([
        'name' => 'electronics-tax',
        'type' => 'tax',
        'target' => 'total',
        'value' => '5%',
        'is_global' => true,
        'is_active' => true,
        'rules' => ['has_category' => 'electronics'],
    ]);

    CartFacade::add('sku-001', 'Book', 500, 1, ['category' => 'books']);
    expect(CartFacade::getConditions()->has('electronics-tax'))->toBeFalse();

    CartFacade::add('sku-002', 'Laptop', 2000, 1, ['category' => 'electronics']);
    expect(CartFacade::getConditions()->has('electronics-tax'))->toBeTrue();
});

it('does not apply category condition when category not present', function (): void {
    Condition::factory()->create([
        'name' => 'clothing-discount',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-10%',
        'is_global' => true,
        'is_active' => true,
        'rules' => ['has_category' => 'clothing'],
    ]);

    CartFacade::add('book-001', 'Book', 300, 1, ['category' => 'books']);
    CartFacade::add('laptop-001', 'Laptop', 2000, 1, ['category' => 'electronics']);
    expect(CartFacade::getConditions()->has('clothing-discount'))->toBeFalse();

    CartFacade::add('shirt-001', 'T-Shirt', 500, 1, ['category' => 'clothing']);
    expect(CartFacade::getConditions()->has('clothing-discount'))->toBeTrue();
});

// Dynamic Condition Tests - Mixed Scenarios
it('applies appropriate tier discount based on cart total', function (): void {
    Condition::factory()->create([
        'name' => 'tier-1-discount',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-5%',
        'is_global' => true,
        'is_active' => true,
        'rules' => [
            'min_total' => '2000',
            'max_total' => '4999',
        ],
    ]);

    Condition::factory()->create([
        'name' => 'tier-2-discount',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-10%',
        'is_global' => true,
        'is_active' => true,
        'rules' => [
            'min_total' => '5000',
            'max_total' => '9999',
        ],
    ]);

    Condition::factory()->create([
        'name' => 'tier-3-discount',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-15%',
        'is_global' => true,
        'is_active' => true,
        'rules' => ['min_total' => '10000'],
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 1);
    expect(CartFacade::getConditions()->has('tier-1-discount'))->toBeFalse();
    expect(CartFacade::getConditions()->has('tier-2-discount'))->toBeFalse();
    expect(CartFacade::getConditions()->has('tier-3-discount'))->toBeFalse();

    CartFacade::clear();
    CartFacade::add('sku-001', 'Product', 1000, 3);
    expect(CartFacade::getConditions()->has('tier-1-discount'))->toBeTrue();
    expect(CartFacade::getConditions()->has('tier-2-discount'))->toBeFalse();

    CartFacade::clear();
    CartFacade::add('sku-001', 'Product', 1000, 7);
    expect(CartFacade::getConditions()->has('tier-1-discount'))->toBeFalse();
    expect(CartFacade::getConditions()->has('tier-2-discount'))->toBeTrue();

    CartFacade::clear();
    CartFacade::add('sku-001', 'Product', 1000, 12);
    expect(CartFacade::getConditions()->has('tier-2-discount'))->toBeFalse();
    expect(CartFacade::getConditions()->has('tier-3-discount'))->toBeTrue();
});

it('applies correct condition based on total threshold', function (): void {
    Condition::factory()->create([
        'name' => 'new-customer-discount',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-20%',
        'is_global' => true,
        'is_active' => true,
        'rules' => ['max_total' => '5000'],
    ]);

    Condition::factory()->create([
        'name' => 'vip-discount',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-30%',
        'is_global' => true,
        'is_active' => true,
        'rules' => ['min_total' => '5001'],
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 4);
    expect(CartFacade::getConditions()->has('new-customer-discount'))->toBeTrue();
    expect(CartFacade::getConditions()->has('vip-discount'))->toBeFalse();

    CartFacade::clear();
    CartFacade::add('sku-001', 'Product', 1000, 6);
    expect(CartFacade::getConditions()->has('new-customer-discount'))->toBeFalse();
    expect(CartFacade::getConditions()->has('vip-discount'))->toBeTrue();
});

it('handles complex multi-rule scenarios', function (): void {
    Condition::factory()->create([
        'name' => 'premium-bundle-deal',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-35%',
        'is_global' => true,
        'is_active' => true,
        'rules' => [
            'min_items' => '3',
            'min_total' => '8000',
            'max_total' => '15000',
            'has_category' => 'premium',
        ],
    ]);

    CartFacade::add('sku-001', 'Regular Product', 1000, 2, ['category' => 'regular']);
    expect(CartFacade::getConditions()->has('premium-bundle-deal'))->toBeFalse();

    CartFacade::add('premium-001', 'Premium A', 3000, 1, ['category' => 'premium']);
    expect(CartFacade::getConditions()->has('premium-bundle-deal'))->toBeFalse();

    CartFacade::add('premium-002', 'Premium B', 3000, 1, ['category' => 'premium']);
    expect(CartFacade::getConditions()->has('premium-bundle-deal'))->toBeTrue();

    CartFacade::clear();
    CartFacade::add('premium-001', 'Premium A', 6000, 4, ['category' => 'premium']);
    expect(CartFacade::getConditions()->has('premium-bundle-deal'))->toBeFalse();
});

// Item-Level Condition Tests
it('applies item-level conditions based on item quantity threshold', function (): void {
    Condition::factory()->create([
        'name' => 'bulk-item-discount',
        'type' => 'discount',
        'target' => 'item',
        'value' => '-10%',
        'is_global' => true,
        'is_active' => true,
        'rules' => ['item_quantity' => '5'],
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 3);
    $item = CartFacade::getItems()->first();
    expect($item->conditions->has('bulk-item-discount'))->toBeFalse();

    CartFacade::clear();
    CartFacade::add('sku-001', 'Product', 1000, 5);
    $item = CartFacade::getItems()->first();
    expect($item->conditions->has('bulk-item-discount'))->toBeTrue();
});

it('applies item-level conditions based on item price threshold', function (): void {
    Condition::factory()->create([
        'name' => 'premium-item-fee',
        'type' => 'fee',
        'target' => 'item',
        'value' => '+100',
        'is_global' => true,
        'is_active' => true,
        'rules' => ['item_price' => '5000'],
    ]);

    CartFacade::add('sku-001', 'Budget Item', 1000, 1);
    $item = CartFacade::getItems()->first();
    expect($item->conditions->has('premium-item-fee'))->toBeFalse();

    CartFacade::clear();
    CartFacade::add('sku-002', 'Premium Item', 6000, 1);
    $item = CartFacade::getItems()->first();
    expect($item->conditions->has('premium-item-fee'))->toBeTrue();
});

// Edge Cases
it('handles empty cart gracefully', function (): void {
    Condition::factory()->create([
        'name' => 'always-active',
        'type' => 'fee',
        'target' => 'total',
        'value' => '+100',
        'is_global' => true,
        'is_active' => true,
    ]);

    expect(CartFacade::getConditions()->isEmpty())->toBeTrue();
});

it('handles clearing cart with active conditions', function (): void {
    Condition::factory()->create([
        'name' => 'auto-tax',
        'type' => 'tax',
        'target' => 'total',
        'value' => '10%',
        'is_global' => true,
        'is_active' => true,
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 1);
    expect(CartFacade::getConditions()->has('auto-tax'))->toBeTrue();

    CartFacade::clear();
    expect(CartFacade::getConditions()->isEmpty())->toBeTrue();

    CartFacade::add('sku-002', 'Product', 1000, 1);
    expect(CartFacade::getConditions()->has('auto-tax'))->toBeTrue();
});

it('reapplies conditions after cart is cleared and items added again', function (): void {
    Condition::factory()->create([
        'name' => 'bulk-order',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-15%',
        'is_global' => true,
        'is_active' => true,
        'rules' => ['min_items' => '3'],
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 1);
    CartFacade::add('sku-002', 'Product', 1000, 1);
    CartFacade::add('sku-003', 'Product', 1000, 1);
    expect(CartFacade::getConditions()->has('bulk-order'))->toBeTrue();

    CartFacade::clear();
    expect(CartFacade::getConditions()->isEmpty())->toBeTrue();

    CartFacade::add('sku-001', 'Product', 1000, 1);
    CartFacade::add('sku-002', 'Product', 1000, 1);
    CartFacade::add('sku-003', 'Product', 1000, 1);
    expect(CartFacade::getConditions()->has('bulk-order'))->toBeTrue();
});

// Condition Activation/Deactivation
it('does not apply conditions that become deactivated between cart operations', function (): void {
    $condition = Condition::factory()->create([
        'name' => 'seasonal-promo',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-20%',
        'is_global' => true,
        'is_active' => true,
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 1);
    expect(CartFacade::getConditions()->has('seasonal-promo'))->toBeTrue();

    $condition->update(['is_active' => false]);
    CartFacade::clear();
    CartFacade::add('sku-002', 'Product', 1000, 1);
    expect(CartFacade::getConditions()->has('seasonal-promo'))->toBeFalse();
});

// Snapshot Verification
it('creates condition snapshots with correct global flag', function (): void {
    Condition::factory()->create([
        'name' => 'snapshot-test',
        'type' => 'tax',
        'target' => 'total',
        'value' => '8%',
        'is_global' => true,
        'is_active' => true,
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 1);

    $snapshot = SnapshotCondition::where('name', 'snapshot-test')->first();
    expect($snapshot)->not->toBeNull();
    expect($snapshot->is_global)->toBeTrue();
    expect($snapshot->type)->toBe('tax');
    expect($snapshot->target)->toBe('total');
    expect($snapshot->value)->toBe('8%');
});

it('creates and removes snapshots when conditions are dynamically evaluated', function (): void {
    Condition::factory()->create([
        'name' => 'dynamic-fee',
        'type' => 'fee',
        'target' => 'total',
        'value' => '+500',
        'is_global' => true,
        'is_active' => true,
        'rules' => ['min_items' => '2'],
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 1);
    expect(SnapshotCondition::where('name', 'dynamic-fee')->exists())->toBeFalse();

    CartFacade::add('sku-002', 'Product', 1000, 1);
    expect(SnapshotCondition::where('name', 'dynamic-fee')->exists())->toBeTrue();

    CartFacade::clear();
    expect(SnapshotCondition::where('name', 'dynamic-fee')->exists())->toBeFalse();
});

// Edge Cases and Stress Tests
it('applies conditions correctly during sequential cart builds', function (): void {
    Condition::factory()->create([
        'name' => 'flash-deal',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-40%',
        'is_global' => true,
        'is_active' => true,
        'rules' => [
            'min_items' => '5',
            'min_total' => '3000',
        ],
    ]);

    for ($i = 1; $i <= 10; $i++) {
        CartFacade::add("sku-{$i}", "Product {$i}", 500, 1);

        if ($i < 5) {
            expect(CartFacade::getConditions()->has('flash-deal'))->toBeFalse();
        } elseif ($i >= 6) {
            expect(CartFacade::getConditions()->has('flash-deal'))->toBeTrue();
        }
    }
});

it('correctly handles overlapping condition rules', function (): void {
    Condition::factory()->create([
        'name' => 'condition-a',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-5%',
        'is_global' => true,
        'is_active' => true,
        'rules' => ['min_total' => '2000'],
    ]);

    Condition::factory()->create([
        'name' => 'condition-b',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-10%',
        'is_global' => true,
        'is_active' => true,
        'rules' => ['min_total' => '2000', 'min_items' => '3'],
    ]);

    // Add 2 items with $30 total - only condition-a applies (min_total: $20)
    CartFacade::add('sku-001', 'Product', 1000, 1);
    CartFacade::add('sku-002', 'Product', 1000, 1);
    expect(CartFacade::getConditions()->has('condition-a'))->toBeTrue();
    expect(CartFacade::getConditions()->has('condition-b'))->toBeFalse();

    // Add 3rd item - now both conditions apply (min_total: $20, min_items: 3)
    CartFacade::add('sku-003', 'Product', 1000, 1);
    expect(CartFacade::getConditions()->has('condition-a'))->toBeTrue();
    expect(CartFacade::getConditions()->has('condition-b'))->toBeTrue();
});

it('respects max rules for upper boundary conditions', function (): void {
    Condition::factory()->create([
        'name' => 'small-cart-bonus',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-15%',
        'is_global' => true,
        'is_active' => true,
        'rules' => [
            'max_items' => '2',
            'max_total' => '3000',
        ],
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 1);
    expect(CartFacade::getConditions()->has('small-cart-bonus'))->toBeTrue();

    CartFacade::add('sku-002', 'Product', 1000, 1);
    expect(CartFacade::getConditions()->has('small-cart-bonus'))->toBeTrue();

    CartFacade::clear();
    CartFacade::add('sku-001', 'Product', 1000, 1);
    CartFacade::add('sku-002', 'Product', 1000, 1);
    CartFacade::add('sku-003', 'Product', 1000, 1);
    expect(CartFacade::getConditions()->has('small-cart-bonus'))->toBeFalse();
});

it('handles exact boundary values for min and max rules', function (): void {
    Condition::factory()->create([
        'name' => 'exact-range-deal',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-12%',
        'is_global' => true,
        'is_active' => true,
        'rules' => [
            'min_total' => '5000',
            'max_total' => '5000',
        ],
    ]);

    CartFacade::add('sku-001', 'Product', 1000, 4);
    expect(CartFacade::getConditions()->has('exact-range-deal'))->toBeFalse();

    CartFacade::clear();
    CartFacade::add('sku-001', 'Product', 1000, 5);
    expect(CartFacade::getConditions()->has('exact-range-deal'))->toBeTrue();

    CartFacade::clear();
    CartFacade::add('sku-001', 'Product', 1000, 6);
    expect(CartFacade::getConditions()->has('exact-range-deal'))->toBeFalse();
});
