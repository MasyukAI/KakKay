<?php

declare(strict_types=1);

use MasyukAI\Cart\Collections\CartConditionCollection;
use MasyukAI\Cart\Conditions\CartCondition;

describe('CartConditionCollection Coverage Tests', function () {
    beforeEach(function () {
        $this->collection = new CartConditionCollection;
    });

    it('can validate condition types correctly', function () {
        $validCondition = new CartCondition('discount', 'discount', 'subtotal', '-10');
        $invalidCondition = (object) ['name' => 'invalid'];

        // Test valid condition
        $this->collection->put('valid', $validCondition);
        expect($this->collection->has('valid'))->toBeTrue();

        // Test that invalid conditions are handled gracefully
        expect(function () use ($invalidCondition) {
            $this->collection->put('invalid', $invalidCondition);
        })->not->toThrow(\Exception::class);
    });

    it('can partition conditions by type', function () {
        $discount = new CartCondition('discount', 'discount', 'subtotal', '-10');
        $charge = new CartCondition('tax', 'charge', 'subtotal', '8%');
        $shipping = new CartCondition('shipping', 'charge', 'subtotal', '5.99');

        $this->collection->put('discount', $discount);
        $this->collection->put('tax', $charge);
        $this->collection->put('shipping', $shipping);

        $partitioned = $this->collection->partition(fn ($condition) => $condition->getType() === 'discount');

        expect($partitioned[0])->toHaveCount(1); // discounts
        expect($partitioned[1])->toHaveCount(2); // charges
    });

    it('can slice conditions collection', function () {
        $conditions = collect([
            'first' => new CartCondition('first', 'discount', 'subtotal', '-5'),
            'second' => new CartCondition('second', 'charge', 'subtotal', '10'),
            'third' => new CartCondition('third', 'discount', 'subtotal', '-15'),
        ]);

        foreach ($conditions as $name => $condition) {
            $this->collection->put($name, $condition);
        }

        $sliced = $this->collection->slice(1, 2);

        expect($sliced)->toHaveCount(2);
        expect($sliced->has('first'))->toBeFalse();
        expect($sliced->has('second'))->toBeTrue();
        expect($sliced->has('third'))->toBeTrue();
    });

    it('can determine uniqueness by name', function () {
        $condition1 = new CartCondition('unique', 'discount', 'subtotal', '-10');
        $condition2 = new CartCondition('unique', 'charge', 'subtotal', '5');
        $condition3 = new CartCondition('different', 'discount', 'subtotal', '-5');

        $this->collection->put('first', $condition1);
        $this->collection->put('second', $condition2);
        $this->collection->put('third', $condition3);

        $unique = $this->collection->unique(fn ($condition) => $condition->getName());

        expect($unique)->toHaveCount(2); // 'unique' and 'different'
    });

    it('can pluck specific values from conditions', function () {
        $this->collection->put('discount', new CartCondition('10% Off', 'discount', 'subtotal', '-10%'));
        $this->collection->put('tax', new CartCondition('Sales Tax', 'charge', 'subtotal', '8%'));

        // Pluck using callback since properties are private
        $names = $this->collection->map(fn ($condition) => $condition->getName());

        expect($names->toArray())->toContain('10% Off');
        expect($names->toArray())->toContain('Sales Tax');
        expect($names)->toHaveCount(2);
    });

    it('handles empty operations gracefully', function () {
        // Test empty collection operations
        expect($this->collection->getTotalCharges(100))->toBe(0.0);
        expect($this->collection->discounts())->toBeEmpty();
        expect($this->collection->groupByType())->toBeEmpty();
        expect($this->collection->sortByOrder())->toBeEmpty();
        expect($this->collection->byType('discount'))->toBeEmpty();
    });

    it('can convert to array format', function () {
        $condition = new CartCondition('Test', 'discount', 'subtotal', '-10');
        $this->collection->put('test', $condition);

        $array = $this->collection->toArray();

        expect($array)->toBeArray();
        expect($array)->toHaveKey('test');
    });

    it('can convert to JSON', function () {
        $condition = new CartCondition('Test', 'discount', 'subtotal', '-10');
        $this->collection->put('test', $condition);

        $json = $this->collection->toJson();

        expect($json)->toBeString();
        expect(json_decode($json, true))->toBeArray();
    });

    it('can reject conditions by criteria', function () {
        $this->collection->put('discount', new CartCondition('Discount', 'discount', 'subtotal', '-10'));
        $this->collection->put('tax', new CartCondition('Tax', 'charge', 'subtotal', '8'));
        $this->collection->put('shipping', new CartCondition('Shipping', 'charge', 'subtotal', '5'));

        $withoutCharges = $this->collection->reject(fn ($condition) => $condition->getType() === 'charge');

        expect($withoutCharges)->toHaveCount(1);
        expect($withoutCharges->has('discount'))->toBeTrue();
        expect($withoutCharges->has('tax'))->toBeFalse();
        expect($withoutCharges->has('shipping'))->toBeFalse();
    });

    it('can check if condition exists by name', function () {
        $condition = new CartCondition('Test Condition', 'discount', 'subtotal', '-10');
        $this->collection->put('test', $condition);

        expect($this->collection->hasCondition('test'))->toBeTrue();
        expect($this->collection->hasCondition('nonexistent'))->toBeFalse();
    });

    it('can find conditions by get method', function () {
        $condition1 = new CartCondition('First Discount', 'discount', 'subtotal', '-10');
        $condition2 = new CartCondition('Second Discount', 'discount', 'subtotal', '-5');

        $this->collection->put('first', $condition1);
        $this->collection->put('second', $condition2);

        $found = $this->collection->getCondition('first');

        expect($found)->not->toBeNull();
        expect($found->getName())->toBe('First Discount');

        $notFound = $this->collection->getCondition('nonexistent');
        expect($notFound)->toBeNull();
    });

    it('can sort conditions by order', function () {
        $condition1 = new CartCondition('First', 'discount', 'subtotal', '-10', [], 3);
        $condition2 = new CartCondition('Second', 'charge', 'subtotal', '5', [], 1);
        $condition3 = new CartCondition('Third', 'discount', 'subtotal', '-5', [], 2);

        $this->collection->put('third', $condition3);
        $this->collection->put('first', $condition1);
        $this->collection->put('second', $condition2);

        $sorted = $this->collection->sortBy(fn ($condition) => $condition->getOrder());

        $names = $sorted->map(fn ($condition) => $condition->getName())->values()->toArray();
        expect($names)->toBe(['Second', 'Third', 'First']);
    });

    it('can group conditions by type', function () {
        $this->collection->put('discount1', new CartCondition('Discount 1', 'discount', 'subtotal', '-10'));
        $this->collection->put('discount2', new CartCondition('Discount 2', 'discount', 'subtotal', '-5'));
        $this->collection->put('tax', new CartCondition('Tax', 'charge', 'subtotal', '8'));

        $grouped = $this->collection->groupByType();

        expect($grouped)->toHaveKey('discount');
        expect($grouped)->toHaveKey('charge');
        expect($grouped['discount'])->toHaveCount(2);
        expect($grouped['charge'])->toHaveCount(1);
    });

    it('can get only discount conditions', function () {
        $this->collection->put('discount', new CartCondition('Discount', 'discount', 'subtotal', '-10'));
        $this->collection->put('tax', new CartCondition('Tax', 'charge', 'subtotal', '8'));

        $discounts = $this->collection->discounts();

        expect($discounts)->toHaveCount(1);
        expect($discounts->first()->getType())->toBe('discount');
    });

    it('can calculate total charge amount', function () {
        $this->collection->put('tax', new CartCondition('Tax', 'charge', 'subtotal', '10'));
        $this->collection->put('shipping', new CartCondition('Shipping', 'charge', 'subtotal', '5.50'));
        $this->collection->put('discount', new CartCondition('Discount', 'discount', 'subtotal', '-3'));

        $total = $this->collection->getTotalCharges(100);

        expect($total)->toBe(15.5); // 10 + 5.50, discount not included
    });

    it('can map conditions to array format', function () {
        $this->collection->put('test', new CartCondition('Test', 'discount', 'subtotal', '-10'));

        $mapped = $this->collection->map(fn ($condition) => [
            'name' => $condition->getName(),
            'type' => $condition->getType(),
        ]);

        expect($mapped)->toHaveCount(1);
        expect($mapped->first())->toHaveKey('name');
        expect($mapped->first())->toHaveKey('type');
    });

    it('can get detailed array with calculations', function () {
        $condition = new CartCondition('Test', 'discount', 'subtotal', '-10');
        $this->collection->put('test', $condition);

        $detailed = $this->collection->toDetailedArray(100);

        expect($detailed)->toHaveKey('conditions');
        expect($detailed)->toHaveKey('summary');
        expect($detailed['conditions'])->toHaveCount(1);
    });

    it('can check if has discounts or charges', function () {
        expect($this->collection->hasDiscounts())->toBeFalse();
        expect($this->collection->hasCharges())->toBeFalse();

        $this->collection->put('discount', new CartCondition('Discount', 'discount', 'subtotal', '-10'));

        expect($this->collection->hasDiscounts())->toBeTrue();
        expect($this->collection->hasCharges())->toBeFalse();
    });

    it('can filter by target', function () {
        $this->collection->put('subtotal_discount', new CartCondition('Subtotal Discount', 'discount', 'subtotal', '-10'));
        $this->collection->put('total_tax', new CartCondition('Total Tax', 'charge', 'total', '8'));

        $subtotalConditions = $this->collection->byTarget('subtotal');
        $totalConditions = $this->collection->byTarget('total');

        expect($subtotalConditions)->toHaveCount(1);
        expect($totalConditions)->toHaveCount(1);
    });
});

