<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Traits;

trait ManagesMetadata
{
    /**
     * Store metadata for the cart
     */
    public function setMetadata(string $key, mixed $value): static
    {
        $this->storage->putMetadata($this->getIdentifier(), $this->getStorageInstanceName(), $key, $value);

        return $this;
    }

    /**
     * Retrieve metadata from the cart
     */
    public function getMetadata(string $key, mixed $default = null): mixed
    {
        return $this->storage->getMetadata($this->getIdentifier(), $this->getStorageInstanceName(), $key) ?? $default;
    }

    /**
     * Check if metadata key exists
     */
    public function hasMetadata(string $key): bool
    {
        return $this->storage->getMetadata($this->getIdentifier(), $this->getStorageInstanceName(), $key) !== null;
    }

    /**
     * Remove metadata by setting it to null
     */
    public function removeMetadata(string $key): static
    {
        $this->storage->putMetadata($this->getIdentifier(), $this->getStorageInstanceName(), $key, null);

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
