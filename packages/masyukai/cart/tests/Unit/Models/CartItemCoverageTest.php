<?php

declare(strict_types=1);

use MasyukAI\Cart\Collections\CartConditionCollection;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Models\CartItem;

beforeEach(function (): void {
    $this->condition1 = new CartCondition(
        name: 'discount-10',
        type: 'discount',
        target: 'subtotal',
        value: '-10%'
    );
    $this->condition2 = new CartCondition(
        name: 'shipping',
        type: 'charge',
        target: 'subtotal',
        value: '+15'
    );
});

it('ensures price does not go negative with conditions', function (): void {
    $negativeCondition = new CartCondition(
        name: 'huge-discount',
        type: 'discount',
        target: 'subtotal',
        value: '-200%' // This would make price negative
    );

    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 10.0,
        quantity: 1,
        conditions: [$negativeCondition]
    );

    expect($item->getRawPriceWithConditions())->toBe(0.0); // Should not go negative
});

it('can clear all conditions', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 1,
        conditions: [$this->condition1, $this->condition2]
    );

    expect($item->hasConditions())->toBeTrue()
        ->and($item->conditions->count())->toBe(2);

    $clearedItem = $item->clearConditions();

    expect($clearedItem->hasConditions())->toBeFalse()
        ->and($clearedItem->conditions->count())->toBe(0);
});

it('can check if item has any conditions', function (): void {
    $itemWithConditions = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 1,
        conditions: [$this->condition1]
    );

    $itemWithoutConditions = new CartItem(
        id: 'product-2',
        name: 'Test Product 2',
        price: 100.0,
        quantity: 1
    );

    expect($itemWithConditions->hasConditions())->toBeTrue()
        ->and($itemWithoutConditions->hasConditions())->toBeFalse();
});

it('can get all conditions collection', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 1,
        conditions: [$this->condition1, $this->condition2]
    );

    $conditions = $item->getConditions();

    expect($conditions)->toBeInstanceOf(CartConditionCollection::class)
        ->and($conditions->count())->toBe(2)
        ->and($conditions->has('discount-10'))->toBeTrue()
        ->and($conditions->has('shipping'))->toBeTrue();
});

it('returns null when getting non-existent condition', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 1
    );

    expect($item->getCondition('non-existent'))->toBeNull();
});

it('can use discount amount alias method', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 2,
        conditions: [$this->condition1] // -10%
    );

    expect($item->discountAmount())->toBe(20.0) // Same as getDiscountAmount()
        ->and($item->discountAmount())->toBe($item->getDiscountAmount());
});

it('can use final total alias method', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 2,
        conditions: [$this->condition1] // -10%
    );

    expect($item->finalTotal())->toBe(180.0) // Same as getRawPriceSumWithConditions()
        ->and($item->finalTotal())->toBe($item->getRawPriceSumWithConditions());
});

it('can use withQuantity method (shopping cart style)', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 2
    );

    $newItem = $item->withQuantity(5);

    expect($newItem->quantity)->toBe(5)
        ->and($newItem)->not->toBe($item); // Should be new instance
});

it('can check if two items are equal', function (): void {
    $item1 = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 2
    );

    $item2 = new CartItem(
        id: 'product-1',
        name: 'Different Name', // Different name but same ID
        price: 200.0,
        quantity: 3
    );

    $item3 = new CartItem(
        id: 'product-2',
        name: 'Test Product',
        price: 100.0,
        quantity: 2
    );

    expect($item1->equals($item2))->toBeTrue() // Same ID
        ->and($item1->equals($item3))->toBeFalse(); // Different ID
});

it('can create item copy with modified properties using with method', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 2,
        attributes: ['color' => 'red'],
        conditions: [$this->condition1]
    );

    $modifiedItem = $item->with([
        'name' => 'Modified Product',
        'price' => 150.0,
        'attributes' => ['color' => 'blue', 'size' => 'large'],
    ]);

    expect($modifiedItem->id)->toBe('product-1') // Unchanged
        ->and($modifiedItem->name)->toBe('Modified Product') // Changed
        ->and($modifiedItem->price)->toBe(150.0) // Changed
        ->and($modifiedItem->quantity)->toBe(2) // Unchanged
        ->and($modifiedItem->getAttribute('color'))->toBe('blue') // Changed
        ->and($modifiedItem->getAttribute('size'))->toBe('large') // New
        ->and($modifiedItem->hasCondition('discount-10'))->toBeTrue(); // Unchanged
});

