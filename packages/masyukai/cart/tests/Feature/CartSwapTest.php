<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use MasyukAI\Cart\Services\CartMigrationService;
use MasyukAI\Cart\Facades\Cart;

/**
 * Test cart swapping functionality.
 * 
 * This tests the simple swap feature that changes cart ownership 
 * by changing identifiers without merging content.
 */

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->cartMigration = new CartMigrationService;
});

it('can swap cart ownership between identifiers', function () {
    // Create database storage for testing
    $connection = app('db')->connection();
    $connection->getSchemaBuilder()->dropIfExists('cart_storage_swap_test');
    $connection->getSchemaBuilder()->create('cart_storage_swap_test', function ($table) {
        $table->id();
        $table->string('identifier')->index();
        $table->string('instance')->default('default')->index();
        $table->longText('items')->nullable();
        $table->longText('conditions')->nullable();
        $table->longText('metadata')->nullable();
        $table->timestamps();
        $table->unique(['identifier', 'instance']);
    });

    $storage = new \MasyukAI\Cart\Storage\DatabaseStorage($connection, 'cart_storage_swap_test');
    
    // Add items to guest cart
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

    // Store data with guest identifier
    $storage->putBoth('guest_session_123', 'default', $guestItems, $guestConditions);

    // Verify guest cart exists
    $guestItemsBefore = $storage->getItems('guest_session_123', 'default');
    $guestConditionsBefore = $storage->getConditions('guest_session_123', 'default');
    expect($guestItemsBefore)->toHaveCount(2);
    expect($guestConditionsBefore)->toHaveCount(1);

    // Verify user cart is empty
    $userItemsBefore = $storage->getItems('user_42', 'default');
    expect($userItemsBefore)->toBeEmpty();

    // Perform swap
    $result = $this->cartMigration->swap('guest_session_123', 'user_42', 'default');
    expect($result)->toBeTrue();

    // Verify results after swap
    // Guest cart should be empty
    $guestItemsAfter = $storage->getItems('guest_session_123', 'default');
    $guestConditionsAfter = $storage->getConditions('guest_session_123', 'default');
    expect($guestItemsAfter)->toBeEmpty();
    expect($guestConditionsAfter)->toBeEmpty();

    // User cart should have all the original guest items
    $userItemsAfter = $storage->getItems('user_42', 'default');
    $userConditionsAfter = $storage->getConditions('user_42', 'default');
    expect($userItemsAfter)->toHaveCount(2);
    expect($userConditionsAfter)->toHaveCount(1);

    // Verify items are identical to original guest items
    expect($userItemsAfter['product-1'])->toEqual($guestItems['product-1']);
    expect($userItemsAfter['product-2'])->toEqual($guestItems['product-2']);
    expect($userConditionsAfter['discount'])->toEqual($guestConditions['discount']);
});

it('returns false when swapping non-existent cart', function () {
    // Create database storage for testing
    $connection = app('db')->connection();
    $connection->getSchemaBuilder()->dropIfExists('cart_storage_swap_test_2');
    $connection->getSchemaBuilder()->create('cart_storage_swap_test_2', function ($table) {
        $table->id();
        $table->string('identifier')->index();
        $table->string('instance')->default('default')->index();
        $table->longText('items')->nullable();
        $table->longText('conditions')->nullable();
        $table->longText('metadata')->nullable();
        $table->timestamps();
        $table->unique(['identifier', 'instance']);
    });

    $storage = new \MasyukAI\Cart\Storage\DatabaseStorage($connection, 'cart_storage_swap_test_2');
    $migration = new CartMigrationService;

    // Try to swap a non-existent cart
    $result = $migration->swap('non_existent_session', 'user_42', 'default');
    expect($result)->toBeFalse();
});

it('can swap cart ownership for specific instances', function () {
    // Create database storage for testing
    $connection = app('db')->connection();
    $connection->getSchemaBuilder()->dropIfExists('cart_storage_swap_test_3');
    $connection->getSchemaBuilder()->create('cart_storage_swap_test_3', function ($table) {
        $table->id();
        $table->string('identifier')->index();
        $table->string('instance')->default('default')->index();
        $table->longText('items')->nullable();
        $table->longText('conditions')->nullable();
        $table->longText('metadata')->nullable();
        $table->timestamps();
        $table->unique(['identifier', 'instance']);
    });

    $storage = new \MasyukAI\Cart\Storage\DatabaseStorage($connection, 'cart_storage_swap_test_3');

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

    // Perform swap for wishlist instance only
    $result = $this->cartMigration->swap('guest_session_456', 'user_99', 'wishlist');
    expect($result)->toBeTrue();

    // Verify results after swap
    // Guest wishlist should be empty
    $guestWishlistAfter = $storage->getItems('guest_session_456', 'wishlist');
    expect($guestWishlistAfter)->toBeEmpty();

    // User wishlist should have the items
    $userWishlistAfter = $storage->getItems('user_99', 'wishlist');
    expect($userWishlistAfter)->toHaveCount(1);
    expect($userWishlistAfter['product-1'])->toEqual($wishlistItems['product-1']);
});

