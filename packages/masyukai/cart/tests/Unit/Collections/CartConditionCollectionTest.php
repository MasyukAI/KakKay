<?php

declare(strict_types=1);

use MasyukAI\Cart\Collections\CartConditionCollection;
use MasyukAI\Cart\Conditions\CartCondition;

beforeEach(function (): void {
    $this->collection = new CartConditionCollection;
    $this->condition1 = new CartCondition(
        name: 'discount-10',
        type: 'discount',
        target: 'subtotal',
        value: '-10%',
        attributes: ['description' => '10% off'],
        order: 1
    );
    $this->condition2 = new CartCondition(
        name: 'shipping',
        type: 'charge',
        target: 'subtotal',
        value: '+15',
        attributes: ['description' => 'Shipping fee'],
        order: 2
    );
    $this->condition3 = new CartCondition(
        name: 'tax',
        type: 'charge',
        target: 'subtotal',
        value: '+8%',
        attributes: ['description' => 'Sales tax'],
        order: 3
    );
});

it('can be instantiated empty', function (): void {
    expect($this->collection)->toBeInstanceOf(CartConditionCollection::class)
        ->and($this->collection->count())->toBe(0)
        ->and($this->collection->isEmpty())->toBeTrue();
});

it('can add conditions with put method', function (): void {
    $this->collection->put('discount-10', $this->condition1);

    expect($this->collection->count())->toBe(1)
        ->and($this->collection->has('discount-10'))->toBeTrue()
        ->and($this->collection->get('discount-10'))->toBe($this->condition1);
});

it('can add multiple conditions', function (): void {
    $this->collection->put('discount-10', $this->condition1);
    $this->collection->put('shipping', $this->condition2);
    $this->collection->put('tax', $this->condition3);

    expect($this->collection->count())->toBe(3)
        ->and($this->collection->has('discount-10'))->toBeTrue()
        ->and($this->collection->has('shipping'))->toBeTrue()
        ->and($this->collection->has('tax'))->toBeTrue();
});

it('can remove conditions', function (): void {
    $this->collection->put('discount-10', $this->condition1);
    $this->collection->put('shipping', $this->condition2);

    $this->collection->forget('discount-10');

    expect($this->collection->count())->toBe(1)
        ->and($this->collection->has('discount-10'))->toBeFalse()
        ->and($this->collection->has('shipping'))->toBeTrue();
});

it('can filter conditions by type', function (): void {
    $this->collection->put('discount-10', $this->condition1); // discount
    $this->collection->put('shipping', $this->condition2);   // charge
    $this->collection->put('tax', $this->condition3);        // charge

    $discounts = $this->collection->filter(function ($condition) {
        return $condition->getType() === 'discount';
    });

    $charges = $this->collection->filter(function ($condition) {
        return $condition->getType() === 'charge';
    });

    expect($discounts->count())->toBe(1)
        ->and($charges->count())->toBe(2);
});

it('can filter conditions by target', function (): void {
    $itemCondition = new CartCondition(
        name: 'item-discount',
        type: 'discount',
        target: 'item',
        value: '-5%'
    );

    $this->collection->put('discount-10', $this->condition1);  // subtotal
    $this->collection->put('shipping', $this->condition2);     // subtotal
    $this->collection->put('item-discount', $itemCondition);   // item

    $subtotalConditions = $this->collection->byTarget('subtotal');
    $itemConditions = $this->collection->byTarget('item');

    expect($subtotalConditions->count())->toBe(2)
        ->and($itemConditions->count())->toBe(1)
        ->and($itemConditions->first())->toBe($itemCondition);
});