it('can create item copy with all properties modified using with method', function (): void {
    $model = new stdClass;
    $model->id = 123;

    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 2
    );

    $modifiedItem = $item->with([
        'id' => 'product-2',
        'name' => 'Modified Product',
        'price' => 150.0,
        'quantity' => 3,
        'attributes' => ['color' => 'blue'],
        'conditions' => [$this->condition1],
        'associated_model' => $model,
    ]);

    expect($modifiedItem->id)->toBe('product-2')
        ->and($modifiedItem->name)->toBe('Modified Product')
        ->and($modifiedItem->price)->toBe(150.0)
        ->and($modifiedItem->quantity)->toBe(3)
        ->and($modifiedItem->getAttribute('color'))->toBe('blue')
        ->and($modifiedItem->hasCondition('discount-10'))->toBeTrue()
        ->and($modifiedItem->getAssociatedModel())->toBe($model);
});

it('can convert to string representation', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 99.99,
        quantity: 2
    );

    $string = (string) $item;

    expect($string)->toBe('Test Product (ID: product-1, Price: 99.99, Quantity: 2)');
});

it('handles array conditions in constructor', function (): void {
    $conditionArray = [
        'discount-10' => [
            'name' => 'discount-10',
            'type' => 'discount',
            'target' => 'subtotal',
            'value' => '-10%',
        ],
    ];

    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 1,
        conditions: $conditionArray
    );

    expect($item->hasCondition('discount-10'))->toBeTrue()
        ->and($item->getCondition('discount-10'))->toBeInstanceOf(CartCondition::class);
});

it('handles Collection conditions in constructor', function (): void {
    $collection = collect([$this->condition1, $this->condition2]);

    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 1,
        conditions: $collection
    );

    expect($item->hasCondition('discount-10'))->toBeTrue()
        ->and($item->hasCondition('shipping'))->toBeTrue()
        ->and($item->conditions->count())->toBe(2);
});

it('handles CartConditionCollection in constructor', function (): void {
    $conditionCollection = new CartConditionCollection;
    $conditionCollection->put('discount-10', $this->condition1);
    $conditionCollection->put('shipping', $this->condition2);

    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 1,
        conditions: $conditionCollection
    );

    expect($item->hasCondition('discount-10'))->toBeTrue()
        ->and($item->hasCondition('shipping'))->toBeTrue()
        ->and($item->conditions->count())->toBe(2);
});

it('handles associated model serialization with toArray method', function (): void {
    $model = new class
    {
        public $id = 123;

        public $name = 'Test Model';

        public function toArray(): array
        {
            return [
                'id' => $this->id,
                'name' => $this->name,
            ];
        }
    };

    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 1,
        associatedModel: $model
    );

    $array = $item->toArray();

    expect($array['associated_model'])->toBeArray()
        ->and($array['associated_model']['class'])->toContain('class@anonymous')
        ->and($array['associated_model']['id'])->toBe(123)
        ->and($array['associated_model']['data'])->toBe(['id' => 123, 'name' => 'Test Model']);
});

it('handles associated model serialization without toArray method', function (): void {
    $model = new class
    {
        public $id = 123;

        public $name = 'Test Model';
    };

    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 1,
        associatedModel: $model
    );

    $array = $item->toArray();

    expect($array['associated_model'])->toBeArray()
        ->and($array['associated_model']['class'])->toContain('class@anonymous')
        ->and($array['associated_model']['id'])->toBe(123)
        ->and($array['associated_model']['data'])->toBeArray();
});

it('handles string associated model serialization', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 1,
        associatedModel: 'stdClass' // Use existing class
    );

    $array = $item->toArray();

    expect($array['associated_model'])->toBe('stdClass');
});

it('handles null associated model serialization', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 1
    );

    $array = $item->toArray();

    expect($array['associated_model'])->toBeNull();
});

it('trims whitespace in name validation', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: '  Test Product  ',
        price: 100.0,
        quantity: 1
    );

    expect($item->name)->toBe('  Test Product  '); // Original preserved

    $trimmedItem = $item->setName('  New Name  ');
    expect($trimmedItem->name)->toBe('New Name'); // Trimmed
});

it('trims whitespace in id validation', function (): void {
    $item = new CartItem(
        id: '  product-1  ',
        name: 'Test Product',
        price: 100.0,
        quantity: 1
    );

    expect($item->id)->toBe('  product-1  '); // Original preserved during construction
});

it('validates associated model exists when provided as string', function (): void {
    // This should work with existing class
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 1,
        associatedModel: 'stdClass'
    );

    expect($item->getAssociatedModel())->toBe('stdClass');

    // This should fail with non-existent class
    expect(fn () => new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 1,
        associatedModel: 'App\\Models\\NonExistentModel'
    ))->toThrow(\MasyukAI\Cart\Exceptions\UnknownModelException::class);
});

it('ignores empty string associated model validation', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 1,
        associatedModel: ''
    );

    expect($item->getAssociatedModel())->toBe('');
});

it('handles getAttribute with default value', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 1,
        attributes: ['color' => 'red']
    );

    expect($item->getAttribute('color', 'default'))->toBe('red')
        ->and($item->getAttribute('size', 'large'))->toBe('large')
        ->and($item->getAttribute('weight', null))->toBeNull();
});
