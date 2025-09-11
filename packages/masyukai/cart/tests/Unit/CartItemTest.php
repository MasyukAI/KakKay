<?php

declare(strict_types=1);

use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Models\CartItem;
use MasyukAI\Cart\Support\CartMoney;

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

it('can create cart item with basic data', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 99.99,
        quantity: 2
    );

    expect($item->id)->toBe('product-1')
        ->and($item->name)->toBe('Test Product')
        ->and($item->price)->toBe(99.99)
        ->and($item->quantity)->toBe(2)
        ->and($item->attributes)->toBeInstanceOf(\Illuminate\Support\Collection::class)
        ->and($item->attributes->toArray())->toBe([])
        ->and($item->conditions)->toBeInstanceOf(\MasyukAI\Cart\Collections\CartConditionCollection::class)
        ->and($item->conditions->isEmpty())->toBeTrue()
        ->and($item->associatedModel)->toBeNull();
});

it('can create cart item with all data', function (): void {
    $model = new stdClass;
    $model->id = 123;

    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 99.99,
        quantity: 2,
        attributes: ['color' => 'red', 'size' => 'large'],
        conditions: [$this->condition1, $this->condition2],
        associatedModel: $model
    );

    expect($item->id)->toBe('product-1')
        ->and($item->name)->toBe('Test Product')
        ->and($item->price)->toBe(99.99)
        ->and($item->quantity)->toBe(2)
        ->and($item->attributes->toArray())->toBe(['color' => 'red', 'size' => 'large'])
        ->and($item->conditions->count())->toBe(2)
        ->and($item->associatedModel)->toBe($model);
});

it('can calculate price sum', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 50.0,
        quantity: 3
    );

    expect($item->getPriceSum())
        ->toBeInstanceOf(CartMoney::class)
        ->and($item->getRawPriceSum())->toBe(150.0);
});

it('can get single attribute', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 99.99,
        quantity: 1,
        attributes: ['color' => 'red', 'size' => 'large']
    );

    expect($item->getAttribute('color'))->toBe('red')
        ->and($item->getAttribute('size'))->toBe('large')
        ->and($item->getAttribute('nonexistent'))->toBeNull();
});

it('can check if associated with model', function (): void {
    $model = new stdClass;
    $model->id = 123;

    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 99.99,
        quantity: 1,
        associatedModel: $model
    );

    expect($item->isAssociatedWith(stdClass::class))->toBeTrue()
        ->and($item->isAssociatedWith('App\\Models\\Product'))->toBeFalse();

    $itemWithoutModel = new CartItem(
        id: 'product-2',
        name: 'Test Product 2',
        price: 49.99,
        quantity: 1
    );

    expect($itemWithoutModel->isAssociatedWith(stdClass::class))->toBeFalse();
});

it('can calculate price with conditions', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 1,
        conditions: [$this->condition1] // -10%
    );

    expect($item->getRawPrice())->toBe(90.0);
});

it('can calculate price sum with conditions', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 2,
        conditions: [$this->condition1] // -10%
    );

    expect($item->getRawPriceSum())->toBe(180.0);
});

it('can calculate discount amount', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 2,
        conditions: [$this->condition1] // -10%
    );

    expect($item->getDiscountAmount()->getAmount())->toBe(20.0); // 200 - 180 = 20
});

it('can convert to array', function (): void {
    $model = new stdClass;
    $model->id = 123;

    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 2,
        attributes: ['color' => 'red'],
        conditions: [$this->condition1],
        associatedModel: $model
    );

    $array = $item->toArray();

    expect($array)->toBeArray()
        ->and($array['id'])->toBe('product-1')
        ->and($array['name'])->toBe('Test Product')
        ->and($array['price'])->toBe(100.0) // Raw price (not calculated)
        ->and($array['quantity'])->toBe(2)
        ->and($array['attributes'])->toBe(['color' => 'red'])
        ->and($array['conditions'])->toBeArray()
        ->and($array['associated_model'])->toBeArray(); // Model gets serialized to array

    // Verify legacy fields are no longer present
    expect($array)->not->toHaveKeys([
        'price_sum',
        'price_without_conditions',
        'price_sum_without_conditions',
        'subtotal_without_conditions',
        'discount_amount',
    ]);

    // But calculated values are still accessible via methods
    expect($item->getPriceSum()->getAmount())->toBe(180.0); // (100 - 10%) * 2 = 180
    expect($item->getPriceWithoutConditions()->getAmount())->toBe(100.0); // Original price
    expect($item->getPriceSumWithoutConditions()->getAmount())->toBe(200.0); // Original total
    expect($item->getDiscountAmount()->getAmount())->toBe(20.0);
});

