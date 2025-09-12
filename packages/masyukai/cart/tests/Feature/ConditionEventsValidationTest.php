<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Events\ConditionAdded;
use MasyukAI\Cart\Events\ConditionRemoved;
use MasyukAI\Cart\Storage\SessionStorage;

it('validates ConditionAdded event works correctly', function () {
    // Set up session and events manually
    $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
    $events = new \Illuminate\Events\Dispatcher;

    $cart = new Cart(
        storage: new SessionStorage($sessionStore),
        identifier: 'test-user',
        events: $events,
        instanceName: 'condition_test',
        eventsEnabled: true
    );

    // Track dispatched events
    $dispatchedEvents = [];
    $events->listen(ConditionAdded::class, function ($event) use (&$dispatchedEvents) {
        $dispatchedEvents[] = $event;
    });

    // Add an item
    $cart->add('product-123', 'Test Product', 100.00, 1);

    // Add a discount condition
    $cart->addDiscount('summer_sale', '-20%');

    // Verify event was dispatched
    expect($dispatchedEvents)->toHaveCount(1);

    $event = $dispatchedEvents[0];
    expect($event)->toBeInstanceOf(ConditionAdded::class);
    expect($event->condition->getName())->toBe('summer_sale');
    expect($event->condition->getType())->toBe('discount');
    expect($event->condition->getValue())->toBe('-20%');
    expect($event->cart)->toBeInstanceOf(Cart::class);
    expect($event->target)->toBeNull(); // Cart-level condition
    expect($event->isItemCondition())->toBeFalse();

    // Verify impact calculation
    expect($event->getConditionImpact())->toBe(-20.00);
});

it('validates ConditionRemoved event works correctly', function () {
    // Set up session and events manually
    $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
    $events = new \Illuminate\Events\Dispatcher;

    $cart = new Cart(
        storage: new SessionStorage($sessionStore),
        identifier: 'test-user',
        events: $events,
        instanceName: 'condition_test',
        eventsEnabled: true
    );

    // Track dispatched events
    $dispatchedEvents = [];
    $events->listen(ConditionRemoved::class, function ($event) use (&$dispatchedEvents) {
        $dispatchedEvents[] = $event;
    });

    // Add an item and discount
    $cart->add('product-123', 'Test Product', 100.00, 1);
    $cart->addDiscount('big_discount', '-30%');

    // Remove the discount
    $cart->removeCondition('big_discount');

    // Verify event was dispatched
    expect($dispatchedEvents)->toHaveCount(1);

    $event = $dispatchedEvents[0];
    expect($event)->toBeInstanceOf(ConditionRemoved::class);
    expect($event->condition->getName())->toBe('big_discount');
    expect($event->condition->getType())->toBe('discount');
    expect($event->cart)->toBeInstanceOf(Cart::class);
    expect($event->target)->toBeNull(); // Cart-level condition
    expect($event->isItemCondition())->toBeFalse();

    // Verify lost savings calculation
    expect($event->getLostSavings())->toBe(30.00);
});

it('validates item-level condition events work correctly', function () {
    // Set up session and events manually
    $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
    $events = new \Illuminate\Events\Dispatcher;

    $cart = new Cart(
        storage: new SessionStorage($sessionStore),
        identifier: 'test-user',
        events: $events,
        instanceName: 'condition_test',
        eventsEnabled: true
    );

    // Track dispatched events
    $addedEvents = [];
    $removedEvents = [];
    $events->listen(ConditionAdded::class, function ($event) use (&$addedEvents) {
        $addedEvents[] = $event;
    });
    $events->listen(ConditionRemoved::class, function ($event) use (&$removedEvents) {
        $removedEvents[] = $event;
    });

    // Add an item
    $cart->add('product-123', 'Test Product', 500.00, 1);

    // Add item-level condition
    $condition = new CartCondition('bulk_discount', 'discount', 'item', '-15%');
    $cart->addItemCondition('product-123', $condition);

    // Verify add event
    expect($addedEvents)->toHaveCount(1);
    $addEvent = $addedEvents[0];
    expect($addEvent->condition->getName())->toBe('bulk_discount');
    expect($addEvent->target)->toBe('product-123');
    expect($addEvent->isItemCondition())->toBeTrue();
    expect($addEvent->getConditionImpact())->toBe(-63.75); // -15% of $425 (after cart-level calculations)

    // Remove item-level condition
    $cart->removeItemCondition('product-123', 'bulk_discount');

    // Verify remove event
    expect($removedEvents)->toHaveCount(1);
    $removeEvent = $removedEvents[0];
    expect($removeEvent->condition->getName())->toBe('bulk_discount');
    expect($removeEvent->target)->toBe('product-123');
    expect($removeEvent->isItemCondition())->toBeTrue();
    expect($removeEvent->getLostSavings())->toBe(75.00);
});

it('validates events are not dispatched when disabled', function () {
    // Set up session and events manually
    $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
    $events = new \Illuminate\Events\Dispatcher;

    $cart = new Cart(
        storage: new SessionStorage($sessionStore),
        identifier: 'test-user',
        events: $events,
        instanceName: 'no_events_test',
        eventsEnabled: false  // Events disabled
    );

    // Track dispatched events
    $dispatchedEvents = [];
    $events->listen(ConditionAdded::class, function ($event) use (&$dispatchedEvents) {
        $dispatchedEvents[] = $event;
    });

    // Add item and condition
    $cart->add('product-123', 'Test Product', 100.00, 1);
    $cart->addDiscount('test_discount', '-10%');

    // Verify no events were dispatched
    expect($dispatchedEvents)->toHaveCount(0);
});

it('validates condition events include comprehensive data', function () {
    // Set up session and events manually
    $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
    $events = new \Illuminate\Events\Dispatcher;

    $cart = new Cart(
        storage: new SessionStorage($sessionStore),
        identifier: 'test-user',
        events: $events,
        instanceName: 'data_test',
        eventsEnabled: true
    );

    // Track dispatched events
    $dispatchedEvents = [];
    $events->listen(ConditionAdded::class, function ($event) use (&$dispatchedEvents) {
        $dispatchedEvents[] = $event;
    });

    // Add items and condition
    $cart->add('product-1', 'Product 1', 100.00, 2);
    $cart->addTax('sales_tax', '8.25%');

    // Verify comprehensive event data
    expect($dispatchedEvents)->toHaveCount(1);

    $event = $dispatchedEvents[0];
    $eventData = $event->toArray();

    expect($eventData)->toHaveKey('condition');
    expect($eventData)->toHaveKey('cart');
    expect($eventData)->toHaveKey('impact');
    expect($eventData)->toHaveKey('timestamp');

    expect($eventData['condition']['name'])->toBe('sales_tax');
    expect($eventData['cart']['items_count'])->toBe(1);
    expect($eventData['cart']['total_quantity'])->toBe(2);

    // Verify the impact is calculated correctly
    expect($eventData['impact'])->toBe(16.50); // 8.25% of $200
});
