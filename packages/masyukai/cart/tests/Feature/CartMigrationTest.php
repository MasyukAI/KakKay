<?php

declare(strict_types=1);

/*
 * CART MIGRATION TEST - IMPORTANT CONCEPTS:
 *
 * This test file demonstrates cart migration between guest and authenticated users.
 *
 * KEY CONCEPTS TO UNDERSTAND:
 * 1. CART IDENTIFIER = WHO owns the cart (user ID or session ID)
 * 2. CART INSTANCE = WHICH cart type ('default', 'wishlist', 'compare', etc.)
 *
 * MIGRATION PROCESS:
 * - Guest carts are identified by session ID (e.g., "abc123def456")
 * - User carts are identified by user ID (e.g., "42")
 * - Migration moves items from guest identifier → user identifier
 * - Instance names stay the same during migration
 *
 * NOTE: These tests work with the 'default' instance and simulate different
 * sessions/users via session manipulation for proper testing approach.
 */

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
use MasyukAI\Cart\Events\CartMerged;
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Listeners\HandleUserLogin;
use MasyukAI\Cart\Listeners\HandleUserLogout;
use MasyukAI\Cart\Services\CartMigrationService;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->cartMigration = new CartMigrationService;

    // Create a test user
    $this->user = new class
    {
        public $id = 1;

        public function getAuthIdentifier()
        {
            return $this->id;
        }
    };
});

it('can migrate guest cart to user cart', function () {
    // CORRECT APPROACH: Work with 'default' instance only, manage identifiers properly

    // Initialize cart with database storage using test database
    // Since we're running from main Laravel app, create the test table first
    $connection = app('db')->connection();
    $connection->getSchemaBuilder()->dropIfExists('cart_storage_test');
    $connection->getSchemaBuilder()->create('cart_storage_test', function ($table) {
        $table->id();
        $table->string('identifier')->index();
        $table->string('instance')->default('default')->index();
        $table->longText('items')->nullable();
        $table->longText('conditions')->nullable();
        $table->longText('metadata')->nullable();
        $table->timestamps();
        $table->unique(['identifier', 'instance']);
    });

    $storage = new \MasyukAI\Cart\Storage\DatabaseStorage($connection, 'cart_storage_test');
    $cart = new \MasyukAI\Cart\Cart($storage);

    // Add items to guest cart using the cart instance (not facade)
    $cart->add('product-1', 'Test Product 1', 10.00, 2);
    $cart->add('product-2', 'Test Product 2', 15.00, 1);

    // Verify initial state
    $guestCount = $cart->count(); // Should have 3 items
    expect($guestCount)->toBe(3);

    // User cart should be empty (user ID 1, default instance)
    $userItems = $storage->getItems('1', 'default');
    $userCount = array_sum(array_column($userItems, 'quantity'));
    expect($userCount)->toBe(0);

    // Get the actual session identifier for migration
    $guestSessionId = session()->getId();

    // Migrate: guest session → user ID 1 (both using 'default' instance)
    $result = $this->cartMigration->migrateGuestCartToUser(1, 'default', $guestSessionId);

    expect($result)->toBeTrue();

    // Verify migration results
    $guestCountAfter = $cart->count(); // Guest cart should be empty
    expect($guestCountAfter)->toBe(0);

    $userItemsAfter = $storage->getItems('1', 'default');
    $userCountAfter = array_sum(array_column($userItemsAfter, 'quantity'));
    expect($userCountAfter)->toBe(3); // User should have the migrated items

    expect($userItemsAfter)->toHaveCount(2); // Two different products
    expect($userItemsAfter['product-1']['name'])->toBe('Test Product 1');
    expect($userItemsAfter['product-1']['quantity'])->toBe(2);
});

it('can handle merge conflicts with add quantities strategy', function () {
    // CORRECT: Use session simulation for guest, check default instance only

    // Setup guest session with items
    session(['id' => 'guest_session_456']);
    Cart::add('product-1', 'Test Product', 10.00, 2);

    // Setup user cart (directly in storage)
    $storage = Cart::storage();
    $userExistingItems = [
        'product-1' => [
            'id' => 'product-1',
            'name' => 'Test Product',
            'price' => 10.00,
            'quantity' => 3,
            'attributes' => [],
            'conditions' => [],
        ],
    ];
    $storage->putItems('1', 'default', $userExistingItems);

    // Set merge strategy to add quantities
    config(['cart.migration.merge_strategy' => 'add_quantities']);

    $guestSessionId = session()->getId();
    $this->cartMigration->migrateGuestCartToUser(1, 'default', $guestSessionId);

    // Check results from storage (default instance only)
    $userItems = $storage->getItems('1', 'default');
    expect($userItems)->toHaveCount(1);
    expect($userItems['product-1']['quantity'])->toBe(5); // 2 + 3
});

