<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Storage\DatabaseStorage;
use MasyukAI\Cart\Storage\SessionStorage;

beforeEach(function () {
    // Create test table with new structure (separate from the main migration)
    if (! Schema::hasTable('carts_refactor_test')) {
        Schema::create('carts_refactor_test', function ($table) {
            $table->id();
            $table->string('identifier')->index()->comment('auth()->id() for authenticated users, session()->id() for guests');
            $table->string('instance')->default('default')->index()->comment('Cart instance name for multiple carts per identifier');
            $table->longText('items')->nullable()->comment('Serialized cart items');
            $table->longText('conditions')->nullable()->comment('Serialized cart conditions');
            $table->longText('metadata')->nullable()->comment('Serialized cart metadata');
            $table->timestamps();

            $table->unique(['identifier', 'instance']);
        });
    }

    $this->databaseStorage = new DatabaseStorage(
        database: app('db')->connection(),
        table: 'carts_refactor_test'
    );

    $this->sessionStorage = new SessionStorage(session()->driver());

    // Clean up any existing data
    DB::table('carts_refactor_test')->truncate();
    session()->flush();
});

afterEach(function () {
    // Clean up test table
    Schema::dropIfExists('carts_refactor_test');
});

describe('Refactored Cart Storage Structure', function () {
    it('stores items and conditions separately in database storage', function () {
        $cart = new Cart(
            storage: $this->databaseStorage,
            events: app('events'),
            instanceName: 'test_instance',
            eventsEnabled: false
        );

        // Mock identifier to avoid auth dependencies
        $reflection = new ReflectionClass($cart);
        $getIdentifierMethod = $reflection->getMethod('getIdentifier');
        $getIdentifierMethod->setAccessible(true);

        // Set session ID
        session()->setId('test-session-123');

        // Add items to cart
        $cart->add('product-1', 'Product 1', 10.00, 2);
        $cart->add('product-2', 'Product 2', 15.00, 1);

        // Add conditions to cart
        $taxCondition = new CartCondition(
            name: 'tax',
            type: 'tax',
            target: 'total',
            value: '10%'
        );
        $cart->addCondition($taxCondition);

        // Get the actual identifier used by cart
        $identifier = $getIdentifierMethod->invoke($cart);

        // Verify database record structure
        $record = DB::table('carts_refactor_test')
            ->where('identifier', $identifier)
            ->where('instance', 'test_instance')
            ->first();

        expect($record)->not->toBeNull();
        expect($record->identifier)->toBe($identifier);
        expect($record->instance)->toBe('test_instance');
        expect($record->items)->not->toBeNull();
        expect($record->conditions)->not->toBeNull();

        // Verify items are stored correctly (now as JSON since we use arrays)
        $items = json_decode($record->items, true);
        expect($items)->toHaveCount(2);

        // Verify conditions are stored correctly (now as JSON since we use arrays)
        $conditions = json_decode($record->conditions, true);
        expect($conditions)->toHaveCount(1);

        // Verify cart can be loaded correctly
        $newCart = new Cart(
            storage: $this->databaseStorage,
            events: app('events'),
            instanceName: 'test_instance',
            eventsEnabled: false
        );

        expect($newCart->getItems())->toHaveCount(2);
        expect($newCart->getConditions())->toHaveCount(1);
        expect($newCart->getTotalQuantity())->toBe(3);
        expect($newCart->subtotal())->toBe(35.00);
    });

    it('uses session ID for guest users', function () {
        $cart = new Cart(
            storage: $this->sessionStorage,
            events: app('events'),
            instanceName: 'guest_cart',
            eventsEnabled: false
        );

        // Set session ID
        session()->setId('guest-session-456');

        // Add item to cart
        $cart->add('guest-product', 'Guest Product', 5.00, 1);

        // The cart should store using session ID
        expect($cart->getItems())->toHaveCount(1);
        expect($cart->get('guest-product'))->not->toBeNull();
    });

    it('maintains separate storage for different instances', function () {
        $cart1 = new Cart(
            storage: $this->databaseStorage,
            events: app('events'),
            instanceName: 'cart',
            eventsEnabled: false
        );

        $cart2 = new Cart(
            storage: $this->databaseStorage,
            events: app('events'),
            instanceName: 'wishlist',
            eventsEnabled: false
        );

        // Set session ID for consistent identifier
        session()->setId('multi-instance-session');

        // Add different items to each instance
        $cart1->add('cart-item', 'Cart Item', 10.00, 1);
        $cart2->add('wishlist-item', 'Wishlist Item', 20.00, 1);

        // Get identifier from cart
        $reflection = new ReflectionClass($cart1);
        $getIdentifierMethod = $reflection->getMethod('getIdentifier');
        $getIdentifierMethod->setAccessible(true);
        $identifier = $getIdentifierMethod->invoke($cart1);

        // Verify they are stored separately
        $cartRecord = DB::table('carts_refactor_test')
            ->where('identifier', $identifier)
            ->where('instance', 'cart')
            ->first();

        $wishlistRecord = DB::table('carts_refactor_test')
            ->where('identifier', $identifier)
            ->where('instance', 'wishlist')
            ->first();

        expect($cartRecord)->not->toBeNull();
        expect($wishlistRecord)->not->toBeNull();
        expect($cartRecord->id)->not->toBe($wishlistRecord->id);

        // Verify content separation
        expect($cart1->getItems())->toHaveCount(1);
        expect($cart2->getItems())->toHaveCount(1);
        expect($cart1->has('cart-item'))->toBeTrue();
        expect($cart1->has('wishlist-item'))->toBeFalse();
        expect($cart2->has('wishlist-item'))->toBeTrue();
        expect($cart2->has('cart-item'))->toBeFalse();
    });

    it('handles conditions storage correctly', function () {
        $cart = new Cart(
            storage: $this->databaseStorage,
            events: app('events'),
            instanceName: 'conditions_test',
            eventsEnabled: false
        );

        // Set session ID
        session()->setId('conditions-test-session');

        // Add multiple conditions
        $taxCondition = new CartCondition(
            name: 'tax',
            type: 'tax',
            target: 'total',
            value: '8%'
        );

        $discountCondition = new CartCondition(
            name: 'discount',
            type: 'discount',
            target: 'total',
            value: '-10'
        );

        $cart->addCondition([$taxCondition, $discountCondition]);

        // Verify conditions are stored and can be retrieved
        expect($cart->getConditions())->toHaveCount(2);
        expect($cart->getCondition('tax'))->not->toBeNull();
        expect($cart->getCondition('discount'))->not->toBeNull();

        // Test condition removal
        $cart->removeCondition('tax');
        expect($cart->getConditions())->toHaveCount(1);
        expect($cart->getCondition('tax'))->toBeNull();
        expect($cart->getCondition('discount'))->not->toBeNull();

        // Test clear all conditions
        $cart->clearConditions();
        expect($cart->getConditions())->toHaveCount(0);
    });

    it('supports new storage interface methods', function () {
        $storage = $this->databaseStorage;

        // Test separate items and conditions storage with arrays
        $itemsArray = [
            ['id' => 'item1', 'name' => 'Test Item', 'price' => 10, 'quantity' => 1],
            ['id' => 'item2', 'name' => 'Another Item', 'price' => 15, 'quantity' => 2],
        ];
        $conditionsArray = [
            ['name' => 'tax', 'type' => 'percentage', 'target' => 'subtotal', 'value' => 10],
        ];

        $storage->putItems('test-id', 'test-instance', $itemsArray);
        $storage->putConditions('test-id', 'test-instance', $conditionsArray);

        expect($storage->getItems('test-id', 'test-instance'))->toBe($itemsArray);
        expect($storage->getConditions('test-id', 'test-instance'))->toBe($conditionsArray);

        // Test putBoth method
        $storage->putBoth('test-id-2', 'test-instance', $itemsArray, $conditionsArray);
        expect($storage->getItems('test-id-2', 'test-instance'))->toBe($itemsArray);
        expect($storage->getConditions('test-id-2', 'test-instance'))->toBe($conditionsArray);
    });
});
