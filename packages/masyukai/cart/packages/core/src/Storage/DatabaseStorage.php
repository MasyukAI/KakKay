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
     * Take over cart ownership by ensuring the target identifier has an active cart.
     * Priority is preserving the target cart, not the source cart.
     */
    public function takeoverCart(string $sourceIdentifier, string $targetIdentifier, string $instance): bool
    {
        // Check if target cart already exists and has content
        $targetExists = $this->has($targetIdentifier, $instance);
        
        if ($targetExists) {
            // Target cart exists - preserve it and just ensure it's marked as active
            // Remove source cart since we're keeping the target
            if ($this->has($sourceIdentifier, $instance)) {
                $this->forget($sourceIdentifier, $instance);
            }
            
            // Update target cart's timestamp to mark it as active
            $this->database->table($this->table)
                ->where('identifier', $targetIdentifier)
                ->where('instance', $instance)
                ->update(['updated_at' => now()]);
                
            return true;
        }
        
        // Target cart doesn't exist - check if source cart exists and has content
        if (!$this->has($sourceIdentifier, $instance)) {
            return false; // No cart to take over
        }
        
        $sourceRecord = $this->database->table($this->table)
            ->where('identifier', $sourceIdentifier)
            ->where('instance', $instance)
            ->first();
            
        if (!$sourceRecord || (empty($sourceRecord->items) && empty($sourceRecord->conditions))) {
            return false; // Source cart exists but is empty
        }
        
        // Transfer source cart to target identifier (simple identifier update)
        $updated = $this->database->table($this->table)
            ->where('identifier', $sourceIdentifier)
            ->where('instance', $instance)
            ->update([
                'identifier' => $targetIdentifier,
                'updated_at' => now(),
            ]);

        return $updated > 0;
    }

    /**
     * @deprecated Use takeoverCart() instead. This method will be removed in a future version.
     */
    public function swapIdentifier(string $oldIdentifier, string $newIdentifier, string $instance): bool
    {
        return $this->takeoverCart($oldIdentifier, $newIdentifier, $instance);
    }
}