it('can use removeCondition method', function () {
    $condition = new CartCondition('test_remove', 'discount', 'subtotal', '-10');
    $collection = new CartConditionCollection;
    $collection->addCondition($condition);

    expect($collection->count())->toBe(1);

    $result = $collection->removeCondition('test_remove');
    expect($result->count())->toBe(0);
    expect($result)->toBeInstanceOf(CartConditionCollection::class);
});

it('can create collection from array using fromArray method', function () {
    $conditionsArray = [
        CartCondition::fromArray(['name' => 'discount1', 'type' => 'discount', 'value' => '-10']),
        new CartCondition('discount2', 'discount', 'subtotal', '-5'),
    ];

    $collection = CartConditionCollection::fromArray($conditionsArray);

    expect($collection->count())->toBe(2);
    expect($collection->get('discount1'))->toBeInstanceOf(CartCondition::class);
    expect($collection->get('discount2'))->toBeInstanceOf(CartCondition::class);
});

it('can group conditions by target', function () {
    $collection = new CartConditionCollection;
    $collection->addCondition(new CartCondition('discount1', 'discount', 'item', '-10'));
    $collection->addCondition(new CartCondition('tax1', 'charge', 'subtotal', '5'));
    $collection->addCondition(new CartCondition('discount2', 'discount', 'item', '-15'));

    $grouped = $collection->groupByTarget();

    expect($grouped->count())->toBe(2);
    expect($grouped->get('item')->count())->toBe(2);
    expect($grouped->get('subtotal')->count())->toBe(1);
});

