<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Traits;

use MasyukAI\Cart\Events\MetadataAdded;
use MasyukAI\Cart\Events\MetadataRemoved;

trait ManagesMetadata
{
    /**
     * Store metadata for the cart
     */
    public function setMetadata(string $key, mixed $value): static
    {
        $this->storage->putMetadata($this->getIdentifier(), $this->instance(), $key, $value);

        if ($this->eventsEnabled && $this->events) {
            $this->events->dispatch(new MetadataAdded($key, $value, $this));
        }

        return $this;
    }

    /**
     * Retrieve metadata from the cart
     */
    public function getMetadata(string $key, mixed $default = null): mixed
    {
        return $this->storage->getMetadata($this->getIdentifier(), $this->instance(), $key) ?? $default;
    }

    /**
     * Check if metadata key exists
     */
    public function hasMetadata(string $key): bool
    {
        return $this->storage->getMetadata($this->getIdentifier(), $this->instance(), $key) !== null;
    }

    /**
     * Remove metadata by setting it to null
     */
    public function removeMetadata(string $key): static
    {
        $this->storage->putMetadata($this->getIdentifier(), $this->instance(), $key, null);

        if ($this->eventsEnabled && $this->events) {
            $this->events->dispatch(new MetadataRemoved($key, $this));
        }

        return $this;
    }

    /**
     * Set multiple metadata values at once
     */
    public function setMetadataBatch(array $metadata): static
    {
        foreach ($metadata as $key => $value) {
            $this->setMetadata($key, $value);
        }

        return $this;
    }
}
