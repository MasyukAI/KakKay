<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Events\CartCreated;
use MasyukAI\Cart\Events\ConditionAdded;
use MasyukAI\Cart\Events\ConditionRemoved;
use MasyukAI\Cart\Events\ItemAdded;
use MasyukAI\Cart\Storage\SessionStorage;

beforeEach(function () {
    $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));

    $this->cart = new Cart(
        storage: new SessionStorage($sessionStore),
        events: new \Illuminate\Events\Dispatcher,
        instanceName: 'test_cart',
        eventsEnabled: true
    );
});

it('dispatches cart created event', function () {
    Event::fake();

    $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
    $cart = new Cart(
        storage: new SessionStorage($sessionStore),
        events: new \Illuminate\Events\Dispatcher,
        instanceName: 'new_cart',
        eventsEnabled: true
    );

    Event::assertDispatched(CartCreated::class, function (CartCreated $event) {
        return $event->cart instanceof Cart;
    });
});

it('dispatches item added event', function () {
    Event::fake();

    $this->cart->add('product-123', 'Test Product', 99.99, 2);

    Event::assertDispatched(ItemAdded::class, function (ItemAdded $event) {
        return $event->item->id === 'product-123'
            && $event->item->name === 'Test Product'
            && $event->item->getRawPrice() === 99.99
            && $event->item->quantity === 2
            && $event->cart instanceof Cart;
    });
});

it('dispatches condition added event for cart level conditions', function () {
    Event::fake();

    $this->cart->add('product-123', 'Test Product', 100.00, 1);
    Event::forgetFaked();

    $this->cart->addDiscount('summer_sale', '-20%');

    Event::assertDispatched(ConditionAdded::class, function (ConditionAdded $event) {
        return $event->condition->getName() === 'summer_sale'
            && $event->condition->getType() === 'discount'
            && $event->condition->getValue() === '-20%'
            && $event->cart instanceof Cart
            && $event->target === null  // Cart-level condition
            && ! $event->isItemCondition();
    });
});

it('dispatches condition added event for item level conditions', function () {
    Event::fake();

    $this->cart->add('product-123', 'Test Product', 100.00, 1);
    Event::forgetFaked();

    $condition = new CartCondition('bulk_discount', 'discount', 'item', '-10%');
    $this->cart->addItemCondition('product-123', $condition);

    Event::assertDispatched(ConditionAdded::class, function (ConditionAdded $event) {
        return $event->condition->getName() === 'bulk_discount'
            && $event->condition->getType() === 'discount'
            && $event->target === 'product-123'  // Item-level condition
            && $event->isItemCondition();
    });
});

it('dispatches condition removed event for cart level conditions', function () {
    Event::fake();

    $this->cart->add('product-123', 'Test Product', 100.00, 1);
    $this->cart->addDiscount('summer_sale', '-20%');
    Event::forgetFaked();

    $this->cart->removeCondition('summer_sale');

    Event::assertDispatched(ConditionRemoved::class, function (ConditionRemoved $event) {
        return $event->condition->getName() === 'summer_sale'
            && $event->condition->getType() === 'discount'
            && $event->cart instanceof Cart
            && $event->target === null  // Cart-level condition
            && ! $event->isItemCondition();
    });
});

it('dispatches condition removed event for item level conditions', function () {
    Event::fake();

    $this->cart->add('product-123', 'Test Product', 100.00, 1);
    $condition = new CartCondition('bulk_discount', 'discount', 'item', '-10%');
    $this->cart->addItemCondition('product-123', $condition);
    Event::forgetFaked();

    $this->cart->removeItemCondition('product-123', 'bulk_discount');

    Event::assertDispatched(ConditionRemoved::class, function (ConditionRemoved $event) {
        return $event->condition->getName() === 'bulk_discount'
            && $event->target === 'product-123'  // Item-level condition
            && $event->isItemCondition();
    });
});

it('calculates correct impact for condition added event', function () {
    Event::fake();

    $this->cart->add('product-123', 'Test Product', 100.00, 1);
    Event::forgetFaked();

    $this->cart->addDiscount('test_discount', '-25%');

    Event::assertDispatched(ConditionAdded::class, function (ConditionAdded $event) {
        return abs($event->getConditionImpact() + 25.00) < 0.01; // -25.00 impact
    });
});

it('calculates lost savings for condition removed event', function () {
    Event::fake();

    $this->cart->add('product-123', 'Test Product', 100.00, 1);
    $this->cart->addDiscount('big_discount', '-30%');
    Event::forgetFaked();

    $this->cart->removeCondition('big_discount');

    Event::assertDispatched(ConditionRemoved::class, function (ConditionRemoved $event) {
        return abs($event->getLostSavings() - 30.00) < 0.01; // Lost $30 in savings
    });
});

it('shows zero lost savings for non-discount removals', function () {
    Event::fake();

    $this->cart->add('product-123', 'Test Product', 100.00, 1);
    $this->cart->addTax('sales_tax', '8%');
    Event::forgetFaked();

    $this->cart->removeCondition('sales_tax');

    Event::assertDispatched(ConditionRemoved::class, function (ConditionRemoved $event) {
        return $event->getLostSavings() === 0.0; // No lost savings for tax removal
    });
});

it('does not dispatch events when disabled', function () {
    Event::fake();

    $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
    $cartWithoutEvents = new Cart(
        storage: new SessionStorage($sessionStore),
        events: new \Illuminate\Events\Dispatcher,
        instanceName: 'no_events_cart',
        eventsEnabled: false
    );

    $cartWithoutEvents->add('product-123', 'Test Product', 99.99, 1);
    $cartWithoutEvents->addDiscount('test_discount', '-10%');

    Event::assertNotDispatched(ItemAdded::class);
    Event::assertNotDispatched(ConditionAdded::class);
});

it('works with helper methods for condition events', function () {
    Event::fake();

    $this->cart->add('product-123', 'Test Product', 100.00, 1);
    Event::forgetFaked();

    // Test helper methods that create conditions internally
    $this->cart->addDiscount('helper_discount', '-20%');
    $this->cart->addTax('helper_tax', '10%');
    $this->cart->addFee('helper_fee', '5.00');

    Event::assertDispatchedTimes(ConditionAdded::class, 3);

    // Verify each helper method's condition was properly dispatched
    Event::assertDispatched(ConditionAdded::class, function (ConditionAdded $event) {
        return $event->condition->getName() === 'helper_discount'
            && $event->condition->getType() === 'discount';
    });

    Event::assertDispatched(ConditionAdded::class, function (ConditionAdded $event) {
        return $event->condition->getName() === 'helper_tax'
            && $event->condition->getType() === 'tax';
    });

    Event::assertDispatched(ConditionAdded::class, function (ConditionAdded $event) {
        return $event->condition->getName() === 'helper_fee'
            && $event->condition->getType() === 'fee';
    });
});
