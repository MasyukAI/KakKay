<?php

declare(strict_types=1);

use MasyukAI\Cart\Storage\CacheStorage;
use Illuminate\Contracts\Cache\Repository;
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
    $key = 'test_key';
    $value = ['item1' => 'value1', 'item2' => 'value2'];
    
    $this->cache->shouldReceive('put')
        ->once()
        ->with('test_cart.test_key', $value, 3600);
        
    $this->cache->shouldReceive('get')
        ->once()
        ->with('test_cart.test_key')
        ->andReturn($value);
    
    $this->storage->put($key, $value);
    $result = $this->storage->get($key);
    
    expect($result)->toBe($value);
});

it('can check if item exists', function (): void {
    $key = 'existing_key';
    
    $this->cache->shouldReceive('has')
        ->once()
        ->with('test_cart.existing_key')
        ->andReturn(true);
    
    $exists = $this->storage->has($key);
    
    expect($exists)->toBeTrue();
});

it('returns false when item does not exist', function (): void {
    $key = 'non_existing_key';
    
    $this->cache->shouldReceive('has')
        ->once()
        ->with('test_cart.non_existing_key')
        ->andReturn(false);
    
    $exists = $this->storage->has($key);
    
    expect($exists)->toBeFalse();
});

it('can remove items from storage', function (): void {
    $key = 'item_to_remove';
    
    $this->cache->shouldReceive('forget')
        ->once()
        ->with('test_cart.item_to_remove');
    
    $this->storage->forget($key);
    
    // The expectation is verified by Mockery
    expect(true)->toBeTrue();
});

it('can flush all items when cache store supports it', function (): void {
    // Create a real class with flush method for proper method_exists behavior
    $store = new class {
        public bool $flushed = false;
        
        public function flush(): void {
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
    $store = new stdClass(); // Object without flush method
    
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
        ->with('my_prefix.test_key')
        ->andReturn('test_value');
    
    $result = $storage->get('test_key');
    
    expect($result)->toBe('test_value');
});

it('uses default ttl when not specified', function (): void {
    $cache = m::mock(Repository::class);
    $storage = new CacheStorage($cache);
    
    $cache->shouldReceive('put')
        ->once()
        ->with('cart.test_key', 'test_value', 86400); // Default 24 hours
    
    $storage->put('test_key', 'test_value');
    
    // The expectation is verified by Mockery
    expect(true)->toBeTrue();
});

it('handles null values correctly', function (): void {
    $key = 'null_value_key';
    
    $this->cache->shouldReceive('put')
        ->once()
        ->with('test_cart.null_value_key', null, 3600);
        
    $this->cache->shouldReceive('get')
        ->once()
        ->with('test_cart.null_value_key')
        ->andReturn(null);
    
    $this->storage->put($key, null);
    $result = $this->storage->get($key);
    
    expect($result)->toBeNull();
});

it('handles complex data structures', function (): void {
    $key = 'complex_data';
    $complexData = [
        'cart' => [
            'items' => [
                ['id' => 1, 'name' => 'Product 1', 'price' => 100.50],
                ['id' => 2, 'name' => 'Product 2', 'price' => 75.25]
            ],
            'conditions' => [],
            'metadata' => ['created_at' => '2024-01-01', 'user_id' => 123]
        ]
    ];
    
    $this->cache->shouldReceive('put')
        ->once()
        ->with('test_cart.complex_data', $complexData, 3600);
        
    $this->cache->shouldReceive('get')
        ->once()
        ->with('test_cart.complex_data')
        ->andReturn($complexData);
    
    $this->storage->put($key, $complexData);
    $result = $this->storage->get($key);
    
    expect($result)->toBe($complexData);
});
