<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Events\CartConditionAdded;
use MasyukAI\Cart\Events\CartConditionRemoved;
use MasyukAI\Cart\Events\ItemConditionAdded;
use MasyukAI\Cart\Events\ItemConditionRemoved;
use MasyukAI\Cart\Facades\Cart;

test('CartConditionAdded event is dispatched when cart-level condition is added', function () {
    Event::fake();

    Cart::add('test-product', 'Test Product', 100.00, 1);

    $condition = new CartCondition(
        name: 'VAT',
        type: 'tax',
        target: 'subtotal',
        value: '10%'
    );

    Cart::addCondition($condition);

    Event::assertDispatched(CartConditionAdded::class);
});

test('CartConditionRemoved event is dispatched when cart-level condition is removed', function () {
    Event::fake();

    Cart::add('test-product', 'Test Product', 100.00, 1);

    $condition = new CartCondition(
        name: 'VAT',
        type: 'tax',
        target: 'subtotal',
        value: '10%'
    );

    Cart::addCondition($condition);
    Cart::removeCondition('VAT');

    Event::assertDispatched(CartConditionRemoved::class);
});

test('ItemConditionAdded event is dispatched when item-level condition is added', function () {
    Event::fake();

    Cart::add('test-product', 'Test Product', 100.00, 1);

    $condition = new CartCondition(
        name: 'discount',
        type: 'discount',
        target: 'subtotal',
        value: '-10'
    );

    Cart::addItemCondition('test-product', $condition);

    Event::assertDispatched(ItemConditionAdded::class);
});

test('ItemConditionRemoved event is dispatched when item-level condition is removed', function () {
    Event::fake();

    Cart::add('test-product', 'Test Product', 100.00, 1);

    $condition = new CartCondition(
        name: 'discount',
        type: 'discount',
        target: 'subtotal',
        value: '-10'
    );

    Cart::addItemCondition('test-product', $condition);
    Cart::removeItemCondition('test-product', 'discount');

    Event::assertDispatched(ItemConditionRemoved::class);
});

test('condition events contain correct cart and condition data', function () {
    Event::fake();

    Cart::add('test-product', 'Test Product', 100.00, 1);

    $condition = new CartCondition(
        name: 'VAT',
        type: 'tax',
        target: 'subtotal',
        value: '10%'
    );

    Cart::addCondition($condition);

    Event::assertDispatched(CartConditionAdded::class, function ($event) {
        return $event->condition->getName() === 'VAT'
            && $event->cart->instance() === 'default'
            && $event->cart->countItems() === 1;
    });
});
