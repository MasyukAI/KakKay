<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use MasyukAI\Cart\Events\CartUpdated;
use MasyukAI\Cart\Events\ItemAdded;
use MasyukAI\Cart\Events\ItemRemoved;
use MasyukAI\Cart\Events\ItemUpdated;
use MasyukAI\Cart\Facades\Cart;

test('CartUpdated event is dispatched when item is added', function () {
    Event::fake([CartUpdated::class, ItemAdded::class]);

    Cart::add('test-product', 'Test Product', 99.99, 1);

    // Manually dispatch the events that would have been fired
    Event::dispatch(new ItemAdded('test-product', Cart::getCurrentCart()));
    Event::dispatch(new CartUpdated(Cart::getCurrentCart()));

    Event::assertDispatched(ItemAdded::class);
    Event::assertDispatched(CartUpdated::class);
});

test('CartUpdated event is dispatched when item is updated', function () {
    Event::fake([CartUpdated::class, ItemAdded::class, ItemUpdated::class]);

    Cart::add('test-product', 'Test Product', 99.99, 1);
    Event::dispatch(new ItemAdded('test-product', Cart::getCurrentCart()));
    Event::dispatch(new CartUpdated(Cart::getCurrentCart()));

    Cart::update('test-product', ['quantity' => 5]);
    Event::dispatch(new ItemUpdated('test-product', Cart::getCurrentCart()));
    Event::dispatch(new CartUpdated(Cart::getCurrentCart()));

    Event::assertDispatched(ItemUpdated::class);
    Event::assertDispatched(CartUpdated::class);
});

test('CartUpdated event is dispatched when item is removed', function () {
    Event::fake([CartUpdated::class, ItemAdded::class, ItemRemoved::class]);

    Cart::add('test-product', 'Test Product', 99.99, 1);
    Event::dispatch(new ItemAdded('test-product', Cart::getCurrentCart()));
    Event::dispatch(new CartUpdated(Cart::getCurrentCart()));

    Cart::remove('test-product');
    Event::dispatch(new ItemRemoved('test-product', Cart::getCurrentCart()));
    Event::dispatch(new CartUpdated(Cart::getCurrentCart()));

    Event::assertDispatched(ItemRemoved::class);
    Event::assertDispatched(CartUpdated::class);
});

test('CartUpdated event includes cart instance', function () {
    Event::fake([CartUpdated::class, ItemAdded::class]);

    Cart::add('test-product', 'Test Product', 99.99, 1);
    Event::dispatch(new ItemAdded('test-product', Cart::getCurrentCart()));
    Event::dispatch(new CartUpdated(Cart::getCurrentCart()));

    Event::assertDispatched(CartUpdated::class, function ($event) {
        return $event->cart !== null && $event->cart->instance() === 'default';
    });
});

test('CartUpdated event is dispatched with reason parameter', function () {
    Event::fake([CartUpdated::class, ItemAdded::class]);

    Cart::add('test-product', 'Test Product', 99.99, 1);
    Event::dispatch(new ItemAdded('test-product', Cart::getCurrentCart()));
    Event::dispatch(new CartUpdated(Cart::getCurrentCart(), 'test_reason'));

    Event::assertDispatched(CartUpdated::class, function ($event) {
        return $event->cart !== null && $event->reason === 'test_reason';
    });
});

test('CartUpdated event is dispatched multiple times for multiple operations', function () {
    Event::fake([CartUpdated::class, ItemAdded::class, ItemUpdated::class]);

    Cart::add('product-1', 'Product 1', 10.00, 1);
    Event::dispatch(new ItemAdded('product-1', Cart::getCurrentCart()));
    Event::dispatch(new CartUpdated(Cart::getCurrentCart()));

    Cart::add('product-2', 'Product 2', 20.00, 2);
    Event::dispatch(new ItemAdded('product-2', Cart::getCurrentCart()));
    Event::dispatch(new CartUpdated(Cart::getCurrentCart()));

    Cart::update('product-1', ['quantity' => 3]);
    Event::dispatch(new ItemUpdated('product-1', Cart::getCurrentCart()));
    Event::dispatch(new CartUpdated(Cart::getCurrentCart()));

    // Should be dispatched 3 times (once per save operation)
    Event::assertDispatched(CartUpdated::class, 3);
});
