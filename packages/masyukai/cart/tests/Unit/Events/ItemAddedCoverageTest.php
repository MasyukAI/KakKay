<?php

declare(strict_types=1);

use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Events\ItemAdded;
use MasyukAI\Cart\Models\CartItem;
use MasyukAI\Cart\Storage\SessionStorage;

beforeEach(function () {
    $sessionStore = new Store('testing', new ArraySessionHandler(120));
    $storage = new SessionStorage($sessionStore);
    $this->cart = new Cart($storage, null, 'test-instance', false);

    $this->item = new CartItem(
        id: 'test-item',
        name: 'Test Product',
        price: 99.99,
        quantity: 2
    );
});

it('can be instantiated with item and cart', function () {
    $event = new ItemAdded($this->item, $this->cart);

    expect($event->item)->toBe($this->item);
    expect($event->cart)->toBe($this->cart);
});

it('provides toArray method', function () {
    $event = new ItemAdded($this->item, $this->cart);
    $array = $event->toArray();

    expect($array)->toBeArray();
    expect($array)->toHaveKeys(['item_id', 'item_name', 'quantity', 'price', 'identifier', 'instance_name', 'timestamp']);
});

it('can be serialized to JSON', function () {
    $event = new ItemAdded($this->item, $this->cart);

    $json = json_encode($event);
    expect($json)->toBeString();
    expect($json)->toContain('test-item');
});

it('handles item with conditions', function () {
    $itemWithCondition = $this->item->addCondition(
        new \MasyukAI\Cart\Conditions\CartCondition('discount', 'discount', 'subtotal', '-10%')
    );

    $event = new ItemAdded($itemWithCondition, $this->cart);

    expect($event->item)->toBe($itemWithCondition);
    expect($event->item->getConditions())->toHaveCount(1);
});

it('works with different item quantities', function () {
    $singleItem = new CartItem('single', 'Single', 10.0, 1);
    $multipleItem = new CartItem('multiple', 'Multiple', 5.0, 10);

    $event1 = new ItemAdded($singleItem, $this->cart);
    $event2 = new ItemAdded($multipleItem, $this->cart);

    expect($event1->item->quantity)->toBe(1);
    expect($event2->item->quantity)->toBe(10);
});

it('preserves item attributes in event', function () {
    $itemWithAttrs = new CartItem(
        id: 'attr-item',
        name: 'Item with Attributes',
        price: 15.0,
        quantity: 1,
        attributes: ['color' => 'red', 'size' => 'large']
    );

    $event = new ItemAdded($itemWithAttrs, $this->cart);

    expect($event->item->getAttribute('color'))->toBe('red');
    expect($event->item->getAttribute('size'))->toBe('large');
});
