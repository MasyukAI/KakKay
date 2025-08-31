<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Storage;

use Illuminate\Contracts\Session\Session;

readonly class SessionStorage implements StorageInterface
{
    public function __construct(
        private Session $session,
        private string $keyPrefix = 'cart'
    ) {
        //
    }

    /**
     * Retrieve an item from storage
     */
    public function get(string $key): mixed
    {
        return $this->session->get($this->getKey($key));
    }

    /**
     * Store an item in storage
     */
    public function put(string $key, mixed $value): void
    {
        $this->session->put($this->getKey($key), $value);
    }

    /**
     * Check if an item exists in storage
     */
    public function has(string $key): bool
    {
        return $this->session->has($this->getKey($key));
    }

    /**
     * Remove an item from storage
     */
    public function forget(string $key): void
    {
        $this->session->forget($this->getKey($key));
    }

    /**
     * Clear all items from storage
     */
    public function flush(): void
    {
        $this->session->flush();
    }

    /**
     * Get the full storage key
     */
    private function getKey(string $key): string
    {
        return "{$this->keyPrefix}.{$key}";
    }
}
