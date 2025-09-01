<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Traits;

use MasyukAI\Cart\Events\CartCleared;
use MasyukAI\Cart\Storage\StorageInterface;

trait ManagesInstances
{
    /**
     * Get the current instance name
     */
    public function instance(): string
    {
        return $this->instanceName;
    }

    /**
     * Get the storage interface
     */
    public function storage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * Set the current cart instance
     */
    public function setInstance(string $name): static
    {
        return new static(
            $this->storage,
            $this->events,
            $name,
            $this->eventsEnabled,
            $this->config
        );
    }

    /**
     * Clear the entire cart
     */
    public function clear(): bool
    {
        $this->storage->forget($this->getIdentifier(), $this->getStorageInstanceName());

        if ($this->eventsEnabled && $this->events) {
            $this->events->dispatch(new CartCleared($this));
        }

        return true;
    }

    /**
     * Get the current instance name
     */
    public function getCurrentInstance(): string
    {
        return $this->instanceName;
    }

    /**
     * Get the instance name to use for storage operations
     */
    private function getStorageInstanceName(): string
    {
        // Instance name is ALWAYS what was set via setInstance(), never modified
        return $this->instanceName;
    }
}
