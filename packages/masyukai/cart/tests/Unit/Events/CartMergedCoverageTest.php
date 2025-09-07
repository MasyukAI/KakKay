<?php

declare(strict_types=1);

use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Events\CartMerged;
use MasyukAI\Cart\Storage\SessionStorage;

beforeEach(function () {
    $sessionStore = new Store('testing', new ArraySessionHandler(120));
    $storage = new SessionStorage($sessionStore);
    $this->cart = new Cart($storage, null, 'test-instance', false);

    $sessionStore2 = new Store('testing', new ArraySessionHandler(120));
    $storage2 = new SessionStorage($sessionStore2);
    $this->sourceCart = new Cart($storage2, null, 'source-instance', false);
});

it('can be instantiated with target cart, source cart and strategy', function () {
    $event = new CartMerged($this->cart, $this->sourceCart, 5, 'add_quantities');

    expect($event->targetCart)->toBe($this->cart);
    expect($event->sourceCart)->toBe($this->sourceCart);
    expect($event->totalItemsMerged)->toBe(5);
    expect($event->mergeStrategy)->toBe('add_quantities');
    expect($event->hadConflicts)->toBeFalse();
});

it('provides toArray method', function () {
    $event = new CartMerged($this->cart, $this->sourceCart, 3, 'add_quantities');
    $array = $event->toArray();

    expect($array)->toBeArray();
    expect($array)->toHaveKeys(['target_cart', 'source_cart', 'merge_details', 'timestamp']);
    expect($array['merge_details'])->toHaveKey('strategy');
    expect($array['merge_details']['strategy'])->toBe('add_quantities');
});

it('can be serialized to JSON', function () {
    $event = new CartMerged($this->cart, $this->sourceCart, 2, 'add_quantities');

    $json = json_encode($event);
    expect($json)->toBeString();
});

it('works with different merge strategies', function () {
    $strategies = ['add_quantities', 'keep_highest', 'keep_user', 'replace_with_guest'];

    foreach ($strategies as $strategy) {
        $event = new CartMerged($this->cart, $this->sourceCart, 1, $strategy);
        expect($event->mergeStrategy)->toBe($strategy);
    }
});

it('handles carts with items', function () {
    $this->cart->add('item1', 'Product 1', 10.0, 1);
    $this->sourceCart->add('item2', 'Product 2', 20.0, 2);

    $event = new CartMerged($this->cart, $this->sourceCart, 2, 'add_quantities');

    expect($event->targetCart->getItems())->toHaveCount(1);
    expect($event->sourceCart->getItems())->toHaveCount(1);
});

it('preserves cart instance information', function () {
    $event = new CartMerged($this->cart, $this->sourceCart, 0, 'add_quantities');

    expect($event->targetCart->instance())->toBe('test-instance');
    expect($event->sourceCart->instance())->toBe('source-instance');
});

it('handles empty carts', function () {
    $event = new CartMerged($this->cart, $this->sourceCart, 0, 'add_quantities');

    expect($event->targetCart->isEmpty())->toBeTrue();
    expect($event->sourceCart->isEmpty())->toBeTrue();
});

it('works with carts containing conditions', function () {
    $this->cart->addCondition(
        new \MasyukAI\Cart\Conditions\CartCondition('tax', 'tax', 'subtotal', '+10%')
    );
    $this->sourceCart->addCondition(
        new \MasyukAI\Cart\Conditions\CartCondition('discount', 'discount', 'subtotal', '-5%')
    );

    $event = new CartMerged($this->cart, $this->sourceCart, 0, 'add_quantities');

    expect($event->targetCart->getConditions())->toHaveCount(1);
    expect($event->sourceCart->getConditions())->toHaveCount(1);
});

it('can handle conflicts flag', function () {
    $event = new CartMerged($this->cart, $this->sourceCart, 3, 'add_quantities', true);

    expect($event->hadConflicts)->toBeTrue();
    expect($event->totalItemsMerged)->toBe(3);
});
