<?php

declare(strict_types=1);

use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Facades\Cart;

test('conditions with target subtotal are applied to subtotal', function () {
    session()->flush();
    Cart::setInstance('test')->clear();

    // Add an item: $100
    Cart::add('1', 'Product A', 100, 1);

    // Add a subtotal-targeted condition: -10%
    $discount = new CartCondition(
        name: 'subtotal-discount',
        type: 'discount',
        target: 'subtotal',
        value: '-10%'
    );
    Cart::addCondition($discount);

    // Subtotal should be: 100 - 10% = 90
    expect(Cart::getRawSubtotal())->toBe(90.0);
});

test('conditions with target total are applied after subtotal conditions', function () {
    session()->flush();
    Cart::setInstance('test2')->clear();

    // Add an item: $100
    Cart::add('1', 'Product A', 100, 1);

    // Add a subtotal-targeted condition: -$10
    $subtotalDiscount = new CartCondition(
        name: 'subtotal-discount',
        type: 'discount',
        target: 'subtotal',
        value: '-10'
    );
    Cart::addCondition($subtotalDiscount);

    // Add a total-targeted condition: +$5 fee
    $totalFee = new CartCondition(
        name: 'total-fee',
        type: 'fee',
        target: 'total',
        value: '+5'
    );
    Cart::addCondition($totalFee);

    // Subtotal should be: 100 - 10 = 90
    expect(Cart::getRawSubtotal())->toBe(90.0);

    // Total should be: 90 + 5 = 95
    expect(Cart::getRawTotal())->toBe(95.0);
});

test('multiple subtotal conditions are applied in order', function () {
    session()->flush();
    Cart::setInstance('test3')->clear();

    // Add an item: $100
    Cart::add('1', 'Product A', 100, 1);

    // Add first subtotal condition: -$10
    $discount1 = new CartCondition(
        name: 'discount-1',
        type: 'discount',
        target: 'subtotal',
        value: '-10'
    );
    Cart::addCondition($discount1);

    // Add second subtotal condition: -10%
    $discount2 = new CartCondition(
        name: 'discount-2',
        type: 'discount',
        target: 'subtotal',
        value: '-10%'
    );
    Cart::addCondition($discount2);

    // Subtotal should be: (100 - 10) - 10% = 90 - 9 = 81
    expect(Cart::getRawSubtotal())->toBe(81.0);
});

test('item conditions, subtotal conditions, and total conditions are all applied correctly', function () {
    session()->flush();
    Cart::setInstance('test4')->clear();

    // Add an item: $100
    Cart::add('1', 'Product A', 100, 1);

    // Add item-level condition: -$5
    $itemDiscount = new CartCondition(
        name: 'item-discount',
        type: 'discount',
        target: 'item',
        value: '-5'
    );
    Cart::addItemCondition('1', $itemDiscount);

    // Add subtotal-targeted condition: -10%
    $subtotalDiscount = new CartCondition(
        name: 'subtotal-discount',
        type: 'discount',
        target: 'subtotal',
        value: '-10%'
    );
    Cart::addCondition($subtotalDiscount);

    // Add total-targeted condition: +$2 fee
    $totalFee = new CartCondition(
        name: 'total-fee',
        type: 'fee',
        target: 'total',
        value: '+2'
    );
    Cart::addCondition($totalFee);

    // Item price after item condition: 100 - 5 = 95
    // Subtotal after subtotal condition: 95 - 10% = 85.5
    // Total after total condition: 85.5 + 2 = 87.5
    expect(Cart::getRawSubtotal())->toBe(85.5);
    expect(Cart::getRawTotal())->toBe(87.5);
});
