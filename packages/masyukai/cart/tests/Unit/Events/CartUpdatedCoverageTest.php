<?php

declare(strict_types=1);

use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Events\CartUpdated;
use MasyukAI\Cart\Storage\SessionStorage;

beforeEach(function () {
    $sessionStore = new Store('testing', new ArraySessionHandler(120));
    $storage = new SessionStorage($sessionStore);
    $this->cart = new Cart($storage, null, 'test-instance', false);
});

it('can be instantiated with cart only', function () {
    $event = new CartUpdated($this->cart);

    expect($event->cart)->toBe($this->cart);
    expect($event->reason)->toBeNull();
});

it('can be instantiated with cart and reason', function () {
    $event = new CartUpdated($this->cart, 'item_added');

    expect($event->cart)->toBe($this->cart);
    expect($event->reason)->toBe('item_added');
});

it('provides toArray method', function () {
    $event = new CartUpdated($this->cart);
    $array = $event->toArray();

    expect($array)->toBeArray();
    expect($array)->toHaveKeys(['identifier', 'instance_name', 'reason', 'items_count', 'total_quantity', 'subtotal', 'total', 'conditions_count', 'timestamp']);
});

it('can be serialized to JSON', function () {
    $event = new CartUpdated($this->cart);

    $json = json_encode($event);
    expect($json)->toBeString();
});

it('works with cart containing items', function () {
    $this->cart->add('item1', 'Product 1', 10.0, 1);
    $this->cart->add('item2', 'Product 2', 20.0, 2);

    $event = new CartUpdated($this->cart, 'items_updated');

    expect($event->cart->getItems())->toHaveCount(2);
    expect($event->cart->getTotalQuantity())->toBe(3);
    expect($event->reason)->toBe('items_updated');
});

it('preserves cart instance information', function () {
    $event = new CartUpdated($this->cart);

    expect($event->cart->instance())->toBe('test-instance');
});

it('handles empty cart', function () {
    $event = new CartUpdated($this->cart);

    expect($event->cart->isEmpty())->toBeTrue();
    expect($event->cart->getTotalQuantity())->toBe(0);
});

it('works with cart containing conditions', function () {
    $this->cart->addCondition(
        new \MasyukAI\Cart\Conditions\CartCondition('tax', 'tax', 'subtotal', '+10%')
    );
    $this->cart->addCondition(
        new \MasyukAI\Cart\Conditions\CartCondition('discount', 'discount', 'subtotal', '-5%')
    );

    $event = new CartUpdated($this->cart, 'conditions_applied');

    expect($event->cart->getConditions())->toHaveCount(2);
    expect($event->reason)->toBe('conditions_applied');
});

it('captures cart totals at event time', function () {
    $this->cart->add('item1', 'Product 1', 15.50, 2);
    $this->cart->add('item2', 'Product 2', 25.75, 1);

    $event = new CartUpdated($this->cart, 'totals_calculated');

    expect($event->cart->subtotal()->getAmount())->toBeGreaterThan(0);
    expect($event->cart->getTotalQuantity())->toBe(3);
    expect($event->reason)->toBe('totals_calculated');
});

it('handles cart with item attributes', function () {
    $this->cart->add('item1', 'Product 1', 10.0, 1, ['color' => 'red', 'size' => 'large']);

    $event = new CartUpdated($this->cart, 'item_with_attributes');
    $items = $event->cart->getItems();

    expect($items['item1']->attributes->toArray())->toEqual(['color' => 'red', 'size' => 'large']);
    expect($event->reason)->toBe('item_with_attributes');
});
