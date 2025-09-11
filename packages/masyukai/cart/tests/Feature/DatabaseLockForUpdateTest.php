<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use MasyukAI\Cart\Facades\Cart;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Set up database storage for this test
    config(['cart.storage' => 'database']);

    // Run the cart migration
    $this->artisan('migrate', ['--force' => true]);

    // Clear all cart data from database if table exists
    if (DB::getSchemaBuilder()->hasTable('carts')) {
        DB::table('carts')->truncate();
    }
    
    // Ensure fresh session for each test
    session()->regenerate();
    session()->flush();
    session()->start();
    
    // Clear cart facades after session setup
    Cart::clear();
});

it('prevents race conditions with lockForUpdate enabled', function () {
    config(['cart.database.lock_for_update' => true]);

    // Add initial item to cart
    Cart::add('race-test-1', 'Product for Race Test', 100, 1);

    // Verify item was added
    expect(Cart::countItems())->toBe(1);
    expect(Cart::get('race-test-1')->quantity)->toBe(1);

    // Simulate concurrent update scenario
    // In a real scenario, this would involve multiple processes
    // Here we simulate by directly calling storage methods

    $storage = Cart::storage();
    $identifier = session()->getId();
    $instance = 'default';

    // Get current items
    $items = $storage->getItems($identifier, $instance);
    expect($items)->toHaveCount(1);

    // Modify quantity
    $items['race-test-1']['quantity'] = 2;

    // This should work with lockForUpdate - no conflicts expected
    $storage->putItems($identifier, $instance, $items);

    // Verify the update
    $updatedItems = $storage->getItems($identifier, $instance);
    expect($updatedItems['race-test-1']['quantity'])->toBe(2);

    // Also verify through Cart facade
    expect(Cart::get('race-test-1')->quantity)->toBe(2);
});

it('handles concurrent condition updates with lockForUpdate', function () {
    config(['cart.database.lock_for_update' => true]);

    // Add a condition to the cart
    Cart::addCondition(new \MasyukAI\Cart\Conditions\CartCondition('Tax', 'tax', 'subtotal', '10%'));

    // Verify condition was added
    expect(Cart::getConditions())->toHaveCount(1);

    $storage = Cart::storage();
    $identifier = session()->getId();
    $instance = 'default';

    // Add another condition through storage
    $conditions = $storage->getConditions($identifier, $instance);
    $conditions['discount'] = [
        'name' => 'Discount',
        'type' => 'discount',
        'target' => 'subtotal',
        'value' => '-5%',
    ];

    // This should work without conflicts
    $storage->putConditions($identifier, $instance, $conditions);

    // Verify both conditions exist
    expect(Cart::getConditions())->toHaveCount(2);
    expect(Cart::getCondition('Tax'))->not()->toBeNull();
});

it('metadata updates work with lockForUpdate', function () {
    config(['cart.database.lock_for_update' => true]);

    // Add an item first to create the cart record
    Cart::add('metadata-test-1', 'Product for Metadata Test', 100, 1);

    $storage = Cart::storage();
    $identifier = session()->getId();
    $instance = 'default';

    // Add metadata
    $storage->putMetadata($identifier, $instance, 'customer_notes', 'Special instructions');
    $storage->putMetadata($identifier, $instance, 'promo_code', 'SAVE10');

    // Verify metadata
    expect($storage->getMetadata($identifier, $instance, 'customer_notes'))->toBe('Special instructions');
    expect($storage->getMetadata($identifier, $instance, 'promo_code'))->toBe('SAVE10');
});

it('can disable lockForUpdate for maximum performance', function () {
    config(['cart.database.lock_for_update' => false]);

    // Add initial item to cart (use different ID than other tests)
    Cart::add('disable-test-1', 'Product for Disable Test', 100, 1);

    // Verify normal operations still work without locking
    expect(Cart::countItems())->toBe(1);

    // Update quantity (Cart::update adds to existing quantity, so 1 + 3 = 4)
    Cart::update('disable-test-1', ['quantity' => 3]);
    expect(Cart::get('disable-test-1')->quantity)->toBe(4);

    // Add condition
    Cart::addCondition(new \MasyukAI\Cart\Conditions\CartCondition('Tax', 'tax', 'subtotal', '10%'));

    expect(Cart::getConditions())->toHaveCount(1);
});
