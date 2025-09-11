<?php

declare(strict_types=1);

use MasyukAI\Cart\Storage\DatabaseStorage;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

// Use RefreshDatabase trait to clean database between tests
uses(RefreshDatabase::class);

describe('DatabaseStorage Refactoring Tests', function () {
    beforeEach(function () {
        $this->storage = new DatabaseStorage(app(DatabaseManager::class)->connection());
    });

    it('can store and retrieve items using refactored methods', function () {
        $items = [
            'item1' => ['name' => 'Test Item 1', 'price' => 100],
            'item2' => ['name' => 'Test Item 2', 'price' => 200],
        ];

        $this->storage->putItems('test-user', 'default', $items);
        $retrieved = $this->storage->getItems('test-user', 'default');

        expect($retrieved)->toBe($items);
    });

    it('can store and retrieve conditions using refactored methods', function () {
        $conditions = [
            'tax' => ['type' => 'percentage', 'value' => 8.5],
            'discount' => ['type' => 'fixed', 'value' => 50],
        ];

        $this->storage->putConditions('test-user', 'default', $conditions);
        $retrieved = $this->storage->getConditions('test-user', 'default');

        expect($retrieved)->toBe($conditions);
    });

    it('can store both items and conditions using refactored method', function () {
        $items = [
            'item1' => ['name' => 'Test Item', 'price' => 100],
        ];
        $conditions = [
            'tax' => ['type' => 'percentage', 'value' => 8.5],
        ];

        $this->storage->putBoth('test-user', 'default', $items, $conditions);

        $retrievedItems = $this->storage->getItems('test-user', 'default');
        $retrievedConditions = $this->storage->getConditions('test-user', 'default');

        expect($retrievedItems)->toBe($items);
        expect($retrievedConditions)->toBe($conditions);
    });

    it('can store and retrieve metadata using refactored methods', function () {
        $this->storage->putMetadata('test-user', 'default', 'last_updated', '2024-01-01');
        $this->storage->putMetadata('test-user', 'default', 'user_preferences', ['theme' => 'dark']);

        $lastUpdated = $this->storage->getMetadata('test-user', 'default', 'last_updated');
        $preferences = $this->storage->getMetadata('test-user', 'default', 'user_preferences');

        expect($lastUpdated)->toBe('2024-01-01');
        expect($preferences)->toBe(['theme' => 'dark']);
    });

    it('handles empty data gracefully', function () {
        $items = $this->storage->getItems('non-existent', 'default');
        $conditions = $this->storage->getConditions('non-existent', 'default');
        $metadata = $this->storage->getMetadata('non-existent', 'default', 'key');

        expect($items)->toBe([]);
        expect($conditions)->toBe([]);
        expect($metadata)->toBeNull();
    });

    it('validates data size correctly', function () {
        // Test that large data triggers validation
        $largeItems = [];
        for ($i = 0; $i < 2000; $i++) {
            $largeItems["item$i"] = ['name' => str_repeat('x', 1000), 'price' => $i];
        }

        expect(fn() => $this->storage->putItems('test-user', 'default', $largeItems))
            ->toThrow(\InvalidArgumentException::class);
    });
});