it('can filter conditions by value', function (): void {
    $condition4 = new CartCondition(
        name: 'special-discount',
        type: 'discount',
        target: 'subtotal',
        value: '-10%' // Same value as condition1
    );
    
    $this->collection->put('discount-10', $this->condition1);     // -10%
    $this->collection->put('shipping', $this->condition2);        // +15
    $this->collection->put('tax', $this->condition3);             // +8%
    $this->collection->put('special-discount', $condition4);      // -10%

    $tenPercentConditions = $this->collection->byValue('-10%');
    $fifteenFlatConditions = $this->collection->byValue('+15');
    $eightPercentConditions = $this->collection->byValue('+8%');

    expect($tenPercentConditions->count())->toBe(2)
        ->and($fifteenFlatConditions->count())->toBe(1)
        ->and($eightPercentConditions->count())->toBe(1)
        ->and($tenPercentConditions->keys()->toArray())->toContain('discount-10', 'special-discount')
        ->and($fifteenFlatConditions->first())->toBe($this->condition2);
});

it('can sort conditions by order', function (): void {
    // Add in random order
    $this->collection->put('tax', $this->condition3);       // order: 3
    $this->collection->put('discount-10', $this->condition1); // order: 1
    $this->collection->put('shipping', $this->condition2);   // order: 2

    $sorted = $this->collection->sortBy(function ($condition) {
        return $condition->getOrder();
    });

    $sortedKeys = $sorted->keys()->toArray();
    expect($sortedKeys)->toBe(['discount-10', 'shipping', 'tax']);
});

it('can find conditions by name', function (): void {
    $this->collection->put('discount-10', $this->condition1);
    $this->collection->put('shipping', $this->condition2);

    $found = $this->collection->first(function ($condition) {
        return $condition->getName() === 'shipping';
    });

    expect($found)->toBe($this->condition2)
        ->and($found->getName())->toBe('shipping');
});

it('can calculate total charge amount', function (): void {
    $this->collection->put('shipping', $this->condition2);  // +15
    $this->collection->put('tax', $this->condition3);       // +8%

    $subtotal = 100;
    $total = 0;

    foreach ($this->collection as $condition) {
        if ($condition->getType() === 'charge') {
            $value = $condition->getValue();
            if (str_contains($value, '%')) {
                $percentage = (float) str_replace(['%', '+'], '', $value);
                $total += $subtotal * ($percentage / 100);
            } else {
                $total += (float) str_replace('+', '', $value);
            }
        }
    }

    expect($total)->toBe(23.0); // 15 + (100 * 8%)
});

it('can get discount conditions only', function (): void {
    $this->collection->put('discount-10', $this->condition1); // discount
    $this->collection->put('shipping', $this->condition2);     // charge

    $discounts = $this->collection->filter(fn ($condition) => $condition->getType() === 'discount');

    expect($discounts->count())->toBe(1)
        ->and($discounts->first()->getName())->toBe('discount-10');
});

it('can group conditions by type', function (): void {
    $this->collection->put('discount-10', $this->condition1); // discount
    $this->collection->put('shipping', $this->condition2);     // charge
    $this->collection->put('tax', $this->condition3);          // charge

    $grouped = $this->collection->groupBy(function ($condition) {
        return $condition->getType();
    });

    expect($grouped->has('discount'))->toBeTrue()
        ->and($grouped->has('charge'))->toBeTrue()
        ->and($grouped->get('discount')->count())->toBe(1)
        ->and($grouped->get('charge')->count())->toBe(2);
});

it('can check if condition exists by name', function (): void {
    $this->collection->put('discount-10', $this->condition1);

    $existsByKey = $this->collection->has('discount-10');
    $existsByValue = $this->collection->contains(function ($condition) {
        return $condition->getName() === 'discount-10';
    });

    expect($existsByKey)->toBeTrue()
        ->and($existsByValue)->toBeTrue();
});

it('can reject conditions by criteria', function (): void {
    $this->collection->put('discount-10', $this->condition1);
    $this->collection->put('shipping', $this->condition2);
    $this->collection->put('tax', $this->condition3);

    $nonCharges = $this->collection->reject(function ($condition) {
        return $condition->getType() === 'charge';
    });

    expect($nonCharges->count())->toBe(1)
        ->and($nonCharges->first()->getType())->toBe('discount');
});