it('can check if collection has discounts', function () {
    $collection = new CartConditionCollection;
    $collection->addCondition(new CartCondition('discount1', 'discount', 'subtotal', '-10'));

    expect($collection->hasDiscounts())->toBeTrue();

    $emptyCollection = new CartConditionCollection;
    expect($emptyCollection->hasDiscounts())->toBeFalse();
});

it('can filter conditions with specific attribute', function () {
    $collection = new CartConditionCollection;
    $condition1 = new CartCondition('discount1', 'discount', 'subtotal', '-10', ['category' => 'electronics']);
    $condition2 = new CartCondition('discount2', 'discount', 'subtotal', '-5', ['category' => 'books']);

    $collection->addCondition($condition1);
    $collection->addCondition($condition2);

    $filtered = $collection->withAttribute('category', 'electronics');
    expect($filtered->count())->toBe(1);
    expect($filtered->first()->getName())->toBe('discount1');
});

it('can find condition by attribute', function () {
    $collection = new CartConditionCollection;
    $condition = new CartCondition('discount1', 'discount', 'subtotal', '-10', ['category' => 'electronics']);
    $collection->addCondition($condition);

    $found = $collection->findByAttribute('category', 'electronics');
    expect($found)->toBeInstanceOf(CartCondition::class);
    expect($found->getName())->toBe('discount1');

    $notFound = $collection->findByAttribute('category', 'books');
    expect($notFound)->toBeNull();
});

it('can remove conditions by type', function () {
    $collection = new CartConditionCollection;
    $collection->addCondition(new CartCondition('discount1', 'discount', 'subtotal', '-10'));
    $collection->addCondition(new CartCondition('tax1', 'charge', 'subtotal', '5'));

    expect($collection->count())->toBe(2);

    $filtered = $collection->removeByType('discount');
    expect($filtered->count())->toBe(1);
    expect($filtered->first()->getType())->toBe('charge');
});

it('can remove conditions by target', function () {
    $collection = new CartConditionCollection;
    $collection->addCondition(new CartCondition('discount1', 'discount', 'item', '-10'));
    $collection->addCondition(new CartCondition('tax1', 'charge', 'subtotal', '5'));

    expect($collection->count())->toBe(2);

    $filtered = $collection->removeByTarget('item');
    expect($filtered->count())->toBe(1);
    expect($filtered->first()->getTarget())->toBe('subtotal');
});
