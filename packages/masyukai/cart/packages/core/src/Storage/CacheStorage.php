<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Storage;

use Illuminate\Contracts\Cache\Repository as Cache;

readonly class CacheStorage implements StorageInterface
{
    public function __construct(
        private Cache $cache,
        private string $keyPrefix = 'cart',
        private int $ttl = 86400, // 24 hours
        private bool $useLocking = false, // Enable for multi-server setups with shared cache
        private int $lockTimeout = 5 // Lock timeout in seconds
    ) {
        //
    }

    /**
     * Check if cart exists in storage
     */
    public function has(string $identifier, string $instance): bool
    {
        // Check if either items or conditions exist for this cart
        return $this->cache->has($this->getItemsKey($identifier, $instance))
            || $this->cache->has($this->getConditionsKey($identifier, $instance));
    }

    /**
     * Remove cart from storage
     */
    public function forget(string $identifier, string $instance): void
    {
        $this->cache->forget($this->getItemsKey($identifier, $instance));
        $this->cache->forget($this->getConditionsKey($identifier, $instance));
    }

    /**
     * Clear all carts from storage
     */
    public function flush(): void
    {
        // For cache storage, we'll clear all items
        // In production you might want to use cache tags for more granular control
        if (method_exists($this->cache->getStore(), 'flush')) {
            $this->cache->getStore()->flush();
        }
    }

    /**
     * Get all instances for a specific identifier
     */
    public function getInstances(string $identifier): array
    {
        // This is a limitation of cache storage - we can't easily list keys
        // In production, you might want to maintain a separate index
        // For now, return empty array
        return [];
    }

    /**
     * Remove all instances for a specific identifier
     */
    public function forgetIdentifier(string $identifier): void
    {
        // This is a limitation of cache storage - we can't easily list keys
        // In production, you might want to maintain a separate index
        // For now, we can't efficiently remove all instances for an identifier
    }

    /**
     * Retrieve cart items from storage
     */
    public function getItems(string $identifier, string $instance): array
    {
        $data = $this->cache->get($this->getItemsKey($identifier, $instance));

        if (is_string($data)) {
            return json_decode($data, true) ?: [];
        }

        return $data ?: [];
    }

    /**
     * Retrieve cart conditions from storage
     */
    public function getConditions(string $identifier, string $instance): array
    {
        $data = $this->cache->get($this->getConditionsKey($identifier, $instance));

        if (is_string($data)) {
            return json_decode($data, true) ?: [];
        }

        return $data ?: [];
    }

    /**
     * Store cart items in storage
     */
    public function putItems(string $identifier, string $instance, array $items): void
    {
        if ($this->useLocking && method_exists($this->cache, 'lock')) {
            $this->putItemsWithLock($identifier, $instance, $items);
        } else {
            $this->putItemsSimple($identifier, $instance, $items);
        }
    }

    /**
     * Store cart conditions in storage
     */
    public function putConditions(string $identifier, string $instance, array $conditions): void
    {
        if ($this->useLocking && method_exists($this->cache, 'lock')) {
            $this->putConditionsWithLock($identifier, $instance, $conditions);
        } else {
            $this->putConditionsSimple($identifier, $instance, $conditions);
        }
    }

    /**
     * Store both items and conditions in storage
     */
    public function putBoth(string $identifier, string $instance, array $items, array $conditions): void
    {
        $this->putItems($identifier, $instance, $items);
        $this->putConditions($identifier, $instance, $conditions);
    }

    /**
     * Store cart metadata
     */
    public function putMetadata(string $identifier, string $instance, string $key, mixed $value): void
    {
        $metadataKey = $this->getMetadataKey($identifier, $instance, $key);

        if ($this->useLocking && method_exists($this->cache, 'lock')) {
            $this->putMetadataWithLock($metadataKey, $value);
        } else {
            $this->cache->put($metadataKey, $value, $this->ttl);
        }
    }

    /**
     * Retrieve cart metadata
     */
    public function getMetadata(string $identifier, string $instance, string $key): mixed
    {
        $metadataKey = $this->getMetadataKey($identifier, $instance, $key);

        return $this->cache->get($metadataKey);
    }

    /**
     * Store items with locking to prevent concurrent modification
     */
    private function putItemsWithLock(string $identifier, string $instance, array $items): void
    {
        $key = $this->getItemsKey($identifier, $instance);
        $lock = $this->cache->lock("lock.{$key}", $this->lockTimeout);

        $lock->block($this->lockTimeout, function () use ($key, $items) {
            $this->cache->put($key, $items, $this->ttl);
        });
    }

    /**
     * Store items without locking (simple/fast mode)
     */
    private function putItemsSimple(string $identifier, string $instance, array $items): void
    {
        $this->cache->put($this->getItemsKey($identifier, $instance), $items, $this->ttl);
    }

    /**
     * Store conditions with locking to prevent concurrent modification
     */
    private function putConditionsWithLock(string $identifier, string $instance, array $conditions): void
    {
        $key = $this->getConditionsKey($identifier, $instance);
        $lock = $this->cache->lock("lock.{$key}", $this->lockTimeout);

        $lock->block($this->lockTimeout, function () use ($key, $conditions) {
            $this->cache->put($key, $conditions, $this->ttl);
        });
    }

    /**
     * Store conditions without locking (simple/fast mode)
     */
    private function putConditionsSimple(string $identifier, string $instance, array $conditions): void
    {
        $this->cache->put($this->getConditionsKey($identifier, $instance), $conditions, $this->ttl);
    }

    /**
     * Store metadata with locking to prevent concurrent modification
     */
    private function putMetadataWithLock(string $metadataKey, mixed $value): void
    {
        $lock = $this->cache->lock("lock.{$metadataKey}", $this->lockTimeout);

        $lock->block($this->lockTimeout, function () use ($metadataKey, $value) {
            $this->cache->put($metadataKey, $value, $this->ttl);
        });
    }

    /**
     * Get the items storage key
     */
    private function getItemsKey(string $identifier, string $instance): string
    {
        return "{$this->keyPrefix}.{$identifier}.{$instance}.items";
    }

    /**
     * Get the conditions storage key
     */
    private function getConditionsKey(string $identifier, string $instance): string
    {
        return "{$this->keyPrefix}.{$identifier}.{$instance}.conditions";
    }

    /**
     * Get the metadata storage key
     */
    private function getMetadataKey(string $identifier, string $instance, string $key): string
    {
        return "{$this->keyPrefix}.{$identifier}.{$instance}.metadata.{$key}";
    }

    /**
     * Swap cart identifier by transferring cart data from old identifier to new identifier.
     * This changes cart ownership to ensure the new identifier has an active cart.
     */
    public function swapIdentifier(string $oldIdentifier, string $newIdentifier, string $instance): bool
    {
        // Check if source cart exists
        if (! $this->has($oldIdentifier, $instance)) {
            return false;
        }

        // Get all data from the source identifier
        $items = $this->getItems($oldIdentifier, $instance);
        $conditions = $this->getConditions($oldIdentifier, $instance);

        // Transfer source cart to new identifier (swap even if empty to ensure ownership)
        $this->putBoth($newIdentifier, $instance, $items, $conditions);

        // Remove data from old identifier
        $this->forget($oldIdentifier, $instance);

        return true;
    }
}
