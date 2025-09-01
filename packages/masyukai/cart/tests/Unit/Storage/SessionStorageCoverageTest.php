<?php

declare(strict_types=1);

use MasyukAI\Cart\Storage\SessionStorage;
use Illuminate\Session\Store;
use Illuminate\Session\ArraySessionHandler;

describe('SessionStorage Coverage Tests', function () {
    beforeEach(function () {
        $this->sessionHandler = new ArraySessionHandler(5);
        $this->session = new Store('test', $this->sessionHandler);
        $this->session->start();
        $this->storage = new SessionStorage($this->session, 'cart');
    });

    afterEach(function () {
        // Clean up session between tests
        $this->session->flush();
    });

    it('can store and retrieve items separately', function () {
        $items = [
            'item1' => ['id' => 'item1', 'name' => 'Test Item', 'price' => 10.00, 'quantity' => 2],
            'item2' => ['id' => 'item2', 'name' => 'Another Item', 'price' => 5.00, 'quantity' => 1],
        ];
        
        $this->storage->putItems('user123', 'default', $items);
        $retrieved = $this->storage->getItems('user123', 'default');
        
        expect($retrieved)->toBe($items);
    });

    it('can store and retrieve conditions separately', function () {
        $conditions = [
            'discount' => ['name' => 'discount', 'type' => 'discount', 'value' => '-10%'],
            'tax' => ['name' => 'tax', 'type' => 'charge', 'value' => '8%'],
        ];
        
        $this->storage->putConditions('user123', 'default', $conditions);
        $retrieved = $this->storage->getConditions('user123', 'default');
        
        expect($retrieved)->toBe($conditions);
    });

    it('can store both items and conditions at once', function () {
        $items = ['item1' => ['id' => 'item1', 'name' => 'Test', 'price' => 10.00]];
        $conditions = ['tax' => ['name' => 'tax', 'type' => 'charge', 'value' => '8%']];
        
        $this->storage->putBoth('user123', 'default', $items, $conditions);
        
        expect($this->storage->getItems('user123', 'default'))->toBe($items);
        expect($this->storage->getConditions('user123', 'default'))->toBe($conditions);
    });

    it('returns empty array when items do not exist', function () {
        $items = $this->storage->getItems('nonexistent', 'default');
        expect($items)->toBe([]);
    });

    it('returns empty array when conditions do not exist', function () {
        $conditions = $this->storage->getConditions('nonexistent', 'default');
        expect($conditions)->toBe([]);
    });

    it('returns empty array for instances when identifier does not exist', function () {
        $instances = $this->storage->getInstances('nonexistent');
        expect($instances)->toBe([]);
    });

    it('handles forgetIdentifier gracefully when identifier does not exist', function () {
        // This should not throw an error
        $this->storage->forgetIdentifier('nonexistent');
        expect($this->storage->getInstances('nonexistent'))->toBe([]);
    });

    it('can get all instances for a specific identifier', function () {
        $this->storage->putItems('user123', 'default', ['item1' => []]);
        $this->storage->putItems('user123', 'wishlist', ['item2' => []]);
        $this->storage->putItems('user123', 'compare', ['item3' => []]);
        
        $instances = $this->storage->getInstances('user123');
        
        expect($instances)->toBeArray()
            ->toHaveCount(3)
            ->toContain('default')
            ->toContain('wishlist')
            ->toContain('compare');
    });

    it('can remove all instances for a specific identifier', function () {
        $this->storage->putItems('user123', 'default', ['item1' => []]);
        $this->storage->putItems('user123', 'wishlist', ['item2' => []]);
        $this->storage->putItems('user456', 'default', ['item3' => []]);
        
        $this->storage->forgetIdentifier('user123');
        
        expect($this->storage->getInstances('user123'))->toBeEmpty();
        expect($this->storage->getItems('user123', 'default'))->toBeEmpty();
        expect($this->storage->getItems('user123', 'wishlist'))->toBeEmpty();
        
        // Other users should not be affected
        expect($this->storage->getItems('user456', 'default'))->not->toBeEmpty();
    });

    it('can flush all cart data from session', function () {
        $this->storage->putItems('user1', 'default', ['item1' => []]);
        $this->storage->putItems('user2', 'wishlist', ['item2' => []]);
        
        // Verify data exists before flush
        expect($this->storage->getItems('user1', 'default'))->not->toBeEmpty();
        expect($this->storage->getItems('user2', 'wishlist'))->not->toBeEmpty();
        
        $this->storage->flush();
        
        // Verify all cart data is removed
        expect($this->storage->getItems('user1', 'default'))->toBeEmpty();
        expect($this->storage->getItems('user2', 'wishlist'))->toBeEmpty();
        expect($this->storage->getInstances('user1'))->toBeEmpty();
        expect($this->storage->getInstances('user2'))->toBeEmpty();
    });

    it('handles JSON string data correctly for items', function () {
        $items = ['item1' => ['name' => 'Test']];
        
        // Manually put JSON string in session
        $key = 'cart.user123.default.items';
        $this->session->put($key, json_encode($items));
        
        $retrieved = $this->storage->getItems('user123', 'default');
        
        expect($retrieved)->toBe($items);
    });

    it('handles JSON string data correctly for conditions', function () {
        $conditions = ['tax' => ['name' => 'tax']];
        
        // Manually put JSON string in session
        $key = 'cart.user123.default.conditions';
        $this->session->put($key, json_encode($conditions));
        
        $retrieved = $this->storage->getConditions('user123', 'default');
        
        expect($retrieved)->toBe($conditions);
    });

    it('handles invalid JSON gracefully for items', function () {
        // Put invalid JSON in session
        $key = 'cart.user123.default.items';
        $this->session->put($key, 'invalid json');
        
        $retrieved = $this->storage->getItems('user123', 'default');
        
        expect($retrieved)->toBe([]);
    });

    it('handles invalid JSON gracefully for conditions', function () {
        // Put invalid JSON in session
        $key = 'cart.user123.default.conditions';
        $this->session->put($key, 'invalid json');
        
        $retrieved = $this->storage->getConditions('user123', 'default');
        
        expect($retrieved)->toBe([]);
    });

    it('uses custom key prefix when provided', function () {
        $customStorage = new SessionStorage($this->session, 'custom_prefix');
        $items = ['item1' => ['name' => 'Test']];
        
        $customStorage->putItems('user123', 'default', $items);
        
        // Check that it uses custom prefix
        $key = 'custom_prefix.user123.default.items';
        expect($this->session->has($key))->toBeTrue();
        
        $retrieved = $customStorage->getItems('user123', 'default');
        expect($retrieved)->toBe($items);
    });

});
