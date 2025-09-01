<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Storage\SessionStorage;

beforeEach(function () {
    // Initialize session storage for testing
    $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
    $this->sessionStorage = new SessionStorage($sessionStore);

    // Create events dispatcher
    if (! app()->bound('events')) {
        app()->singleton('events', function ($app) {
            return new \Illuminate\Events\Dispatcher($app);
        });
    }
});

it('validates instance isolation and setInstance behavior', function () {
    // CORRECTED TEST: Different instances SHOULD be isolated even with the same identifier
    // This validates the correct architecture: instances are separate namespaces for organization

    // Create a cart with default instance
    $defaultCart = new Cart(
        storage: $this->sessionStorage,
        events: app('events'),
        instanceName: 'default',
        eventsEnabled: true
    );

    // Add item to default instance
    $defaultCart->add('item-1', 'Item 1', 10.00, 1);
    expect($defaultCart->getItems())->toHaveCount(1);
    expect($defaultCart->instance())->toBe('default');

    // Create cart with different instance but SAME identifier (test_session_id)
    $wishlistCart = new Cart(
        storage: $this->sessionStorage,
        events: app('events'),
        instanceName: 'wishlist',  // Different instance name
        eventsEnabled: true
    );

    // Verify the cart has the correct instance name
    expect($wishlistCart->instance())->toBe('wishlist');

    // CRITICAL TEST: Different instances should be ISOLATED even with same identifier
    // This allows for separate cart types like: default, wishlist, saved-for-later, etc.
    expect($wishlistCart->getItems())->toHaveCount(0); // Different instance = isolated
    expect($wishlistCart->get('item-1'))->toBeNull(); // Should NOT see item from default cart

    // Add item to wishlist instance
    $wishlistCart->add('item-2', 'Item 2', 15.00, 2);
    expect($wishlistCart->getItems())->toHaveCount(1); // Only has its own item

    // Verify default cart is still unchanged
    expect($defaultCart->getItems())->toHaveCount(1); // Still only has item-1
    expect($defaultCart->get('item-2'))->toBeNull(); // Should NOT see wishlist item

    // Both carts should have different instances (this validates proper isolation)
    expect($defaultCart->instance())->not->toBe($wishlistCart->instance()); // Different instances
});

it('validates setInstance method behavior', function () {
    // Test that setInstance returns a new cart instance with different instance name
    // and ISOLATED data (different instances should not share data)

    $cart1 = new Cart(
        storage: $this->sessionStorage,
        events: app('events'),
        instanceName: 'instance_a',
        eventsEnabled: true
    );

    // Add item to first cart
    $cart1->add('item-a', 'Item A', 10.00, 1);
    expect($cart1->getItems())->toHaveCount(1);
    expect($cart1->instance())->toBe('instance_a');

    // Use setInstance to get a new cart
    $cart2 = $cart1->setInstance('instance_b');

    // The returned cart should have the new instance name
    expect($cart2->instance())->toBe('instance_b');

    // IMPORTANT: The new instance should be ISOLATED (different instances = different data)
    expect($cart2->getItems())->toHaveCount(0); // Should NOT see items from cart1
    expect($cart2->get('item-a'))->toBeNull(); // Should NOT see item-a from cart1

    // Add item to second cart
    $cart2->add('item-b', 'Item B', 20.00, 2);
    expect($cart2->getItems())->toHaveCount(1); // Only has its own item

    // First cart should still only have its own item (instances are isolated)
    expect($cart1->getItems())->toHaveCount(1); // Still only item-a
    expect($cart1->get('item-a'))->not->toBeNull(); // Still has item-a
    expect($cart1->get('item-b'))->toBeNull(); // Should NOT see item-b from cart2

    // This confirms that setInstance creates a truly separate cart instance
    // Instances are isolated namespaces for different cart types (default, wishlist, etc.)
});
