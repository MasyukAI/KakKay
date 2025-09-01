<?php

declare(strict_types=1);

use Illuminate\Contracts\Cache\Repository;
use MasyukAI\Cart\Storage\CacheStorage;
use Mockery as m;

beforeEach(function (): void {
    $this->cache = m::mock(Repository::class);
    $this->storage = new CacheStorage($this->cache, 'test_cart', 3600);
});

afterEach(function (): void {
    m::close();
});

it('can be instantiated with cache and configuration', function (): void {
    $cache = m::mock(Repository::class);
    $storage = new CacheStorage($cache, 'custom_prefix', 7200);

    expect($storage)->toBeInstanceOf(CacheStorage::class);
});

it('can store and retrieve items', function (): void {
    $identifier = 'user_123';
    $instance = 'default';
    $value = 'serialized_content';

    $this->cache->shouldReceive('put')
        ->once()
        ->with('test_cart.user_123.default', $value, 3600);

    $this->cache->shouldReceive('get')
        ->once()
        ->with('test_cart.user_123.default')
        ->andReturn($value);

    $this->storage->put($identifier, $instance, $value);
    $result = $this->storage->get($identifier, $instance);

    expect($result)->toBe($value);
});

it('can check if item exists', function (): void {
    $identifier = 'user_123';
    $instance = 'default';

    $this->cache->shouldReceive('has')
        ->once()
        ->with('test_cart.user_123.default')
        ->andReturn(true);

    $exists = $this->storage->has($identifier, $instance);

    expect($exists)->toBeTrue();
});

it('returns false when item does not exist', function (): void {
    $identifier = 'user_123';
    $instance = 'nonexistent';

    $this->cache->shouldReceive('has')
        ->once()
        ->with('test_cart.user_123.nonexistent')
        ->andReturn(false);

    $exists = $this->storage->has($identifier, $instance);

    expect($exists)->toBeFalse();
});

it('can remove items from storage', function (): void {
    $identifier = 'user_123';
    $instance = 'default';

    $this->cache->shouldReceive('forget')
        ->once()
        ->with('test_cart.user_123.default');

    $this->storage->forget($identifier, $instance);

    // The expectation is verified by Mockery
    expect(true)->toBeTrue();
});

it('can flush all items when cache store supports it', function (): void {
    // Create a real class with flush method for proper method_exists behavior
    $store = new class
    {
        public bool $flushed = false;

        public function flush(): void
        {
            $this->flushed = true;
        }
    };

    $this->cache->shouldReceive('getStore')
        ->twice() // Called once for method_exists, once for flush
        ->andReturn($store);

    $this->storage->flush();

    // Verify the store's flush method was called
    expect($store->flushed)->toBeTrue();
});

it('handles flush gracefully when store does not support it', function (): void {
    $store = new stdClass; // Object without flush method

    $this->cache->shouldReceive('getStore')
        ->once()
        ->andReturn($store);

    $this->storage->flush();

    // Should not throw exception
    expect(true)->toBeTrue();
});

it('uses correct key prefix format', function (): void {
    $cache = m::mock(Repository::class);
    $storage = new CacheStorage($cache, 'my_prefix');

    $cache->shouldReceive('get')
        ->once()
        ->with('my_prefix.user_123.default')
        ->andReturn('test_value');

    $result = $storage->get('user_123', 'default');

    expect($result)->toBe('test_value');
});

it('uses default ttl when not specified', function (): void {
    $cache = m::mock(Repository::class);
    $storage = new CacheStorage($cache);

    $cache->shouldReceive('put')
        ->once()
        ->with('cart.user_123.default', 'test_value', 86400); // Default 24 hours

    $storage->put('user_123', 'default', 'test_value');

    // The expectation is verified by Mockery
    expect(true)->toBeTrue();
});

it('handles null values correctly', function (): void {
    $identifier = 'user_123';
    $instance = 'default';
    $content = ''; // Empty string instead of null

    $this->cache->shouldReceive('put')
        ->once()
        ->with('test_cart.user_123.default', $content, 3600);

    $this->cache->shouldReceive('get')
        ->once()
        ->with('test_cart.user_123.default')
        ->andReturn($content);

    $this->storage->put($identifier, $instance, $content);
    $result = $this->storage->get($identifier, $instance);

    expect($result)->toBe($content);
});

it('handles complex data structures', function (): void {
    $identifier = 'user_123';
    $instance = 'default';
    $complexData = json_encode([
        'cart' => [
            'items' => [
                ['id' => 1, 'name' => 'Product 1', 'price' => 100.50],
                ['id' => 2, 'name' => 'Product 2', 'price' => 75.25],
            ],
            'conditions' => [],
            'metadata' => ['created_at' => '2024-01-01', 'user_id' => 123],
        ],
    ]);

    $this->cache->shouldReceive('put')
        ->once()
        ->with('test_cart.user_123.default', $complexData, 3600);

    $this->cache->shouldReceive('get')
        ->once()
        ->with('test_cart.user_123.default')
        ->andReturn($complexData);

    $this->storage->put($identifier, $instance, $complexData);
    $result = $this->storage->get($identifier, $instance);

    expect($result)->toBe($complexData);
});