it('can set new quantity', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 99.99,
        quantity: 2
    );

    $newItem = $item->setQuantity(5);

    expect($newItem->quantity)->toBe(5)
        ->and($newItem->id)->toBe('product-1') // Other properties preserved
        ->and($item->quantity)->toBe(2); // Original item unchanged
});

it('can add and remove attributes', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 99.99,
        quantity: 1
    );

    $itemWithAttribute = $item->addAttribute('color', 'red');
    expect($itemWithAttribute->getAttribute('color'))->toBe('red')
        ->and($itemWithAttribute->hasAttribute('color'))->toBeTrue();

    $itemWithoutAttribute = $itemWithAttribute->removeAttribute('color');
    expect($itemWithoutAttribute->hasAttribute('color'))->toBeFalse();
});

it('can add and remove conditions', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 1
    );

    $itemWithCondition = $item->addCondition($this->condition1);
    expect($itemWithCondition->hasCondition('discount-10'))->toBeTrue()
        ->and($itemWithCondition->getCondition('discount-10'))->toBe($this->condition1);

    $itemWithoutCondition = $itemWithCondition->removeCondition('discount-10');
    expect($itemWithoutCondition->hasCondition('discount-10'))->toBeFalse();
});

it('validates required fields on creation', function (): void {
    expect(fn () => new CartItem(
        id: '',
        name: 'Test Product',
        price: 99.99,
        quantity: 1
    ))->toThrow(\MasyukAI\Cart\Exceptions\InvalidCartItemException::class);

    expect(fn () => new CartItem(
        id: 'product-1',
        name: '',
        price: 99.99,
        quantity: 1
    ))->toThrow(\MasyukAI\Cart\Exceptions\InvalidCartItemException::class);

    expect(fn () => new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: -10.0,
        quantity: 1
    ))->toThrow(\MasyukAI\Cart\Exceptions\InvalidCartItemException::class);

    expect(fn () => new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 99.99,
        quantity: -1
    ))->toThrow(\MasyukAI\Cart\Exceptions\InvalidCartItemException::class);
});

it('can convert to JSON', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 99.99,
        quantity: 2
    );

    $json = $item->toJson();
    $decoded = json_decode($json, true);

    expect($decoded)->toBeArray()
        ->and($decoded['id'])->toBe('product-1')
        ->and($decoded['name'])->toBe('Test Product');
});

it('can be JSON serialized', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 99.99,
        quantity: 2
    );

    $json = json_encode($item);
    $decoded = json_decode($json, true);

    expect($decoded)->toBeArray()
        ->and($decoded['id'])->toBe('product-1')
        ->and($decoded['name'])->toBe('Test Product');
});

it('handles complex conditions correctly', function (): void {
    $discountCondition = new CartCondition(
        name: 'discount-20',
        type: 'discount',
        target: 'subtotal',
        value: '-20%'
    );

    $chargeCondition = new CartCondition(
        name: 'handling',
        type: 'charge',
        target: 'subtotal',
        value: '+5'
    );

    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 2,
        conditions: [$discountCondition, $chargeCondition]
    );

    // Original: 200.0
    // Price after 20% discount: 80.0
    // Price after $5 charge: 85.0
    // Sum with conditions: 85.0 * 2 = 170.0
    expect($item->getRawPriceSum())->toBe(170.0)
        ->and($item->getDiscountAmount()->getAmount())->toBe(30.0); // 200 - 170
});

it('can set item name', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Original Name',
        price: 100.0,
        quantity: 2
    );

    $newItem = $item->setName('New Name');

    expect($newItem->name)->toBe('New Name')
        ->and($newItem->id)->toBe($item->id)
        ->and($newItem->price)->toBe($item->price)
        ->and($newItem->quantity)->toBe($item->quantity)
        ->and($newItem)->not->toBe($item); // Should be new instance
});

