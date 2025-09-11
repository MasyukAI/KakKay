<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use MasyukAI\Cart\Storage\DatabaseStorage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('migrate', ['--force' => true]);
});

it('applies lockForUpdate when configuration is enabled', function () {
    config(['cart.database.lock_for_update' => true]);

    $storage = new DatabaseStorage(
        app('db.connection'),
        'carts'
    );

    // Test data
    $identifier = 'test-cart';
    $instance = 'default';
    $items = [
        '1' => [
            'id' => '1',
            'name' => 'Test Product',
            'price' => 100,
            'quantity' => 1,
        ],
    ];

    // First insert
    $storage->putItems($identifier, $instance, $items);

    // Verify items were stored
    $storedItems = $storage->getItems($identifier, $instance);
    expect($storedItems)->toHaveCount(1);
    expect($storedItems['1']['quantity'])->toBe(1);

    // Update items (this will use lockForUpdate if enabled)
    $items['1']['quantity'] = 2;
    $storage->putItems($identifier, $instance, $items);

    // Verify update
    $updatedItems = $storage->getItems($identifier, $instance);
    expect($updatedItems['1']['quantity'])->toBe(2);
});

it('works without lockForUpdate when configuration is disabled', function () {
    config(['cart.database.lock_for_update' => false]);

    $storage = new DatabaseStorage(
        app('db.connection'),
        'carts'
    );

    // Test data
    $identifier = 'test-cart-2';
    $instance = 'default';
    $items = [
        '1' => [
            'id' => '1',
            'name' => 'Test Product',
            'price' => 100,
            'quantity' => 1,
        ],
    ];

    // First insert
    $storage->putItems($identifier, $instance, $items);

    // Verify items were stored
    $storedItems = $storage->getItems($identifier, $instance);
    expect($storedItems)->toHaveCount(1);
    expect($storedItems['1']['quantity'])->toBe(1);

    // Update items (this will NOT use lockForUpdate)
    $items['1']['quantity'] = 3;
    $storage->putItems($identifier, $instance, $items);

    // Verify update
    $updatedItems = $storage->getItems($identifier, $instance);
    expect($updatedItems['1']['quantity'])->toBe(3);
});

it('applies lockForUpdate to condition operations', function () {
    config(['cart.database.lock_for_update' => true]);

    $storage = new DatabaseStorage(
        app('db.connection'),
        'carts'
    );

    $identifier = 'test-cart-3';
    $instance = 'default';
    $conditions = [
        'tax' => [
            'name' => 'Tax',
            'type' => 'tax',
            'target' => 'subtotal',
            'value' => '10%',
        ],
    ];

    // Store conditions
    $storage->putConditions($identifier, $instance, $conditions);

    // Verify conditions were stored
    $storedConditions = $storage->getConditions($identifier, $instance);
    expect($storedConditions)->toHaveCount(1);
    expect($storedConditions['tax']['name'])->toBe('Tax');

    // Add another condition
    $conditions['discount'] = [
        'name' => 'Discount',
        'type' => 'discount',
        'target' => 'subtotal',
        'value' => '-5%',
    ];

    $storage->putConditions($identifier, $instance, $conditions);

    // Verify both conditions
    $updatedConditions = $storage->getConditions($identifier, $instance);
    expect($updatedConditions)->toHaveCount(2);
    expect($updatedConditions['tax']['name'])->toBe('Tax');
    expect($updatedConditions['discount']['name'])->toBe('Discount');
});

it('applies lockForUpdate to metadata operations', function () {
    config(['cart.database.lock_for_update' => true]);

    $storage = new DatabaseStorage(
        app('db.connection'),
        'carts'
    );

    $identifier = 'test-cart-4';
    $instance = 'default';

    // Store metadata
    $storage->putMetadata($identifier, $instance, 'customer_notes', 'Special delivery');

    // Verify metadata was stored
    $notes = $storage->getMetadata($identifier, $instance, 'customer_notes');
    expect($notes)->toBe('Special delivery');

    // Update metadata
    $storage->putMetadata($identifier, $instance, 'customer_notes', 'Updated notes');
    $storage->putMetadata($identifier, $instance, 'promo_code', 'SAVE10');

    // Verify updated metadata
    $updatedNotes = $storage->getMetadata($identifier, $instance, 'customer_notes');
    $promoCode = $storage->getMetadata($identifier, $instance, 'promo_code');

    expect($updatedNotes)->toBe('Updated notes');
    expect($promoCode)->toBe('SAVE10');
});
