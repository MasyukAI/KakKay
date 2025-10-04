<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;
use MasyukAI\FilamentCart\Models\Condition;

afterEach(function () {
    Cart::clear();
});

it('applies global conditions to new carts', function () {
    // Create a global condition
    $globalCondition = Condition::create([
        'name' => 'global-tax',
        'display_name' => 'Global Tax',
        'type' => 'tax',
        'target' => 'total',
        'value' => '10%',
        'is_global' => true,
        'is_active' => true,
        'rules' => [],
        'order' => 0,
        'attributes' => [],
    ]);

    // Create a new cart
    Cart::setInstance('test');
    Cart::add('1', 'Test Product', 100, 1);

    // Assert the global condition was applied
    expect(Cart::getConditions()->has('global-tax'))->toBeTrue();
});

it('does not apply inactive global conditions', function () {
    // Create an inactive global condition
    Condition::create([
        'name' => 'inactive-tax',
        'display_name' => 'Inactive Tax',
        'type' => 'tax',
        'target' => 'total',
        'value' => '5%',
        'is_global' => true,
        'is_active' => false,
        'rules' => [],
        'order' => 0,
        'attributes' => [],
    ]);

    // Create a new cart
    Cart::setInstance('test');
    Cart::add('1', 'Test Product', 100, 1);

    // Verify the condition was NOT applied
    expect(Cart::getConditions()->has('inactive-tax'))->toBeFalse();
});

it('does not apply non-global conditions automatically', function () {
    // Create a regular (non-global) condition
    Condition::create([
        'name' => 'regular-discount',
        'display_name' => 'Regular Discount',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-10',
        'is_global' => false,
        'is_active' => true,
        'rules' => [],
        'order' => 0,
        'attributes' => [],
    ]);

    // Create a new cart
    Cart::setInstance('test');
    Cart::add('1', 'Test Product', 100, 1);

    // Assert the regular condition was not automatically applied
    expect(Cart::getConditions()->has('regular-discount'))->toBeFalse();
});

it('applies global conditions when items are added', function () {
    // Create a global condition
    Condition::create([
        'name' => 'shipping-fee',
        'display_name' => 'Shipping Fee',
        'type' => 'shipping',
        'target' => 'total',
        'value' => '+15',
        'is_global' => true,
        'is_active' => true,
        'rules' => [],
        'order' => 0,
        'attributes' => [],
    ]);

    // Create a cart and add items
    Cart::setInstance('test');
    Cart::add('1', 'Product 1', 50, 1);

    expect(Cart::getConditions()->has('shipping-fee'))->toBeTrue();

    // Add another item
    Cart::add('2', 'Product 2', 75, 1);

    // Condition should still be present (and not duplicated)
    expect(Cart::getConditions()->has('shipping-fee'))->toBeTrue();
});

it('evaluates dynamic rules before applying global conditions', function () {
    // Create a global condition with a rule (min 2 items)
    Condition::create([
        'name' => 'bulk-discount',
        'display_name' => 'Bulk Discount',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-20%',
        'is_global' => true,
        'is_active' => true,
        'rules' => ['min_items' => '2'],
        'order' => 0,
        'attributes' => [],
    ]);

    // Create a cart with 1 item
    Cart::setInstance('test');
    Cart::add('1', 'Product 1', 100, 1);

    // Condition should not be applied (rule not met)
    expect(Cart::getConditions()->has('bulk-discount'))->toBeFalse();

    // Add another item
    Cart::add('2', 'Product 2', 100, 1);

    // Now condition should be applied (rule met)
    expect(Cart::getConditions()->has('bulk-discount'))->toBeTrue();
});

it('respects the enable_global_conditions config', function () {
    // Disable global conditions
    config(['filament-cart.enable_global_conditions' => false]);

    // Create a global condition
    Condition::create([
        'name' => 'disabled-tax',
        'display_name' => 'Disabled Tax',
        'type' => 'tax',
        'target' => 'total',
        'value' => '10%',
        'is_global' => true,
        'is_active' => true,
        'rules' => [],
        'order' => 0,
        'attributes' => [],
    ]);

    // Create a new cart
    Cart::setInstance('test');
    Cart::add('1', 'Test Product', 100, 1);

    // Assert the global condition was not applied
    expect(Cart::getConditions()->has('disabled-tax'))->toBeFalse();
});
