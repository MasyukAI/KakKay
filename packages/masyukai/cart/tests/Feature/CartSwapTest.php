<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use MasyukAI\Cart\Services\CartMigrationService;
use MasyukAI\Cart\Facades\Cart;

/**
 * Test cart takeover functionality.
 * 
 * This tests the cart takeover feature that prioritizes preserving target carts
 * over source carts when transferring cart ownership.
 */

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->cartMigration = new CartMigrationService;
});

it('preserves target cart over source cart when target exists', function () {
    // Create database storage for testing
    $connection = app('db')->connection();
    $connection->getSchemaBuilder()->dropIfExists('cart_storage_takeover_test');
    $connection->getSchemaBuilder()->create('cart_storage_takeover_test', function ($table) {
        $table->id();
        $table->string('identifier')->index();
        $table->string('instance')->default('default')->index();
        $table->longText('items')->nullable();
        $table->longText('conditions')->nullable();
        $table->longText('metadata')->nullable();
        $table->timestamps();
        $table->unique(['identifier', 'instance']);
    });

    $storage = new \MasyukAI\Cart\Storage\DatabaseStorage($connection, 'cart_storage_takeover_test');
    
    // Add items to guest cart
    $guestItems = [
        'guest-product-1' => [
            'id' => 'guest-product-1',
            'name' => 'Guest Product 1',
            'price' => 10.00,
            'quantity' => 2,
            'attributes' => ['color' => 'red'],
            'conditions' => [],
        ],
    ];

    // Add different items to user cart (target cart that should be preserved)
    $userItems = [
        'user-product-1' => [
            'id' => 'user-product-1',
            'name' => 'User Product 1',
            'price' => 25.00,
            'quantity' => 1,
            'attributes' => ['size' => 'large'],
            'conditions' => [],
        ],
        'user-product-2' => [
            'id' => 'user-product-2',
            'name' => 'User Product 2',
            'price' => 15.00,
            'quantity' => 3,
            'attributes' => ['category' => 'electronics'],
            'conditions' => [],
        ],
    ];

    $userConditions = [
        'user_discount' => [
            'name' => 'user_discount',
            'type' => 'discount',
            'target' => 'subtotal',
            'value' => '15%',
        ]
    ];

    // Store data with both identifiers
    $storage->putBoth('guest_session_123', 'default', $guestItems, []);
    $storage->putBoth('user_42', 'default', $userItems, $userConditions);

    // Verify both carts exist initially
    expect($storage->getItems('guest_session_123', 'default'))->toHaveCount(1);
    expect($storage->getItems('user_42', 'default'))->toHaveCount(2);

    // Perform takeover (guest trying to take over user cart)
    $result = $this->cartMigration->takeoverCart('guest_session_123', 'user_42', 'default');
    expect($result)->toBeTrue();

    // Verify results after takeover:
    // Guest cart should be gone (discarded)
    $guestItemsAfter = $storage->getItems('guest_session_123', 'default');
    expect($guestItemsAfter)->toBeEmpty();

    // User cart should be preserved exactly as it was (target cart priority)
    $userItemsAfter = $storage->getItems('user_42', 'default');
    $userConditionsAfter = $storage->getConditions('user_42', 'default');
    
    expect($userItemsAfter)->toHaveCount(2);
    expect($userConditionsAfter)->toHaveCount(1);
    
    // Verify the exact content is preserved (not guest content)
    expect($userItemsAfter['user-product-1'])->toEqual($userItems['user-product-1']);
    expect($userItemsAfter['user-product-2'])->toEqual($userItems['user-product-2']);
    expect($userConditionsAfter['user_discount'])->toEqual($userConditions['user_discount']);
    
    // Guest content should NOT be in the user cart
    expect($userItemsAfter)->not->toHaveKey('guest-product-1');
});

