<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Storage;

use Illuminate\Database\ConnectionInterface as Database;

readonly class DatabaseStorage implements StorageInterface
{
    public function __construct(
        private Database $database,
        private string $table = 'cart_storage'
    ) {
        //
    }

    /**
     * Retrieve an item from storage
     */
    public function get(string $key): mixed
    {
        $record = $this->database->table($this->table)
            ->where('key', $key)
            ->first();

        if (!$record) {
            return null;
        }

        return unserialize($record->value);
    }

    /**
     * Store an item in storage
     */
    public function put(string $key, mixed $value): void
    {
        $this->database->table($this->table)->updateOrInsert(
            ['key' => $key],
            [
                'value' => serialize($value),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    /**
     * Check if an item exists in storage
     */
    public function has(string $key): bool
    {
        return $this->database->table($this->table)
            ->where('key', $key)
            ->exists();
    }

    /**
     * Remove an item from storage
     */
    public function forget(string $key): void
    {
        $this->database->table($this->table)
            ->where('key', $key)
            ->delete();
    }

    /**
     * Clear all items from storage
     */
    public function flush(): void
    {
        $this->database->table($this->table)->truncate();
    }
}
