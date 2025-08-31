<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Storage;

interface StorageInterface
{
    /**
     * Retrieve an item from storage
     */
    public function get(string $key): mixed;

    /**
     * Store an item in storage
     */
    public function put(string $key, mixed $value): void;

    /**
     * Check if an item exists in storage
     */
    public function has(string $key): bool;

    /**
     * Remove an item from storage
     */
    public function forget(string $key): void;

    /**
     * Clear all items from storage
     */
    public function flush(): void;
}