it('transfers source cart when target cart does not exist', function () {
    // Create database storage for testing
    $connection = app('db')->connection();
    $connection->getSchemaBuilder()->dropIfExists('cart_storage_takeover_test_2');
    $connection->getSchemaBuilder()->create('cart_storage_takeover_test_2', function ($table) {
        $table->id();
        $table->string('identifier')->index();
        $table->string('instance')->default('default')->index();
        $table->longText('items')->nullable();
        $table->longText('conditions')->nullable();
        $table->longText('metadata')->nullable();
        $table->timestamps();
        $table->unique(['identifier', 'instance']);
    });

    $storage = new \MasyukAI\Cart\Storage\DatabaseStorage($connection, 'cart_storage_takeover_test_2');
    
    // Add items to guest cart only (no user cart exists)
    $guestItems = [
        'product-1' => [
            'id' => 'product-1',
            'name' => 'Test Product 1',
            'price' => 10.00,
            'quantity' => 2,
            'attributes' => ['color' => 'red'],
            'conditions' => [],
        ],
        'product-2' => [
            'id' => 'product-2',
            'name' => 'Test Product 2',
            'price' => 15.00,
            'quantity' => 1,
            'attributes' => ['size' => 'large'],
            'conditions' => [],
        ],
    ];

    $guestConditions = [
        'discount' => [
            'name' => 'discount',
            'type' => 'discount',
            'target' => 'subtotal',
            'value' => '10%',
        ]
    ];

    // Store data with guest identifier only
    $storage->putBoth('guest_session_123', 'default', $guestItems, $guestConditions);

    // Verify guest cart exists and user cart is empty
    expect($storage->getItems('guest_session_123', 'default'))->toHaveCount(2);
    expect($storage->getItems('user_42', 'default'))->toBeEmpty();

    // Perform takeover
    $result = $this->cartMigration->takeoverCart('guest_session_123', 'user_42', 'default');
    expect($result)->toBeTrue();

    // Verify results after takeover:
    // Guest cart should be empty (transferred)
    $guestItemsAfter = $storage->getItems('guest_session_123', 'default');
    $guestConditionsAfter = $storage->getConditions('guest_session_123', 'default');
    expect($guestItemsAfter)->toBeEmpty();
    expect($guestConditionsAfter)->toBeEmpty();

    // User cart should have all the original guest items (since no user cart existed)
    $userItemsAfter = $storage->getItems('user_42', 'default');
    $userConditionsAfter = $storage->getConditions('user_42', 'default');
    expect($userItemsAfter)->toHaveCount(2);
    expect($userConditionsAfter)->toHaveCount(1);

    // Verify items are identical to original guest items
    expect($userItemsAfter['product-1'])->toEqual($guestItems['product-1']);
    expect($userItemsAfter['product-2'])->toEqual($guestItems['product-2']);
    expect($userConditionsAfter['discount'])->toEqual($guestConditions['discount']);
});

it('returns false when taking over non-existent cart', function () {
    // Create database storage for testing
    $connection = app('db')->connection();
    $connection->getSchemaBuilder()->dropIfExists('cart_storage_takeover_test_3');
    $connection->getSchemaBuilder()->create('cart_storage_takeover_test_3', function ($table) {
        $table->id();
        $table->string('identifier')->index();
        $table->string('instance')->default('default')->index();
        $table->longText('items')->nullable();
        $table->longText('conditions')->nullable();
        $table->longText('metadata')->nullable();
        $table->timestamps();
        $table->unique(['identifier', 'instance']);
    });

    $storage = new \MasyukAI\Cart\Storage\DatabaseStorage($connection, 'cart_storage_takeover_test_3');
    $migration = new CartMigrationService;

    // Try to take over a non-existent cart
    $result = $migration->takeoverCart('non_existent_session', 'user_42', 'default');
    expect($result)->toBeFalse();
});

it('can take over cart ownership for specific instances', function () {
    // Create database storage for testing
    $connection = app('db')->connection();
    $connection->getSchemaBuilder()->dropIfExists('cart_storage_takeover_test_4');
    $connection->getSchemaBuilder()->create('cart_storage_takeover_test_4', function ($table) {
        $table->id();
        $table->string('identifier')->index();
        $table->string('instance')->default('default')->index();
        $table->longText('items')->nullable();
        $table->longText('conditions')->nullable();
        $table->longText('metadata')->nullable();
        $table->timestamps();
        $table->unique(['identifier', 'instance']);
    });

    $storage = new \MasyukAI\Cart\Storage\DatabaseStorage($connection, 'cart_storage_takeover_test_4');

    // Add items to guest wishlist cart
    $wishlistItems = [
        'product-1' => [
            'id' => 'product-1',
            'name' => 'Wishlist Product',
            'price' => 20.00,
            'quantity' => 1,
            'attributes' => [],
            'conditions' => [],
        ],
    ];

    // Store data with guest identifier in wishlist instance
    $storage->putBoth('guest_session_456', 'wishlist', $wishlistItems, []);

    // Verify guest wishlist exists
    $guestWishlistBefore = $storage->getItems('guest_session_456', 'wishlist');
    expect($guestWishlistBefore)->toHaveCount(1);

    // Perform takeover for wishlist instance only
    $result = $this->cartMigration->takeoverCart('guest_session_456', 'user_99', 'wishlist');
    expect($result)->toBeTrue();

    // Verify results after takeover
    // Guest wishlist should be empty
    $guestWishlistAfter = $storage->getItems('guest_session_456', 'wishlist');
    expect($guestWishlistAfter)->toBeEmpty();

    // User wishlist should have the items (since no user wishlist existed)
    $userWishlistAfter = $storage->getItems('user_99', 'wishlist');
    expect($userWishlistAfter)->toHaveCount(1);
    expect($userWishlistAfter['product-1'])->toEqual($wishlistItems['product-1']);
});

