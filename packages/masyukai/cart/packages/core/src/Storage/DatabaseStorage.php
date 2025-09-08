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
     * Retrieve cart items from storage
     */
    public function getItems(string $identifier, string $instance): array
    {
        $record = $this->database->table($this->table)
            ->where('identifier', $identifier)
            ->where('instance', $instance)
            ->first();

        if (! $record || ! $record->items) {
            return [];
        }

        return json_decode($record->items, true) ?: [];
    }

    /**
     * Retrieve cart conditions from storage
     */
    public function getConditions(string $identifier, string $instance): array
    {
        $record = $this->database->table($this->table)
            ->where('identifier', $identifier)
            ->where('instance', $instance)
            ->first();

        if (! $record || ! $record->conditions) {
            return [];
        }

        return json_decode($record->conditions, true) ?: [];
    }

    /**
     * Store cart items in storage
     */
    public function putItems(string $identifier, string $instance, array $items): void
    {
        $this->database->table($this->table)->updateOrInsert(
            ['identifier' => $identifier, 'instance' => $instance],
            [
                'items' => json_encode($items),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    /**
     * Store cart conditions in storage
     */
    public function putConditions(string $identifier, string $instance, array $conditions): void
    {
        $this->database->table($this->table)->updateOrInsert(
            ['identifier' => $identifier, 'instance' => $instance],
            [
                'conditions' => json_encode($conditions),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    /**
     * Store both items and conditions in storage
     */
    public function putBoth(string $identifier, string $instance, array $items, array $conditions): void
    {
        $this->database->table($this->table)->updateOrInsert(
            ['identifier' => $identifier, 'instance' => $instance],
            [
                'items' => json_encode($items),
                'conditions' => json_encode($conditions),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    /**
     * Check if cart exists in storage
     */
    public function has(string $identifier, string $instance): bool
    {
        return $this->database->table($this->table)
            ->where('identifier', $identifier)
            ->where('instance', $instance)
            ->exists();
    }

    /**
     * Remove cart from storage
     */
    public function forget(string $identifier, string $instance): void
    {
        $this->database->table($this->table)
            ->where('identifier', $identifier)
            ->where('instance', $instance)
            ->delete();
    }

    /**
     * Clear all carts from storage
     */
    public function flush(): void
    {
        $this->database->table($this->table)->truncate();
    }

    /**
     * Get all instances for a specific identifier
     */
    public function getInstances(string $identifier): array
    {
        return $this->database->table($this->table)
            ->where('identifier', $identifier)
            ->pluck('instance')
            ->toArray();
    }

    /**
     * Remove all instances for a specific identifier
     */
    public function forgetIdentifier(string $identifier): void
    {
        $this->database->table($this->table)
            ->where('identifier', $identifier)
            ->delete();
    }

    /**
     * Store cart metadata
     */
    public function putMetadata(string $identifier, string $instance, string $key, mixed $value): void
    {
        // Get existing metadata
        $existing = $this->database->table($this->table)
            ->where('identifier', $identifier)
            ->where('instance', $instance)
            ->value('metadata');

        $metadata = $existing ? json_decode($existing, true) : [];
        $metadata[$key] = $value;

        $this->database->table($this->table)->updateOrInsert(
            ['identifier' => $identifier, 'instance' => $instance],
            [
                'metadata' => json_encode($metadata),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    /**
     * Retrieve cart metadata
     */
    public function getMetadata(string $identifier, string $instance, string $key): mixed
    {
        $result = $this->database->table($this->table)
            ->where('identifier', $identifier)
            ->where('instance', $instance)
            ->value('metadata');

        if (! $result) {
            return null;
        }

        $metadata = json_decode($result, true);

        return $metadata[$key] ?? null;
    }

    /**
     * Swap cart identifier by directly updating the identifier column.
     * This transfers cart ownership from old identifier to new identifier.
     * The objective is to change ownership to ensure target has an active cart.
     */
    public function swapIdentifier(string $oldIdentifier, string $newIdentifier, string $instance): bool
    {
        // Check if source cart exists
        if (!$this->has($oldIdentifier, $instance)) {
            return false;
        }

        // Simply update the identifier column - this is the "super stupid and simple" solution
        // Swap even if cart is empty to ensure ownership transfer and prevent abandonment
        $updated = $this->database->table($this->table)
            ->where('identifier', $oldIdentifier)
            ->where('instance', $instance)
            ->update([
                'identifier' => $newIdentifier,
                'updated_at' => now(),
            ]);

        return $updated > 0;
    }
}
