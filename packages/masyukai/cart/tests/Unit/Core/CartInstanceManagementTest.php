<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Storage\DatabaseStorage;
use MasyukAI\Cart\Storage\SessionStorage;

beforeEach(function () {
    // Ensure events dispatcher is available
    if (! app()->bound('events')) {
        app()->singleton('events', function ($app) {
            return new \Illuminate\Events\Dispatcher($app);
        });
    }

    // Initialize session storage with array session store for testing
    $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
    $this->sessionStorage = new SessionStorage($sessionStore);

    // Only initialize database storage if db is available (some tests don't need it)
    if (app()->bound('db')) {
        try {
            $this->databaseStorage = new DatabaseStorage(
                database: app('db')->connection(),
                table: 'carts'
            );
        } catch (\Exception $e) {
            $this->databaseStorage = null; // Skip database tests if connection fails
        }
    } else {
        $this->databaseStorage = null; // Skip database tests if db not bound
    }

    // Initialize cart with session storage for most tests
    $this->cart = new Cart(
        $this->sessionStorage,
        'bulletproof_test',
        new \Illuminate\Events\Dispatcher,
        'bulletproof_test',
        true
    );

    // Clear any existing cart data
    $this->cart->clear();
});

describe('Cart instance management', function () {
    it('can switch instances using setInstance', function () {
        // Add item to default instance
        $this->cart->add('item-1', 'Item 1', 10.00, 1);
        expect($this->cart->instance())->toBe('bulletproof_test');
        expect($this->cart->getItems())->toHaveCount(1);

        // Switch to new instance
        $newCart = $this->cart->setInstance('new_instance', app('events'));
        expect($newCart->instance())->toBe('new_instance');
        expect($newCart->getItems())->toHaveCount(0); // New instance should be empty

        // Original cart should still have the item when we switch back
        $originalCart = $newCart->setInstance('bulletproof_test', app('events'));
        expect($originalCart->getItems())->toHaveCount(1);
    });

    it('provides getCurrentInstance method', function () {
        expect($this->cart->instance())->toBe('bulletproof_test');

        $newCart = $this->cart->setInstance('test_instance', app('events'));
        expect($newCart->instance())->toBe('test_instance');
    });
});