it('can take over all instances from one identifier to another', function () {
    // Create database storage for testing
    $connection = app('db')->connection();
    $connection->getSchemaBuilder()->dropIfExists('cart_storage_takeover_test_5');
    $connection->getSchemaBuilder()->create('cart_storage_takeover_test_5', function ($table) {
        $table->id();
        $table->string('identifier')->index();
        $table->string('instance')->default('default')->index();
        $table->longText('items')->nullable();
        $table->longText('conditions')->nullable();
        $table->longText('metadata')->nullable();
        $table->timestamps();
        $table->unique(['identifier', 'instance']);
    });

    $storage = new \MasyukAI\Cart\Storage\DatabaseStorage($connection, 'cart_storage_takeover_test_5');

    // Add items to multiple cart instances for guest
    $defaultItems = [
        'product-1' => [
            'id' => 'product-1',
            'name' => 'Default Product',
            'price' => 10.00,
            'quantity' => 1,
            'attributes' => [],
            'conditions' => [],
        ],
    ];

    $wishlistItems = [
        'product-2' => [
            'id' => 'product-2',
            'name' => 'Wishlist Product',
            'price' => 20.00,
            'quantity' => 1,
            'attributes' => [],
            'conditions' => [],
        ],
    ];

    // Store data in multiple instances
    $storage->putBoth('guest_session_789', 'default', $defaultItems, []);
    $storage->putBoth('guest_session_789', 'wishlist', $wishlistItems, []);

    // Verify guest carts exist
    expect($storage->getItems('guest_session_789', 'default'))->toHaveCount(1);
    expect($storage->getItems('guest_session_789', 'wishlist'))->toHaveCount(1);

    // Take over all instances
    $results = $this->cartMigration->takeoverAllInstances('guest_session_789', 'user_123');
    
    // Verify all takeovers were successful
    expect($results)->toHaveKey('default');
    expect($results)->toHaveKey('wishlist');
    expect($results['default'])->toBeTrue();
    expect($results['wishlist'])->toBeTrue();

    // Verify guest carts are empty
    expect($storage->getItems('guest_session_789', 'default'))->toBeEmpty();
    expect($storage->getItems('guest_session_789', 'wishlist'))->toBeEmpty();

    // Verify user carts have the items (since no user carts existed)
    expect($storage->getItems('user_123', 'default'))->toHaveCount(1);
    expect($storage->getItems('user_123', 'wishlist'))->toHaveCount(1);
});

it('can take over through cart facade', function () {
    // Create database storage for testing
    $connection = app('db')->connection();
    $connection->getSchemaBuilder()->dropIfExists('cart_storage_takeover_test_6');
    $connection->getSchemaBuilder()->create('cart_storage_takeover_test_6', function ($table) {
        $table->id();
        $table->string('identifier')->index();
        $table->string('instance')->default('default')->index();
        $table->longText('items')->nullable();
        $table->longText('conditions')->nullable();
        $table->longText('metadata')->nullable();
        $table->timestamps();
        $table->unique(['identifier', 'instance']);
    });

    $storage = new \MasyukAI\Cart\Storage\DatabaseStorage($connection, 'cart_storage_takeover_test_6');

    // Add items to guest cart
    $guestItems = [
        'product-1' => [
            'id' => 'product-1',
            'name' => 'Facade Test Product',
            'price' => 25.00,
            'quantity' => 3,
            'attributes' => [],
            'conditions' => [],
        ],
    ];

    // Store data with guest identifier
    $storage->putBoth('guest_session_facade', 'default', $guestItems, []);

    // Create a cart manager with our test storage
    $cartManager = new \MasyukAI\Cart\CartManager($storage);

    // Verify guest cart exists
    expect($storage->getItems('guest_session_facade', 'default'))->toHaveCount(1);

    // Take over using the cart manager (simulating Cart facade)
    $result = $cartManager->takeoverCart('guest_session_facade', 'user_facade', 'default');
    expect($result)->toBeTrue();

    // Verify results
    expect($storage->getItems('guest_session_facade', 'default'))->toBeEmpty();
    expect($storage->getItems('user_facade', 'default'))->toHaveCount(1);
});

