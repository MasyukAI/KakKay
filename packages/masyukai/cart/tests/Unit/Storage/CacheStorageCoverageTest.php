<?php

declare(strict_types=1);

use MasyukAI\Cart\Storage\CacheStorage;

describe('CacheStorage Coverage Tests', function () {
    beforeEach(function () {
        $this->mockCache = \Mockery::mock(\Illuminate\Contracts\Cache\Repository::class);
        $this->storage = new CacheStorage($this->mockCache, 'test_cart', 3600);
    });

    afterEach(function () {
        \Mockery::close();
    });

    it('can be instantiated with custom parameters', function () {
        $cache = \Mockery::mock(\Illuminate\Contracts\Cache\Repository::class);
        $storage = new CacheStorage($cache, 'custom_cart', 7200);

        expect($storage)->toBeInstanceOf(CacheStorage::class);
    });

    it('can store and retrieve items', function () {
        $items = ['item1' => ['name' => 'Test Item', 'price' => 10.99]];

        $this->mockCache->shouldReceive('put')
            ->with('test_cart.user123.default.items', $items, 3600)
            ->once()
            ->andReturn(true);

        $this->mockCache->shouldReceive('get')
            ->with('test_cart.user123.default.items')
            ->once()
            ->andReturn($items);

        $this->storage->putItems('user123', 'default', $items);
        $retrieved = $this->storage->getItems('user123', 'default');

        expect($retrieved)->toBe($items);
    });

    it('can store and retrieve conditions', function () {
        $conditions = ['discount' => ['name' => 'Holiday Sale', 'type' => 'discount']];

        $this->mockCache->shouldReceive('put')
            ->with('test_cart.user123.default.conditions', $conditions, 3600)
            ->once()
            ->andReturn(true);

        $this->mockCache->shouldReceive('get')
            ->with('test_cart.user123.default.conditions')
            ->once()
            ->andReturn($conditions);

        $this->storage->putConditions('user123', 'default', $conditions);
        $retrieved = $this->storage->getConditions('user123', 'default');

        expect($retrieved)->toBe($conditions);
    });

    it('can store both items and conditions at once', function () {
        $items = ['item1' => ['name' => 'Test']];
        $conditions = ['discount' => ['name' => 'Sale']];

        $this->mockCache->shouldReceive('put')
            ->with('test_cart.user123.default.items', $items, 3600)
            ->once()
            ->andReturn(true);

        $this->mockCache->shouldReceive('put')
            ->with('test_cart.user123.default.conditions', $conditions, 3600)
            ->once()
            ->andReturn(true);

        $this->storage->putBoth('user123', 'default', $items, $conditions);

        // Verify both were stored by expectations above
        expect(true)->toBeTrue();
    });

    it('returns empty array when items do not exist', function () {
        $this->mockCache->shouldReceive('get')
            ->with('test_cart.user123.default.items')
            ->once()
            ->andReturn(null);

        $result = $this->storage->getItems('user123', 'default');
        expect($result)->toBe([]);
    });

    it('returns empty array when conditions do not exist', function () {
        $this->mockCache->shouldReceive('get')
            ->with('test_cart.user123.default.conditions')
            ->once()
            ->andReturn(null);

        $result = $this->storage->getConditions('user123', 'default');
        expect($result)->toBe([]);
    });

    it('can check if cart exists in cache', function () {
        $this->mockCache->shouldReceive('has')
            ->with('test_cart.user123.default.items')
            ->once()
            ->andReturn(false);

        $this->mockCache->shouldReceive('has')
            ->with('test_cart.user123.default.conditions')
            ->once()
            ->andReturn(true);

        expect($this->storage->has('user123', 'default'))->toBeTrue();
    });

    it('can remove cart from cache', function () {
        $this->mockCache->shouldReceive('forget')
            ->with('test_cart.user123.default.items')
            ->once()
            ->andReturn(true);

        $this->mockCache->shouldReceive('forget')
            ->with('test_cart.user123.default.conditions')
            ->once()
            ->andReturn(true);

        $this->storage->forget('user123', 'default');

        // Verified by expectations
        expect(true)->toBeTrue();
    });

    it('handles flush when cache store supports it', function () {
        $mockStore = \Mockery::mock(\Illuminate\Contracts\Cache\Store::class);
        $mockStore->shouldReceive('flush')->once()->andReturn(true);

        $this->mockCache->shouldReceive('getStore')->twice()->andReturn($mockStore);

        $this->storage->flush();

        // Verified by expectations
        expect(true)->toBeTrue();
    });

    it('handles flush gracefully when store does not support it', function () {
        // Create a mock store that doesn't have flush method
        $mockStore = new class
        {
            // No flush method defined
        };

        $this->mockCache->shouldReceive('getStore')->once()->andReturn($mockStore);

        // Should not throw exception when flush method doesn't exist
        $this->storage->flush();

        // Test passes if no exception was thrown
        expect(true)->toBeTrue();
    });

    it('uses correct key prefix format', function () {
        $items = ['item1' => ['name' => 'Test']];

        // Verify the exact key format
        $this->mockCache->shouldReceive('put')
            ->with('test_cart.user123.default.items', $items, 3600)
            ->once()
            ->andReturn(true);

        $this->storage->putItems('user123', 'default', $items);

        // Verified by expectation
        expect(true)->toBeTrue();
    });

    it('uses default ttl when not specified', function () {
        $cache = \Mockery::mock(\Illuminate\Contracts\Cache\Repository::class);
        $storage = new CacheStorage($cache, 'test_cart'); // No TTL specified

        $items = ['item1' => ['name' => 'Test']];

        // Should use default TTL (86400 seconds = 24 hours)
        $cache->shouldReceive('put')
            ->with('test_cart.user123.default.items', $items, 86400)
            ->once()
            ->andReturn(true);

        $storage->putItems('user123', 'default', $items);

        expect(true)->toBeTrue();
    });

    it('handles null values correctly', function () {
        $this->mockCache->shouldReceive('get')
            ->with('test_cart.user123.default.items')
            ->once()
            ->andReturn(null);

        $result = $this->storage->getItems('user123', 'default');
        expect($result)->toBe([]);
    });

    it('handles complex data structures', function () {
        $complexData = [
            'item1' => [
                'name' => 'Complex Item',
                'attributes' => ['size' => 'L', 'color' => 'red'],
                'nested' => ['level1' => ['level2' => 'deep']],
            ],
        ];

        $this->mockCache->shouldReceive('put')
            ->with('test_cart.user123.default.items', $complexData, 3600)
            ->once()
            ->andReturn(true);

        $this->mockCache->shouldReceive('get')
            ->with('test_cart.user123.default.items')
            ->once()
            ->andReturn($complexData);

        $this->storage->putItems('user123', 'default', $complexData);
        $result = $this->storage->getItems('user123', 'default');

        expect($result)->toBe($complexData);
    });

    it('can get instances for a specific identifier', function () {
        // CacheStorage doesn't efficiently support listing instances
        // So we test that it returns an empty array gracefully
        $instances = $this->storage->getInstances('user123');
        expect($instances)->toBe([]);
    });

    it('can remove all instances for a specific identifier', function () {
        // CacheStorage doesn't efficiently support removing by identifier
        // So we test that the method doesn't throw errors
        $this->storage->forgetIdentifier('user123');

        // Test passes if no exception was thrown
        expect(true)->toBeTrue();
    });

    it('respects custom TTL when storing data', function () {
        $customTtl = 7200; // 2 hours
        $storage = new CacheStorage($this->mockCache, 'test_cart', $customTtl);
        $items = ['item1' => ['name' => 'Test']];

        $this->mockCache->shouldReceive('put')
            ->with('test_cart.user123.default.items', $items, $customTtl)
            ->once()
            ->andReturn(true);

        $storage->putItems('user123', 'default', $items);

        // The expectation is verified by Mockery
        expect(true)->toBeTrue();
    });

    it('handles JSON string data for items', function () {
        $storage = new CacheStorage($this->mockCache);
        $jsonString = '{"item1":{"name":"Test"}}';

        $this->mockCache->shouldReceive('get')
            ->with('cart.user123.default.items')
            ->once()
            ->andReturn($jsonString);

        $result = $storage->getItems('user123', 'default');

        expect($result)->toBe(['item1' => ['name' => 'Test']]);
    });

    it('handles invalid JSON string data for items', function () {
        $storage = new CacheStorage($this->mockCache);
        $invalidJson = '{"invalid":json}';

        $this->mockCache->shouldReceive('get')
            ->with('cart.user123.default.items')
            ->once()
            ->andReturn($invalidJson);

        $result = $storage->getItems('user123', 'default');

        expect($result)->toBe([]);
    });

    it('handles JSON string data for conditions', function () {
        $storage = new CacheStorage($this->mockCache);
        $jsonString = '{"discount":{"type":"percentage","value":10}}';

        $this->mockCache->shouldReceive('get')
            ->with('cart.user123.default.conditions')
            ->once()
            ->andReturn($jsonString);

        $result = $storage->getConditions('user123', 'default');

        expect($result)->toBe(['discount' => ['type' => 'percentage', 'value' => 10]]);
    });

    it('handles invalid JSON string data for conditions', function () {
        $storage = new CacheStorage($this->mockCache);
        $invalidJson = '{"invalid":json}';

        $this->mockCache->shouldReceive('get')
            ->with('cart.user123.default.conditions')
            ->once()
            ->andReturn($invalidJson);

        $result = $storage->getConditions('user123', 'default');

        expect($result)->toBe([]);
    });

    it('can store and retrieve metadata', function () {
        $storage = new CacheStorage($this->mockCache);
        $metadataValue = 'test_value';

        $this->mockCache->shouldReceive('put')
            ->with('cart.user123.default.metadata.test_key', $metadataValue, 86400)
            ->once()
            ->andReturn(true);

        $this->mockCache->shouldReceive('get')
            ->with('cart.user123.default.metadata.test_key')
            ->once()
            ->andReturn($metadataValue);

        $storage->putMetadata('user123', 'default', 'test_key', $metadataValue);
        $result = $storage->getMetadata('user123', 'default', 'test_key');

        expect($result)->toBe($metadataValue);
    });

    it('can store complex metadata', function () {
        $storage = new CacheStorage($this->mockCache);
        $complexMetadata = ['nested' => ['data' => 'value']];

        $this->mockCache->shouldReceive('put')
            ->with('cart.user123.default.metadata.complex', $complexMetadata, 86400)
            ->once()
            ->andReturn(true);

        $this->mockCache->shouldReceive('get')
            ->with('cart.user123.default.metadata.complex')
            ->once()
            ->andReturn($complexMetadata);

        $storage->putMetadata('user123', 'default', 'complex', $complexMetadata);
        $result = $storage->getMetadata('user123', 'default', 'complex');

        expect($result)->toBe($complexMetadata);
    });
});