it('validates name when setting', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Original Name',
        price: 100.0,
        quantity: 2
    );

    expect(fn () => $item->setName(''))->toThrow(\MasyukAI\Cart\Exceptions\InvalidCartItemException::class)
        ->and(fn () => $item->setName('   '))->toThrow(\MasyukAI\Cart\Exceptions\InvalidCartItemException::class);
});

it('can set item price', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 2
    );

    $newItem = $item->setPrice(150.0);

    expect($newItem->price)->toBe(150.0)
        ->and($newItem->id)->toBe($item->id)
        ->and($newItem->name)->toBe($item->name)
        ->and($newItem->quantity)->toBe($item->quantity)
        ->and($newItem)->not->toBe($item); // Should be new instance
});

it('validates price when setting', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 2
    );

    expect(fn () => $item->setPrice(-10.0))->toThrow(\MasyukAI\Cart\Exceptions\InvalidCartItemException::class);
});

it('validates quantity when setting', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 2
    );

    expect(fn () => $item->setQuantity(-1))->toThrow(\MasyukAI\Cart\Exceptions\InvalidCartItemException::class);
});

it('can set item attributes', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 2,
        attributes: ['color' => 'red']
    );

    $newAttributes = ['color' => 'blue', 'size' => 'large'];
    $newItem = $item->setAttributes($newAttributes);

    expect($newItem->attributes->toArray())->toBe($newAttributes)
        ->and($newItem->getAttribute('color'))->toBe('blue')
        ->and($newItem->getAttribute('size'))->toBe('large')
        ->and($newItem)->not->toBe($item); // Should be new instance
});

it('can update individual attributes', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 2,
        attributes: ['color' => 'red', 'size' => 'small']
    );

    $newItem = $item->addAttribute('color', 'blue');

    expect($newItem->getAttribute('color'))->toBe('blue')
        ->and($newItem->getAttribute('size'))->toBe('small')
        ->and($newItem)->not->toBe($item); // Should be new instance
});

it('can add new attributes via addAttribute', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 2,
        attributes: ['color' => 'red']
    );

    $newItem = $item->addAttribute('size', 'large');

    expect($newItem->getAttribute('color'))->toBe('red')
        ->and($newItem->getAttribute('size'))->toBe('large')
        ->and($newItem->attributes->count())->toBe(2);
});

it('can remove attributes', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 2,
        attributes: ['color' => 'red', 'size' => 'small']
    );

    $newItem = $item->removeAttribute('size');

    expect($newItem->hasAttribute('color'))->toBeTrue()
        ->and($newItem->hasAttribute('size'))->toBeFalse()
        ->and($newItem->attributes->count())->toBe(1);
});

it('handles removing non-existent attributes gracefully', function (): void {
    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 2,
        attributes: ['color' => 'red']
    );

    $newItem = $item->removeAttribute('non-existent');

    expect($newItem->attributes->toArray())->toBe($item->attributes->toArray());
});

it('can check if associated with model class', function (): void {
    $modelInstance = new stdClass;
    $modelInstance->id = 123;

    $itemWithStringModel = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 2,
        associatedModel: 'stdClass' // Use existing class
    );

    $itemWithObjectModel = new CartItem(
        id: 'product-2',
        name: 'Test Product 2',
        price: 100.0,
        quantity: 2,
        associatedModel: $modelInstance // Use object instance
    );

    $itemWithoutModel = new CartItem(
        id: 'product-3',
        name: 'Test Product 3',
        price: 100.0,
        quantity: 2
    );

    expect($itemWithStringModel->isAssociatedWith('stdClass'))->toBeTrue()
        ->and($itemWithStringModel->isAssociatedWith('SomeOtherClass'))->toBeFalse()
        ->and($itemWithObjectModel->isAssociatedWith('stdClass'))->toBeTrue()
        ->and($itemWithoutModel->isAssociatedWith('stdClass'))->toBeFalse();
});

it('can get associated model', function (): void {
    $modelInstance = new stdClass;
    $modelInstance->id = 123;

    $item = new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 2,
        associatedModel: $modelInstance
    );

    expect($item->getAssociatedModel())->toBe($modelInstance);
});

it('throws exception when creating item with non-existent model class', function (): void {
    expect(fn () => new CartItem(
        id: 'product-1',
        name: 'Test Product',
        price: 100.0,
        quantity: 2,
        associatedModel: 'NonExistentClass'
    ))->toThrow(\MasyukAI\Cart\Exceptions\UnknownModelException::class);
});