it('can take over guest cart using convenience method', function () {
    // Create database storage for testing
    $connection = app('db')->connection();
    $connection->getSchemaBuilder()->dropIfExists('cart_storage_takeover_test_7');
    $connection->getSchemaBuilder()->create('cart_storage_takeover_test_7', function ($table) {
        $table->id();
        $table->string('identifier')->index();
        $table->string('instance')->default('default')->index();
        $table->longText('items')->nullable();
        $table->longText('conditions')->nullable();
        $table->longText('metadata')->nullable();
        $table->timestamps();
        $table->unique(['identifier', 'instance']);
    });

    $storage = new \MasyukAI\Cart\Storage\DatabaseStorage($connection, 'cart_storage_takeover_test_7');

    // Set up guest session manually
    session(['id' => 'guest_convenience_test']);
    
    // Add items to guest cart
    $guestItems = [
        'product-1' => [
            'id' => 'product-1',
            'name' => 'Convenience Test Product',
            'price' => 30.00,
            'quantity' => 2,
            'attributes' => [],
            'conditions' => [],
        ],
    ];

    // Store data with guest session
    $guestSessionId = session()->getId();
    $storage->putBoth($guestSessionId, 'default', $guestItems, []);

    // Verify guest cart exists
    expect($storage->getItems($guestSessionId, 'default'))->toHaveCount(1);

    // Take over using convenience method
    $result = $this->cartMigration->takeoverGuestCart(42, 'default', $guestSessionId);
    expect($result)->toBeTrue();

    // Verify results
    expect($storage->getItems($guestSessionId, 'default'))->toBeEmpty();
    expect($storage->getItems('42', 'default'))->toHaveCount(1);
});

it('can take over all guest instances using convenience method', function () {
    // Create database storage for testing
    $connection = app('db')->connection();
    $connection->getSchemaBuilder()->dropIfExists('cart_storage_takeover_test_8');
    $connection->getSchemaBuilder()->create('cart_storage_takeover_test_8', function ($table) {
        $table->id();
        $table->string('identifier')->index();
        $table->string('instance')->default('default')->index();
        $table->longText('items')->nullable();
        $table->longText('conditions')->nullable();
        $table->longText('metadata')->nullable();
        $table->timestamps();
        $table->unique(['identifier', 'instance']);
    });

    $storage = new \MasyukAI\Cart\Storage\DatabaseStorage($connection, 'cart_storage_takeover_test_8');

    // Set up guest session manually
    session(['id' => 'guest_all_instances_test']);
    
    // Add items to multiple guest cart instances
    $defaultItems = [
        'product-1' => [
            'id' => 'product-1',
            'name' => 'Default Product',
            'price' => 10.00,
            'quantity' => 1,
            'attributes' => [],
            'conditions' => [],
        ],
    ];

    $wishlistItems = [
        'product-2' => [
            'id' => 'product-2',
            'name' => 'Wishlist Product',
            'price' => 20.00,
            'quantity' => 1,
            'attributes' => [],
            'conditions' => [],
        ],
    ];

    $guestSessionId = session()->getId();
    $storage->putBoth($guestSessionId, 'default', $defaultItems, []);
    $storage->putBoth($guestSessionId, 'wishlist', $wishlistItems, []);

    // Verify guest carts exist
    expect($storage->getItems($guestSessionId, 'default'))->toHaveCount(1);
    expect($storage->getItems($guestSessionId, 'wishlist'))->toHaveCount(1);

    // Take over all instances using convenience method
    $results = $this->cartMigration->takeoverAllGuestInstances(99, $guestSessionId);
    
    // Verify all takeovers were successful
    expect($results)->toHaveKey('default');
    expect($results)->toHaveKey('wishlist');
    expect($results['default'])->toBeTrue();
    expect($results['wishlist'])->toBeTrue();

    // Verify guest carts are empty
    expect($storage->getItems($guestSessionId, 'default'))->toBeEmpty();
    expect($storage->getItems($guestSessionId, 'wishlist'))->toBeEmpty();

    // Verify user carts have the items
    expect($storage->getItems('99', 'default'))->toHaveCount(1);
    expect($storage->getItems('99', 'wishlist'))->toHaveCount(1);
});