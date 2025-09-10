<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Storage;

use Illuminate\Database\ConnectionInterface as Database;

readonly class DatabaseStorage implements StorageInterface
{
    public function __construct(
        private Database $database,
        private string $table = 'carts'
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

        try {
            return json_decode($record->items, true, 512, JSON_THROW_ON_ERROR) ?: [];
        } catch (\JsonException $e) {
            // Log the error and return empty array for corrupted data
            logger()->error('Failed to decode cart items JSON', [
                'identifier' => $identifier,
                'instance' => $instance,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
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

        try {
            return json_decode($record->conditions, true, 512, JSON_THROW_ON_ERROR) ?: [];
        } catch (\JsonException $e) {
            // Log the error and return empty array for corrupted data
            logger()->error('Failed to decode cart conditions JSON', [
                'identifier' => $identifier,
                'instance' => $instance,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Store cart items in storage
     */
    public function putItems(string $identifier, string $instance, array $items): void
    {
        $this->validateDataSize($items, 'items');

        try {
            $itemsJson = json_encode($items, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException('Cannot encode items to JSON: '.$e->getMessage());
        }

        $this->database->transaction(function () use ($identifier, $instance, $itemsJson) {
            $current = $this->database->table($this->table)
                ->where('identifier', $identifier)
                ->where('instance', $instance)
                ->first(['id', 'version']);

            if ($current) {
                $updated = $this->database->table($this->table)
                    ->where('identifier', $identifier)
                    ->where('instance', $instance)
                    ->where('version', $current->version)
                    ->update([
                        'items' => $itemsJson,
                        'version' => $current->version + 1,
                        'updated_at' => now(),
                    ]);

                if ($updated === 0) {
                    throw new \RuntimeException('Cart was modified by another request. Please retry.');
                }
            } else {
                $this->database->table($this->table)->insert([
                    'identifier' => $identifier,
                    'instance' => $instance,
                    'items' => $itemsJson,
                    'version' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    /**
     * Store cart conditions in storage
     */
    public function putConditions(string $identifier, string $instance, array $conditions): void
    {
        $this->validateDataSize($conditions, 'conditions');

        try {
            $conditionsJson = json_encode($conditions, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException('Cannot encode conditions to JSON: '.$e->getMessage());
        }

        $this->database->transaction(function () use ($identifier, $instance, $conditionsJson) {
            $current = $this->database->table($this->table)
                ->where('identifier', $identifier)
                ->where('instance', $instance)
                ->first(['id', 'version']);

            if ($current) {
                $updated = $this->database->table($this->table)
                    ->where('identifier', $identifier)
                    ->where('instance', $instance)
                    ->where('version', $current->version)
                    ->update([
                        'conditions' => $conditionsJson,
                        'version' => $current->version + 1,
                        'updated_at' => now(),
                    ]);

                if ($updated === 0) {
                    throw new \RuntimeException('Cart was modified by another request. Please retry.');
                }
            } else {
                $this->database->table($this->table)->insert([
                    'identifier' => $identifier,
                    'instance' => $instance,
                    'conditions' => $conditionsJson,
                    'version' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    /**
     * Store both items and conditions in storage
     */
    public function putBoth(string $identifier, string $instance, array $items, array $conditions): void
    {
        $this->validateDataSize($items, 'items');
        $this->validateDataSize($conditions, 'conditions');

        try {
            $itemsJson = json_encode($items, JSON_THROW_ON_ERROR);
            $conditionsJson = json_encode($conditions, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException('Cannot encode data to JSON: '.$e->getMessage());
        }

        $this->database->transaction(function () use ($identifier, $instance, $itemsJson, $conditionsJson) {
            $current = $this->database->table($this->table)
                ->where('identifier', $identifier)
                ->where('instance', $instance)
                ->first(['id', 'version']);

            if ($current) {
                $updated = $this->database->table($this->table)
                    ->where('identifier', $identifier)
                    ->where('instance', $instance)
                    ->where('version', $current->version)
                    ->update([
                        'items' => $itemsJson,
                        'conditions' => $conditionsJson,
                        'version' => $current->version + 1,
                        'updated_at' => now(),
                    ]);

                if ($updated === 0) {
                    throw new \RuntimeException('Cart was modified by another request. Please retry.');
                }
            } else {
                $this->database->table($this->table)->insert([
                    'identifier' => $identifier,
                    'instance' => $instance,
                    'items' => $itemsJson,
                    'conditions' => $conditionsJson,
                    'version' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
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
     * WARNING: This is a dangerous operation that should be used with extreme caution
     */
    public function flush(): void
    {
        // Only allow flush in testing environments to prevent accidental data loss
        if (app()->environment(['testing', 'local'])) {
            $this->database->table($this->table)->truncate();
        } else {
            throw new \RuntimeException('Flush operation is only allowed in testing and local environments');
        }
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
        $this->database->transaction(function () use ($identifier, $instance, $key, $value) {
            // Get existing metadata
            $existing = $this->database->table($this->table)
                ->where('identifier', $identifier)
                ->where('instance', $instance)
                ->value('metadata');

            try {
                $metadata = $existing ? json_decode($existing, true, 512, JSON_THROW_ON_ERROR) : [];
            } catch (\JsonException $e) {
                logger()->error('Failed to decode metadata JSON, starting fresh', [
                    'identifier' => $identifier,
                    'instance' => $instance,
                    'error' => $e->getMessage(),
                ]);
                $metadata = [];
            }

            $metadata[$key] = $value;
            $this->validateDataSize($metadata, 'metadata');

            try {
                $metadataJson = json_encode($metadata, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new \InvalidArgumentException('Cannot encode metadata to JSON: '.$e->getMessage());
            }

            $current = $this->database->table($this->table)
                ->where('identifier', $identifier)
                ->where('instance', $instance)
                ->first(['id', 'version']);

            if ($current) {
                $updated = $this->database->table($this->table)
                    ->where('identifier', $identifier)
                    ->where('instance', $instance)
                    ->where('version', $current->version)
                    ->update([
                        'metadata' => $metadataJson,
                        'version' => $current->version + 1,
                        'updated_at' => now(),
                    ]);

                if ($updated === 0) {
                    throw new \RuntimeException('Cart was modified by another request. Please retry.');
                }
            } else {
                $this->database->table($this->table)->insert([
                    'identifier' => $identifier,
                    'instance' => $instance,
                    'metadata' => $metadataJson,
                    'version' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
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

        try {
            $metadata = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            logger()->error('Failed to decode metadata JSON', [
                'identifier' => $identifier,
                'instance' => $instance,
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

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
        if (! $this->has($oldIdentifier, $instance)) {
            return false;
        }

        // Use transaction to handle the swap safely
        return $this->database->transaction(function () use ($oldIdentifier, $newIdentifier, $instance) {
            // First, delete any existing cart with the target identifier
            // This ensures the swap always succeeds by removing conflicts
            $this->database->table($this->table)
                ->where('identifier', $newIdentifier)
                ->where('instance', $instance)
                ->delete();

            // Now update the source cart to use the new identifier
            $updated = $this->database->table($this->table)
                ->where('identifier', $oldIdentifier)
                ->where('instance', $instance)
                ->update([
                    'identifier' => $newIdentifier,
                    'updated_at' => now(),
                ]);

            return $updated > 0;
        });
    }

    /**
     * Validate data size to prevent memory issues and DoS attacks
     */
    private function validateDataSize(array $data, string $type): void
    {
        // Get size limits from config or use defaults
        $maxItems = config('cart.limits.max_items', 1000);
        $maxDataSize = config('cart.limits.max_data_size_bytes', 1024 * 1024); // 1MB default

        // Check item count limit
        if ($type === 'items' && count($data) > $maxItems) {
            throw new \InvalidArgumentException("Cart cannot contain more than {$maxItems} items");
        }

        // Check data size limit
        try {
            $jsonSize = strlen(json_encode($data, JSON_THROW_ON_ERROR));
            if ($jsonSize > $maxDataSize) {
                $maxSizeMB = round($maxDataSize / (1024 * 1024), 2);
                throw new \InvalidArgumentException("Cart {$type} data size ({$jsonSize} bytes) exceeds maximum allowed size of {$maxSizeMB}MB");
            }
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException("Cannot validate {$type} data size: ".$e->getMessage());
        }
    }
}
