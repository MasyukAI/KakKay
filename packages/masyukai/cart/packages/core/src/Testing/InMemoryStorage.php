<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Testing;

use MasyukAI\Cart\Storage\StorageInterface;

/**
 * Simple in-memory storage implementation for testing purposes.
 *
 * This storage implementation keeps all data in memory arrays,
 * making it suitable for unit testing without external dependencies.
 */
class InMemoryStorage implements StorageInterface
{
    private array $items = [];

    private array $conditions = [];

    private array $metadata = [];

    private array $instances = [];

    private array $versions = [];

    private array $ids = [];

    public function has(string $identifier, string $instance): bool
    {
        return isset($this->items[$identifier][$instance]) ||
               isset($this->conditions[$identifier][$instance]);
    }

    public function getItems(string $identifier, string $instance): array
    {
        return $this->items[$identifier][$instance] ?? [];
    }

    public function putItems(string $identifier, string $instance, array $items): void
    {
        $this->items[$identifier][$instance] = $items;
        $this->incrementVersion($identifier, $instance);
    }

    public function getConditions(string $identifier, string $instance): array
    {
        return $this->conditions[$identifier][$instance] ?? [];
    }

    public function putConditions(string $identifier, string $instance, array $conditions): void
    {
        $this->conditions[$identifier][$instance] = $conditions;
        $this->incrementVersion($identifier, $instance);
    }

    public function forget(string $identifier, string $instance): void
    {
        unset(
            $this->items[$identifier][$instance],
            $this->conditions[$identifier][$instance],
            $this->metadata[$identifier][$instance],
            $this->versions[$identifier][$instance],
            $this->ids[$identifier][$instance]
        );

        if (empty($this->items[$identifier])) {
            unset($this->items[$identifier]);
        }
        if (empty($this->conditions[$identifier])) {
            unset($this->conditions[$identifier]);
        }
        if (empty($this->metadata[$identifier])) {
            unset($this->metadata[$identifier]);
        }
        if (empty($this->versions[$identifier])) {
            unset($this->versions[$identifier]);
        }
        if (empty($this->ids[$identifier])) {
            unset($this->ids[$identifier]);
        }
    }

    public function flush(): void
    {
        $this->items = [];
        $this->conditions = [];
        $this->metadata = [];
        $this->instances = [];
        $this->versions = [];
        $this->ids = [];
    }

    public function getInstances(string $identifier): array
    {
        return $this->instances[$identifier] ?? [];
    }

    public function putMetadata(string $identifier, string $instance, string $key, mixed $value): void
    {
        $this->metadata[$identifier][$instance][$key] = $value;
        $this->incrementVersion($identifier, $instance);
    }

    public function getMetadata(string $identifier, string $instance, string $key): mixed
    {
        return $this->metadata[$identifier][$instance][$key] ?? null;
    }

    public function getVersion(string $identifier, string $instance): ?int
    {
        return $this->versions[$identifier][$instance] ?? null;
    }

    public function getId(string $identifier, string $instance): ?string
    {
        if (! isset($this->ids[$identifier][$instance])) {
            $this->ids[$identifier][$instance] = uniqid('cart_', true);
        }

        return $this->ids[$identifier][$instance];
    }

    public function swapIdentifier(string $from, string $to): void
    {
        if (isset($this->items[$from])) {
            $this->items[$to] = $this->items[$from];
            unset($this->items[$from]);
        }

        if (isset($this->conditions[$from])) {
            $this->conditions[$to] = $this->conditions[$from];
            unset($this->conditions[$from]);
        }

        if (isset($this->metadata[$from])) {
            $this->metadata[$to] = $this->metadata[$from];
            unset($this->metadata[$from]);
        }

        if (isset($this->instances[$from])) {
            $this->instances[$to] = $this->instances[$from];
            unset($this->instances[$from]);
        }

        if (isset($this->versions[$from])) {
            $this->versions[$to] = $this->versions[$from];
            unset($this->versions[$from]);
        }

        if (isset($this->ids[$from])) {
            $this->ids[$to] = $this->ids[$from];
            unset($this->ids[$from]);
        }
    }

    private function incrementVersion(string $identifier, string $instance): void
    {
        $current = $this->versions[$identifier][$instance] ?? 0;
        $this->versions[$identifier][$instance] = $current + 1;
    }
}
