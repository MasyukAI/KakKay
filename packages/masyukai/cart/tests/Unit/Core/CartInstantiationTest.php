<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Storage\DatabaseStorage;
use MasyukAI\Cart\Storage\SessionStorage;

beforeEach(function () {
    // Initialize session storage for testing
    $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
    $this->sessionStorage = new SessionStorage($sessionStore);

    // Initialize database storage if available
    if (app()->bound('db')) {
        try {
            $this->databaseStorage = new DatabaseStorage(
                database: app('db')->connection(),
                table: 'carts'
            );
        } catch (\Exception $e) {
            $this->databaseStorage = null;
        }
    } else {
        $this->databaseStorage = null;
    }

    // Initialize cart with session storage
    $this->cart = new Cart(
        storage: $this->sessionStorage,
        events: app('events'),
        identifier: 'instantiation_test',
        instanceName: 'instantiation_test',
        eventsEnabled: true
    );

    $this->cart->clear();
});

describe('Cart Core Instantiation', function () {
    it('can be instantiated with all required parameters', function () {
        expect($this->cart)->toBeInstanceOf(Cart::class);
        expect($this->cart->instance())->toBe('instantiation_test');
        expect($this->cart->getTotalQuantity())->toBe(0);
        expect($this->cart->total()->getAmount())->toBe(0);
        expect($this->cart->subtotal()->getAmount())->toBe(0);
        expect($this->cart->isEmpty())->toBeTrue();
        expect($this->cart->count())->toBe(0);
    });

    it('has empty collections by default', function () {
        expect($this->cart->getItems())->toHaveCount(0);
        expect($this->cart->getConditions())->toHaveCount(0);
        expect($this->cart->getItems())->toBeInstanceOf(\MasyukAI\Cart\Collections\CartCollection::class);
        expect($this->cart->getConditions())->toBeInstanceOf(\MasyukAI\Cart\Collections\CartConditionCollection::class);
    });

    it('enforces strict type declarations through constructor', function () {
        $reflection = new ReflectionClass(Cart::class);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        // Verify storage parameter has correct type hint
        expect($parameters[0]->getName())->toBe('storage');
        expect($parameters[0]->getType()->getName())->toBe('MasyukAI\\Cart\\Storage\\StorageInterface');

        // Verify identifier parameter has correct type hint
        expect($parameters[1]->getName())->toBe('identifier');
        expect($parameters[1]->getType()->getName())->toBe('string');

        // Verify events parameter has correct type hint
        expect($parameters[2]->getName())->toBe('events');
        expect($parameters[2]->getType()->getName())->toBe('Illuminate\\Contracts\\Events\\Dispatcher');
        expect($parameters[2]->allowsNull())->toBeTrue();

        // Verify instanceName parameter has correct type hint
        expect($parameters[3]->getName())->toBe('instanceName');
        expect($parameters[3]->getType()->getName())->toBe('string');

        // Verify eventsEnabled parameter has correct type hint
        expect($parameters[4]->getName())->toBe('eventsEnabled');
        expect($parameters[4]->getType()->getName())->toBe('bool');
    });

    it('works with events disabled', function () {
        $cartWithoutEvents = new Cart(
            storage: $this->sessionStorage,
            events: app('events'),
            identifier: 'no_events_test',
            instanceName: 'no_events_test',
            eventsEnabled: false
        );

        $cartWithoutEvents->add('test', 'Test Item', 10.0, 1);

        expect($cartWithoutEvents->getTotalQuantity())->toBe(1);
        expect($cartWithoutEvents->instance())->toBe('no_events_test');
    });
});