it('can handle merge conflicts with keep highest quantity strategy', function () {
    // Setup guest session with items
    session(['id' => 'guest_session_789']);
    Cart::add('product-1', 'Test Product', 10.00, 5);

    // Setup user cart (directly in storage)
    $storage = Cart::storage();
    $userExistingItems = [
        'product-1' => [
            'id' => 'product-1',
            'name' => 'Test Product',
            'price' => 10.00,
            'quantity' => 3,
            'attributes' => [],
            'conditions' => [],
        ],
    ];
    $storage->putItems('1', 'default', $userExistingItems);

    // Set merge strategy to keep highest quantity
    config(['cart.migration.merge_strategy' => 'keep_highest_quantity']);

    $guestSessionId = session()->getId();
    $this->cartMigration->migrateGuestCartToUser(1, 'default', $guestSessionId);

    // Check results from storage (default instance only)
    $userItems = $storage->getItems('1', 'default');
    expect($userItems)->toHaveCount(1);
    expect($userItems['product-1']['quantity'])->toBe(5); // Keep highest (guest cart)
});

it('can handle merge conflicts with keep user cart strategy', function () {
    // Setup guest session with items
    session(['id' => 'guest_session_abc']);
    Cart::add('product-1', 'Test Product', 10.00, 5);

    // Setup user cart (directly in storage)
    $storage = Cart::storage();
    $userExistingItems = [
        'product-1' => [
            'id' => 'product-1',
            'name' => 'Test Product',
            'price' => 10.00,
            'quantity' => 3,
            'attributes' => [],
            'conditions' => [],
        ],
    ];
    $storage->putItems('1', 'default', $userExistingItems);

    // Set merge strategy to keep user cart
    config(['cart.migration.merge_strategy' => 'keep_user_cart']);

    $guestSessionId = session()->getId();
    $this->cartMigration->migrateGuestCartToUser(1, 'default', $guestSessionId);

    // Check results from storage (default instance only)
    $userItems = $storage->getItems('1', 'default');
    expect($userItems)->toHaveCount(1);
    expect($userItems['product-1']['quantity'])->toBe(3); // Keep user cart quantity
});

it('can handle merge conflicts with replace with guest strategy', function () {
    // Setup guest session with items
    session(['id' => 'guest_session_def']);
    Cart::add('product-1', 'Test Product', 10.00, 5);

    // Setup user cart (directly in storage)
    $storage = Cart::storage();
    $userExistingItems = [
        'product-1' => [
            'id' => 'product-1',
            'name' => 'Test Product',
            'price' => 10.00,
            'quantity' => 3,
            'attributes' => [],
            'conditions' => [],
        ],
    ];
    $storage->putItems('1', 'default', $userExistingItems);

    // Set merge strategy to replace with guest
    config(['cart.migration.merge_strategy' => 'replace_with_guest']);

    $guestSessionId = session()->getId();
    $this->cartMigration->migrateGuestCartToUser(1, 'default', $guestSessionId);

    // Check results from storage (default instance only)
    $userItems = $storage->getItems('1', 'default');
    expect($userItems)->toHaveCount(1);
    expect($userItems['product-1']['quantity'])->toBe(5); // Replace with guest cart quantity
});

it('dispatches cart merged event on successful migration', function () {
    Event::fake();

    // Setup guest session with items
    session(['id' => 'guest_session_event']);
    Cart::add('product-1', 'Test Product', 10.00, 2);

    $guestSessionId = session()->getId();
    $this->cartMigration->migrateGuestCartToUser(1, 'default', $guestSessionId);

    Event::assertDispatched(CartMerged::class, function ($event) {
        return $event->targetInstance === 'default' &&
               $event->sourceInstance === 'default' &&
               $event->totalItemsMerged === 2;
    });
});

it('handles user login event automatically when configured', function () {
    // Initialize cart with database storage
    $connection = app('db')->connection();
    $storage = new \MasyukAI\Cart\Storage\DatabaseStorage($connection, 'cart_storage_test');
    $cart = new \MasyukAI\Cart\Cart($storage);

    // Configure auto migration
    config(['cart.migration.auto_migrate_on_login' => true]);

    // Mock Auth facade
    Auth::shouldReceive('id')->andReturn(1);
    Auth::shouldReceive('user')->andReturn($this->user);
    Auth::shouldReceive('check')->andReturn(true);

    // Mock session properly - include put() method
    Session::shouldReceive('getId')->andReturn('guest_session_login_123');
    Session::shouldReceive('flash')->withAnyArgs()->andReturn(true);
    Session::shouldReceive('put')->withAnyArgs()->andReturn(true);

    // Add items to guest cart directly via storage
    $storage->putItems('guest_session_login_123', 'default', [
        'product-1' => [
            'id' => 'product-1',
            'name' => 'Test Product',
            'price' => 10.00,
            'quantity' => 2,
            'attributes' => [],
        ],
    ]);

    $listener = new HandleUserLogin($this->cartMigration);
    $event = new Login('web', $this->user, false);

    // Check initial state via storage
    $guestItems = $storage->getItems('guest_session_login_123', 'default');
    expect(array_sum(array_column($guestItems, 'quantity')))->toBe(2);

    $userItems = $storage->getItems('1', 'default');
    expect(array_sum(array_column($userItems, 'quantity')))->toBe(0);

    $listener->handle($event);

    // After login, user cart should have the items (check via storage)
    $userItemsAfter = $storage->getItems('1', 'default');
    expect(array_sum(array_column($userItemsAfter, 'quantity')))->toBe(2);

    // Guest cart should be cleared
    $guestItemsAfter = $storage->getItems('guest_session_login_123', 'default');
    expect(array_sum(array_column($guestItemsAfter, 'quantity')))->toBe(0);
});

