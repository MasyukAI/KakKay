<?php

declare(strict_types=1);

use MasyukAI\Cart\Collections\CartConditionCollection;
use MasyukAI\Cart\Conditions\CartCondition;

it('can add conditions to collection', function () {
    $collection = new CartConditionCollection();
    
    $condition = new CartCondition(
        name: 'Test Condition',
        type: 'discount',
        target: 'subtotal',
        value: '-10%'
    );

    $collection->addCondition($condition);

    expect($collection->count())->toBe(1);
    expect($collection->hasCondition('Test Condition'))->toBeTrue();
    expect($collection->getCondition('Test Condition'))->toBe($condition);
});

it('can filter conditions by type', function () {
    $collection = new CartConditionCollection();
    
    $discount = new CartCondition('Discount', 'discount', 'subtotal', '-10%');
    $tax = new CartCondition('Tax', 'tax', 'subtotal', '8%');
    $shipping = new CartCondition('Shipping', 'shipping', 'subtotal', '+15');

    $collection->addCondition($discount);
    $collection->addCondition($tax);
    $collection->addCondition($shipping);

    $discounts = $collection->byType('discount');
    expect($discounts->count())->toBe(1);
    expect($discounts->first())->toBe($discount);
});

it('can filter conditions by target', function () {
    $collection = new CartConditionCollection();
    
    $subtotalCondition = new CartCondition('Subtotal Tax', 'tax', 'subtotal', '8%');
    $totalCondition = new CartCondition('Total Fee', 'fee', 'total', '+5');

    $collection->addCondition($subtotalCondition);
    $collection->addCondition($totalCondition);

    $subtotalConditions = $collection->byTarget('subtotal');
    expect($subtotalConditions->count())->toBe(1);
    expect($subtotalConditions->first())->toBe($subtotalCondition);
});

it('can get only discount conditions', function () {
    $collection = new CartConditionCollection();
    
    $discount = new CartCondition('Discount', 'discount', 'subtotal', '-10%');
    $charge = new CartCondition('Charge', 'fee', 'subtotal', '+5');

    $collection->addCondition($discount);
    $collection->addCondition($charge);

    $discounts = $collection->discounts();
    expect($discounts->count())->toBe(1);
    expect($discounts->first())->toBe($discount);
});

it('can get only charge conditions', function () {
    $collection = new CartConditionCollection();
    
    $discount = new CartCondition('Discount', 'discount', 'subtotal', '-10%');
    $charge = new CartCondition('Charge', 'fee', 'subtotal', '+5');

    $collection->addCondition($discount);
    $collection->addCondition($charge);

    $charges = $collection->charges();
    expect($charges->count())->toBe(1);
    expect($charges->first())->toBe($charge);
});

it('can apply all conditions to a value', function () {
    $collection = new CartConditionCollection();
    
    // Create conditions with specific order
    $discount = new CartCondition('Discount', 'discount', 'subtotal', '-10%', [], 1);
    $tax = new CartCondition('Tax', 'tax', 'subtotal', '8%', [], 2);

    $collection->addCondition($discount);
    $collection->addCondition($tax);

    // Start with $100
    // Apply 10% discount: $100 * 0.9 = $90
    // Apply 8% tax: $90 * 1.08 = $97.20
    $result = $collection->applyAll(100.0);
    expect($result)->toBe(97.2);
});

it('can create collection from array', function () {
    $conditions = [
        [
            'name' => 'Discount',
            'type' => 'discount',
            'target' => 'subtotal',
            'value' => '-10%',
        ],
        [
            'name' => 'Tax',
            'type' => 'tax',
            'target' => 'subtotal',
            'value' => '8%',
        ],
    ];

    $collection = CartConditionCollection::fromArray($conditions);

    expect($collection->count())->toBe(2);
    expect($collection->hasCondition('Discount'))->toBeTrue();
    expect($collection->hasCondition('Tax'))->toBeTrue();
});

it('can get collection summary', function () {
    $collection = new CartConditionCollection();
    
    $discount = new CartCondition('Discount', 'discount', 'subtotal', '-10%');
    $tax = new CartCondition('Tax', 'tax', 'subtotal', '8%');

    $collection->addCondition($discount);
    $collection->addCondition($tax);

    $summary = $collection->getSummary(100.0);

    expect($summary)->toHaveKeys([
        'total_conditions', 'discounts', 'charges', 'percentages',
        'total_discount_amount', 'total_charges_amount', 'net_adjustment'
    ]);

    expect($summary['total_conditions'])->toBe(2);
    expect($summary['discounts'])->toBe(1);
    expect($summary['charges'])->toBe(1);
    expect($summary['percentages'])->toBe(2);
});

it('can group conditions by type', function () {
    $collection = new CartConditionCollection();
    
    $discount1 = new CartCondition('Discount 1', 'discount', 'subtotal', '-10%');
    $discount2 = new CartCondition('Discount 2', 'discount', 'subtotal', '-5%');
    $tax = new CartCondition('Tax', 'tax', 'subtotal', '8%');

    $collection->addCondition($discount1);
    $collection->addCondition($discount2);
    $collection->addCondition($tax);

    $grouped = $collection->groupByType();

    expect($grouped->has('discount'))->toBeTrue();
    expect($grouped->has('tax'))->toBeTrue();
    expect($grouped->get('discount')->count())->toBe(2);
    expect($grouped->get('tax')->count())->toBe(1);
});