it('can swap all instances from one identifier to another', function () {
    // Create database storage for testing
    $connection = app('db')->connection();
    $connection->getSchemaBuilder()->dropIfExists('cart_storage_swap_test_4');
    $connection->getSchemaBuilder()->create('cart_storage_swap_test_4', function ($table) {
        $table->id();
        $table->string('identifier')->index();
        $table->string('instance')->default('default')->index();
        $table->longText('items')->nullable();
        $table->longText('conditions')->nullable();
        $table->longText('metadata')->nullable();
        $table->timestamps();
        $table->unique(['identifier', 'instance']);
    });

    $storage = new \MasyukAI\Cart\Storage\DatabaseStorage($connection, 'cart_storage_swap_test_4');

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

    // Swap all instances
    $results = $this->cartMigration->swapAllInstances('guest_session_789', 'user_123');
    
    // Verify all swaps were successful
    expect($results)->toHaveKey('default');
    expect($results)->toHaveKey('wishlist');
    expect($results['default'])->toBeTrue();
    expect($results['wishlist'])->toBeTrue();

    // Verify guest carts are empty
    expect($storage->getItems('guest_session_789', 'default'))->toBeEmpty();
    expect($storage->getItems('guest_session_789', 'wishlist'))->toBeEmpty();

    // Verify user carts have the items
    expect($storage->getItems('user_123', 'default'))->toHaveCount(1);
    expect($storage->getItems('user_123', 'wishlist'))->toHaveCount(1);
});

it('can swap through cart facade', function () {
    // Create database storage for testing
    $connection = app('db')->connection();
    $connection->getSchemaBuilder()->dropIfExists('cart_storage_swap_test_5');
    $connection->getSchemaBuilder()->create('cart_storage_swap_test_5', function ($table) {
        $table->id();
        $table->string('identifier')->index();
        $table->string('instance')->default('default')->index();
        $table->longText('items')->nullable();
        $table->longText('conditions')->nullable();
        $table->longText('metadata')->nullable();
        $table->timestamps();
        $table->unique(['identifier', 'instance']);
    });

    $storage = new \MasyukAI\Cart\Storage\DatabaseStorage($connection, 'cart_storage_swap_test_5');

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

    // Swap using the cart manager (simulating Cart facade)
    $result = $cartManager->swap('guest_session_facade', 'user_facade', 'default');
    expect($result)->toBeTrue();

    // Verify results
    expect($storage->getItems('guest_session_facade', 'default'))->toBeEmpty();
    expect($storage->getItems('user_facade', 'default'))->toHaveCount(1);
});

it('can swap guest cart to user using convenience method', function () {
    // Create database storage for testing
    $connection = app('db')->connection();
    $connection->getSchemaBuilder()->dropIfExists('cart_storage_swap_test_6');
    $connection->getSchemaBuilder()->create('cart_storage_swap_test_6', function ($table) {
        $table->id();
        $table->string('identifier')->index();
        $table->string('instance')->default('default')->index();
        $table->longText('items')->nullable();
        $table->longText('conditions')->nullable();
        $table->longText('metadata')->nullable();
        $table->timestamps();
        $table->unique(['identifier', 'instance']);
    });

    $storage = new \MasyukAI\Cart\Storage\DatabaseStorage($connection, 'cart_storage_swap_test_6');

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

    // Swap using convenience method
    $result = $this->cartMigration->swapGuestCartToUser(42, 'default', $guestSessionId);
    expect($result)->toBeTrue();

    // Verify results
    expect($storage->getItems($guestSessionId, 'default'))->toBeEmpty();
    expect($storage->getItems('42', 'default'))->toHaveCount(1);
});

it('can swap all guest instances to user using convenience method', function () {
    // Create database storage for testing
    $connection = app('db')->connection();
    $connection->getSchemaBuilder()->dropIfExists('cart_storage_swap_test_7');
    $connection->getSchemaBuilder()->create('cart_storage_swap_test_7', function ($table) {
        $table->id();
        $table->string('identifier')->index();
        $table->string('instance')->default('default')->index();
        $table->longText('items')->nullable();
        $table->longText('conditions')->nullable();
        $table->longText('metadata')->nullable();
        $table->timestamps();
        $table->unique(['identifier', 'instance']);
    });

    $storage = new \MasyukAI\Cart\Storage\DatabaseStorage($connection, 'cart_storage_swap_test_7');

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

    // Swap all instances using convenience method
    $results = $this->cartMigration->swapAllGuestInstancesToUser(99, $guestSessionId);
    
    // Verify all swaps were successful
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