it('can map conditions to array format', function (): void {
    $this->collection->put('discount-10', $this->condition1);
    $this->collection->put('shipping', $this->condition2);

    $mapped = $this->collection->map(function ($condition) {
        return [
            'name' => $condition->getName(),
            'type' => $condition->getType(),
            'value' => $condition->getValue(),
        ];
    });

    expect($mapped->count())->toBe(2)
        ->and($mapped->first()['name'])->toBe('discount-10')
        ->and($mapped->first()['type'])->toBe('discount')
        ->and($mapped->first()['value'])->toBe('-10%');
});

it('can convert to array', function (): void {
    $this->collection->put('discount-10', $this->condition1);
    $this->collection->put('shipping', $this->condition2);

    $array = $this->collection->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey('discount-10')
        ->and($array)->toHaveKey('shipping')
        ->and($array['discount-10'])->toBeArray()
        ->and($array['discount-10']['name'])->toBe('discount-10');
});

it('can convert to JSON', function (): void {
    $this->collection->put('discount-10', $this->condition1);

    $json = $this->collection->toJson();
    $decoded = json_decode($json, true);

    expect($json)->toBeJson()
        ->and($decoded)->toHaveKey('discount-10');
});

it('can handle empty operations gracefully', function (): void {
    expect($this->collection->first())->toBeNull()
        ->and($this->collection->isEmpty())->toBeTrue()
        ->and($this->collection->isNotEmpty())->toBeFalse()
        ->and($this->collection->filter(fn ($c) => true)->count())->toBe(0);
});

it('can pluck specific values from conditions', function (): void {
    $this->collection->put('discount-10', $this->condition1);
    $this->collection->put('shipping', $this->condition2);

    $names = $this->collection->map(fn ($c) => $c->getName())->values();
    $types = $this->collection->map(fn ($c) => $c->getType())->values();

    expect($names->toArray())->toContain('discount-10')
        ->and($names->toArray())->toContain('shipping')
        ->and($types->toArray())->toContain('discount')
        ->and($types->toArray())->toContain('charge');
});

it('can determine unique conditions by name', function (): void {
    $duplicateCondition = new CartCondition(
        name: 'discount-10', // Same name as condition1
        type: 'discount',
        target: 'subtotal',
        value: '-15%' // Different value
    );

    $this->collection->put('discount-10-1', $this->condition1);
    $this->collection->put('shipping', $this->condition2);
    $this->collection->put('discount-10-2', $duplicateCondition);

    $uniqueByName = $this->collection->unique(function ($condition) {
        return $condition->getName();
    });

    expect($uniqueByName->count())->toBe(2); // discount-10 and shipping
});

it('can validate condition types', function (): void {
    $this->collection->put('discount-10', $this->condition1);
    $this->collection->put('shipping', $this->condition2);
    $this->collection->put('tax', $this->condition3);

    $hasValidTypes = $this->collection->every(function ($condition) {
        return in_array($condition->getType(), ['discount', 'charge']);
    });

    expect($hasValidTypes)->toBeTrue();
});

it('can partition conditions by type', function (): void {
    $this->collection->put('discount-10', $this->condition1);
    $this->collection->put('shipping', $this->condition2);
    $this->collection->put('tax', $this->condition3);

    [$discounts, $charges] = $this->collection->partition(function ($condition) {
        return $condition->getType() === 'discount';
    });

    expect($discounts->count())->toBe(1)
        ->and($charges->count())->toBe(2);
});

it('can slice conditions collection', function (): void {
    $this->collection->put('discount-10', $this->condition1);
    $this->collection->put('shipping', $this->condition2);
    $this->collection->put('tax', $this->condition3);

    $sliced = $this->collection->slice(1, 1);

    expect($sliced->count())->toBe(1)
        ->and($sliced->values()->first())->toBe($this->condition2);
});
