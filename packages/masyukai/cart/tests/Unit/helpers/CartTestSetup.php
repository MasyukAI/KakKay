<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Storage\DatabaseStorage;
use MasyukAI\Cart\Storage\SessionStorage;

/**
 * Shared setup function for all extracted cart test files
 * Call this in beforeEach() to get access to $this->cart, $this->sessionStorage, and $this->databaseStorage
 */
function setupCartTestEnvironment()
{
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
        storage: $this->sessionStorage,
        events: new \Illuminate\Events\Dispatcher,
        instanceName: 'bulletproof_test',
        eventsEnabled: true
    );

    // Clear any existing cart data
    $this->cart->clear();
}
