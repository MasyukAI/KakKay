<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Models\CartItem;
use MasyukAI\Cart\Storage\SessionStorage;

describe('Comprehensive Immutability Guarantees', function () {
    beforeEach(function () {
        $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
        $sessionStorage = new SessionStorage($sessionStore);

        $this->cart = new Cart(
            storage: $sessionStorage,
            events: new \Illuminate\Events\Dispatcher,
            instanceName: 'test_immutability',
            eventsEnabled: true
        );
        $this->cart->clear();

        $this->originalItem = new CartItem('item-1', 'Original Item', 100.00, 2);
        $this->condition = new CartCondition('discount', 'discount', 'subtotal', '-20%');
    });

    describe('CartItem Immutability', function () {
        it('ensures CartItem is readonly and immutable', function () {
            $originalItem = $this->originalItem;

            // All modification methods should return new instances
            $modifiedQuantity = $originalItem->setQuantity(5);
            $modifiedName = $originalItem->setName('Modified Name');
            $modifiedPrice = $originalItem->setPrice(200.00);

            // Original item should remain unchanged
            expect($originalItem->quantity)->toBe(2);
            expect($originalItem->name)->toBe('Original Item');
            expect($originalItem->price)->toBe(100.00);

            // New instances should have modified values
            expect($modifiedQuantity->quantity)->toBe(5);
            expect($modifiedName->name)->toBe('Modified Name');
            expect($modifiedPrice->price)->toBe(200.00);

            // All should be different instances
            expect($originalItem)->not->toBe($modifiedQuantity);
            expect($originalItem)->not->toBe($modifiedName);
            expect($originalItem)->not->toBe($modifiedPrice);
        });

        it('ensures condition operations return new instances', function () {
            $originalItem = $this->originalItem;

            $withCondition = $originalItem->addCondition($this->condition);
            $withoutCondition = $withCondition->removeCondition('discount');
            $clearedConditions = $withCondition->clearConditions();

            // Original item should have no conditions
            expect($originalItem->hasConditions())->toBeFalse();
            expect($originalItem->getConditions()->isEmpty())->toBeTrue();

            // Item with condition should have the condition
            expect($withCondition->hasCondition('discount'))->toBeTrue();
            expect($withCondition->getConditions()->count())->toBe(1);

            // Item without condition should not have the condition
            expect($withoutCondition->hasCondition('discount'))->toBeFalse();

            // Cleared item should have no conditions
            expect($clearedConditions->hasConditions())->toBeFalse();

            // All should be different instances
            expect($originalItem)->not->toBe($withCondition);
            expect($withCondition)->not->toBe($withoutCondition);
            expect($withCondition)->not->toBe($clearedConditions);
        });

        it('ensures attribute operations return new instances', function () {
            $originalItem = $this->originalItem;

            $withAttribute = $originalItem->addAttribute('color', 'red');
            $withMultipleAttributes = $withAttribute->setAttributes(['size' => 'large', 'weight' => '2kg']);
            $withoutAttribute = $withAttribute->removeAttribute('color');

            // Original item should have no attributes
            expect($originalItem->attributes->isEmpty())->toBeTrue();

            // Item with attribute should have the attribute
            expect($withAttribute->hasAttribute('color'))->toBeTrue();
            expect($withAttribute->getAttribute('color'))->toBe('red');

            // Item with multiple attributes should have all
            expect($withMultipleAttributes->hasAttribute('size'))->toBeTrue();
            expect($withMultipleAttributes->hasAttribute('weight'))->toBeTrue();

            // Item without specific attribute should not have it
            expect($withoutAttribute->hasAttribute('color'))->toBeFalse();

            // All should be different instances
            expect($originalItem)->not->toBe($withAttribute);
            expect($withAttribute)->not->toBe($withMultipleAttributes);
            expect($withAttribute)->not->toBe($withoutAttribute);
        });

        it('ensures with() method creates new instance with mixed properties', function () {
            $originalItem = $this->originalItem;

            $modifiedItem = $originalItem->with([
                'name' => 'New Name',
                'quantity' => 10,
                'attributes' => ['color' => 'blue'],
                'conditions' => [$this->condition],
            ]);

            // Original should be unchanged
            expect($originalItem->name)->toBe('Original Item');
            expect($originalItem->quantity)->toBe(2);
            expect($originalItem->attributes->isEmpty())->toBeTrue();
            expect($originalItem->hasConditions())->toBeFalse();

            // Modified should have new values
            expect($modifiedItem->name)->toBe('New Name');
            expect($modifiedItem->quantity)->toBe(10);
            expect($modifiedItem->getAttribute('color'))->toBe('blue');
            expect($modifiedItem->hasCondition('discount'))->toBeTrue();

            // Should be different instances
            expect($originalItem)->not->toBe($modifiedItem);
        });

        it('ensures withQuantity() creates new instance', function () {
            $originalItem = $this->originalItem;
            $newItem = $originalItem->withQuantity(10);

            expect($originalItem->quantity)->toBe(2);
            expect($newItem->quantity)->toBe(10);
            expect($originalItem)->not->toBe($newItem);
        });
    });

    describe('Cart Operation Immutability', function () {
        it('ensures cart add operations do not mutate existing items', function () {
            $this->cart->add('item-1', 'Test Item', 100.00, 1);
            $originalItem = $this->cart->get('item-1');

            // Update via cart - should create new item instance
            $this->cart->update('item-1', ['quantity' => 5]);
            $updatedItem = $this->cart->get('item-1');

            // Items should be different instances
            expect($originalItem)->not->toBe($updatedItem);
            expect($originalItem->quantity)->toBe(1);
            // Update method adds to existing quantity, so 1 + 5 = 6
            expect($updatedItem->quantity)->toBe(6);
        });

        it('ensures condition operations on cart items maintain immutability', function () {
            $this->cart->add('item-1', 'Test Item', 100.00, 1);
            $originalItem = $this->cart->get('item-1');

            // Add condition via cart
            $this->cart->addItemCondition('item-1', $this->condition);
            $itemWithCondition = $this->cart->get('item-1');

            // Should be different instances
            expect($originalItem)->not->toBe($itemWithCondition);
            expect($originalItem->hasConditions())->toBeFalse();
            expect($itemWithCondition->hasCondition('discount'))->toBeTrue();
        });

        it('ensures bulk operations maintain immutability', function () {
            // Add items individually since addMany doesn't exist
            $this->cart->add('item-1', 'Item 1', 100.00, 1);
            $this->cart->add('item-2', 'Item 2', 200.00, 2);

            $originalItems = [
                'item-1' => $this->cart->get('item-1'),
                'item-2' => $this->cart->get('item-2'),
            ];

            // Update multiple items
            $this->cart->update('item-1', ['quantity' => 5]);
            $this->cart->update('item-2', ['quantity' => 10]);

            $updatedItems = [
                'item-1' => $this->cart->get('item-1'),
                'item-2' => $this->cart->get('item-2'),
            ];

            // All items should be different instances
            expect($originalItems['item-1'])->not->toBe($updatedItems['item-1']);
            expect($originalItems['item-2'])->not->toBe($updatedItems['item-2']);

            // Original quantities should be preserved in original instances
            expect($originalItems['item-1']->quantity)->toBe(1);
            expect($originalItems['item-2']->quantity)->toBe(2);

            // Updated quantities should be in new instances (note: update adds to existing)
            expect($updatedItems['item-1']->quantity)->toBe(6); // 1 + 5
            expect($updatedItems['item-2']->quantity)->toBe(12); // 2 + 10
        });
    });

    describe('CartCondition Immutability', function () {
        it('ensures CartCondition is readonly and immutable', function () {
            $condition = new CartCondition('test', 'discount', 'subtotal', '-10%');

            // All properties should be readonly
            expect($condition->getName())->toBe('test');
            expect($condition->getType())->toBe('discount');
            expect($condition->getTarget())->toBe('subtotal');
            expect($condition->getValue())->toBe('-10%');

            // Creating from array should create new instance
            $fromArray = CartCondition::fromArray([
                'name' => 'test2',
                'type' => 'tax',
                'target' => 'total',
                'value' => '+5%',
            ]);

            expect($fromArray)->not->toBe($condition);
            expect($fromArray->getName())->toBe('test2');
        });
    });

    describe('Collection Immutability', function () {
        it('ensures cart collections maintain immutability', function () {
            $this->cart->add('item-1', 'Item 1', 100.00, 1);
            $this->cart->add('item-2', 'Item 2', 200.00, 2);

            $originalCollection = $this->cart->getItems();
            $originalCount = $originalCollection->count();

            // Add new item
            $this->cart->add('item-3', 'Item 3', 300.00, 3);
            $newCollection = $this->cart->getItems();

            // Original collection should be unchanged
            expect($originalCollection->count())->toBe($originalCount);
            expect($originalCollection->has('item-3'))->toBeFalse();

            // New collection should have new item
            expect($newCollection->count())->toBe($originalCount + 1);
            expect($newCollection->has('item-3'))->toBeTrue();
        });

        it('ensures filtered collections are new instances', function () {
            $this->cart->add('item-1', 'Item 1', 100.00, 1, ['category' => 'electronics']);
            $this->cart->add('item-2', 'Item 2', 200.00, 2, ['category' => 'clothing']);

            $allItems = $this->cart->getItems();
            $electronicsItems = $allItems->filterByAttribute('category', 'electronics');
            $clothingItems = $allItems->filterByAttribute('category', 'clothing');

            // All should be different instances
            expect($allItems)->not->toBe($electronicsItems);
            expect($allItems)->not->toBe($clothingItems);
            expect($electronicsItems)->not->toBe($clothingItems);

            // Counts should be correct
            expect($allItems->count())->toBe(2);
            expect($electronicsItems->count())->toBe(1);
            expect($clothingItems->count())->toBe(1);
        });
    });

    describe('Deep Immutability Verification', function () {
        it('ensures nested objects maintain immutability', function () {
            $item = new CartItem(
                'item-1',
                'Test Item',
                100.00,
                1,
                ['tags' => ['red', 'large']],
                [$this->condition]
            );

            $modifiedItem = $item->with(['quantity' => 5]);

            // Original item's nested collections should be unchanged
            expect($item->attributes->get('tags'))->toBe(['red', 'large']);
            expect($item->getConditions()->first()->getName())->toBe('discount');

            // Modified item should have separate instances of collections
            expect($modifiedItem->attributes)->not->toBe($item->attributes);
            expect($modifiedItem->getConditions())->not->toBe($item->getConditions());

            // But nested content should be the same (since we didn't modify it)
            expect($modifiedItem->attributes->get('tags'))->toBe(['red', 'large']);
            expect($modifiedItem->getConditions()->first()->getName())->toBe('discount');
        });

        it('prevents mutation through reference manipulation', function () {
            $attributes = ['color' => 'red', 'sizes' => ['S', 'M', 'L']];
            $item = new CartItem('item-1', 'Test Item', 100.00, 1, $attributes);

            // Modifying original attributes array should not affect item
            $attributes['color'] = 'blue';
            $attributes['sizes'][] = 'XL';

            expect($item->getAttribute('color'))->toBe('red');
            expect($item->getAttribute('sizes'))->toBe(['S', 'M', 'L']);
        });
    });
});