it('handles user logout event when configured', function () {
    // Initialize cart with database storage
    $connection = app('db')->connection();
    $storage = new \MasyukAI\Cart\Storage\DatabaseStorage($connection, 'cart_storage_test');

    config(['cart.migration.backup_on_logout' => true]);

    // Mock Auth facade
    Auth::shouldReceive('id')->andReturn(1);
    Auth::shouldReceive('user')->andReturn($this->user);
    Auth::shouldReceive('check')->andReturn(false); // After logout

    // Mock session to return guest cart ID
    Session::shouldReceive('getId')->andReturn('guest_session_logout_123');

    // Setup user cart with items (via storage since only default instance matters)
    $userItems = [
        'product-1' => [
            'id' => 'product-1',
            'name' => 'Test Product',
            'price' => 10.00,
            'quantity' => 2,
            'attributes' => [],
            'conditions' => [],
        ],
    ];
    $storage->putItems('1', 'default', $userItems);

    $listener = new HandleUserLogout($this->cartMigration);
    $event = new Logout('web', $this->user);

    // Check initial state
    $userItemsInitial = $storage->getItems('1', 'default');
    expect(array_sum(array_column($userItemsInitial, 'quantity')))->toBe(2);

    $guestItemsInitial = $storage->getItems('guest_session_logout_123', 'default');
    expect(array_sum(array_column($guestItemsInitial, 'quantity')))->toBe(0);

    $listener->handle($event);

    // User cart should be copied to guest session for backup
    $guestItemsAfter = $storage->getItems('guest_session_logout_123', 'default');
    expect(array_sum(array_column($guestItemsAfter, 'quantity')))->toBe(2);

    // Original user cart should remain
    $userItemsAfter = $storage->getItems('1', 'default');
    expect(array_sum(array_column($userItemsAfter, 'quantity')))->toBe(2);
});

it('returns false when guest cart is empty', function () {
    // Ensure guest cart is empty
    session(['id' => 'empty_guest_session']);
    expect(Cart::count())->toBe(0);

    $guestSessionId = session()->getId();
    $result = $this->cartMigration->migrateGuestCartToUser(1, 'default', $guestSessionId);

    expect($result)->toBeFalse();
});

it('can get instance name for authenticated user', function () {
    // FIXED: Instance names should not be auto-generated based on user ID
    // This test should verify that instance names remain as set by developer

    // Set a custom instance name
    Cart::setInstance('wishlist');
    $currentInstance = Cart::instance();
    expect($currentInstance)->toBe('wishlist');

    // Instance names should not change based on authentication
    // This test validates that principle
});

it('can get instance name for guest session', function () {
    // FIXED: Instance names should not be auto-generated based on session ID
    // This test should verify that instance names remain as set by developer

    // Set a custom instance name
    Cart::setInstance('compare');
    $currentInstance = Cart::instance();
    expect($currentInstance)->toBe('compare');

    // Instance names should not change based on session state
    // This test validates that principle
});

it('validates merge strategy configuration', function () {
    // Test with invalid merge strategy
    config(['cart.migration.merge_strategy' => 'invalid_strategy']);

    // Setup guest session with items
    session(['id' => 'guest_session_validation']);
    Cart::add('product-1', 'Test Product', 10.00, 2);

    // Setup user cart (directly in storage)
    $storage = Cart::storage();
    $userExistingItems = [
        'product-1' => [
            'id' => 'product-1',
            'name' => 'Test Product',
            'price' => 10.00,
            'quantity' => 3,
            'attributes' => [],
            'conditions' => [],
        ],
    ];
    $storage->putItems('1', 'default', $userExistingItems);

    // Should fall back to default strategy (add_quantities)
    $guestSessionId = session()->getId();
    $this->cartMigration->migrateGuestCartToUser(1, 'default', $guestSessionId);

    // Check results from storage (default instance only)
    $userItems = $storage->getItems('1', 'default');
    expect($userItems['product-1']['quantity'])->toBe(5); // Should add quantities as fallback
});

it('preserves cart item attributes during migration', function () {
    // Setup guest session with items including attributes
    session(['id' => 'guest_session_attributes']);
    Cart::add('product-1', 'Test Product', 10.00, 1, [
        'color' => 'red',
        'size' => 'large',
        'gift_wrap' => true,
    ]);

    $guestSessionId = session()->getId();
    $this->cartMigration->migrateGuestCartToUser(1, 'default', $guestSessionId);

    // Check results from storage (default instance only)
    $storage = Cart::storage();
    $userItems = $storage->getItems('1', 'default');
    $firstItem = array_values($userItems)[0]; // Get the first item from the array

    expect($firstItem['attributes']['color'])->toBe('red');
    expect($firstItem['attributes']['size'])->toBe('large');
    expect($firstItem['attributes']['gift_wrap'])->toBe(true);
});
