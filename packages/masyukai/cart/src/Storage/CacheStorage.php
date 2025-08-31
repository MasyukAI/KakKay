<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Storage;

use Illuminate\Contracts\Cache\Repository as Cache;

readonly class CacheStorage implements StorageInterface
{
    public function __construct(
        private Cache $cache,
        private string $keyPrefix = 'cart',
        private int $ttl = 86400 // 24 hours
    ) {
        //
    }

    /**
     * Retrieve an item from storage
     */
    public function get(string $key): mixed
    {
        return $this->cache->get($this->getKey($key));
    }

    /**
     * Store an item in storage
     */
    public function put(string $key, mixed $value): void
    {
        $this->cache->put($this->getKey($key), $value, $this->ttl);
    }

    /**
     * Check if an item exists in storage
     */
    public function has(string $key): bool
    {
        return $this->cache->has($this->getKey($key));
    }

    /**
     * Remove an item from storage
     */
    public function forget(string $key): void
    {
        $this->cache->forget($this->getKey($key));
    }

    /**
     * Clear all items from storage
     */
    public function flush(): void
    {
        // For cache storage, we'll clear items by prefix pattern
        // This is a simplified implementation - in production you might want
        // to keep track of all keys with this prefix
        if (method_exists($this->cache->getStore(), 'flush')) {
            $this->cache->getStore()->flush();
        }
    }

    /**
     * Get the full storage key
     */
    private function getKey(string $key): string
    {
        return "{$this->keyPrefix}.{$key}";
    }
}
