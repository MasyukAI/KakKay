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
     * Check if cart exists in storage
     */
    public function has(string $identifier, string $instance): bool
    {
        $cartData = $this->session->get($this->keyPrefix, []);

        return isset($cartData[$identifier][$instance]['items']) ||
               isset($cartData[$identifier][$instance]['conditions']);
    }

    /**
     * Remove cart from storage
     */
    public function forget(string $identifier, string $instance): void
    {
        $cartData = $this->session->get($this->keyPrefix, []);

        if (isset($cartData[$identifier][$instance])) {
            unset($cartData[$identifier][$instance]);

            // If this identifier has no more instances, remove it entirely
            if (empty($cartData[$identifier])) {
                unset($cartData[$identifier]);
            }

            // Update the session with the modified data
            if (empty($cartData)) {
                $this->session->forget($this->keyPrefix);
            } else {
                $this->session->put($this->keyPrefix, $cartData);
            }
        }
    }

    /**
     * Clear all carts from storage
     */
    public function flush(): void
    {
        // Remove the entire cart data from session
        $this->session->forget($this->keyPrefix);
    }

    /**
     * Get all instances for a specific identifier
     */
    public function getInstances(string $identifier): array
    {
        // Get the nested cart data for this identifier
        $cartData = $this->session->get($this->keyPrefix, []);

        if (! isset($cartData[$identifier]) || ! is_array($cartData[$identifier])) {
            return [];
        }

        // Return all instance names (keys) for this identifier
        return array_keys($cartData[$identifier]);
    }

    /**
     * Remove all instances for a specific identifier
     */
    public function forgetIdentifier(string $identifier): void
    {
        // Get current cart data
        $cartData = $this->session->get($this->keyPrefix, []);

        // Remove this identifier's data
        unset($cartData[$identifier]);

        // Put back the modified cart data
        if (empty($cartData)) {
            $this->session->forget($this->keyPrefix);
        } else {
            $this->session->put($this->keyPrefix, $cartData);
        }
    }

    /**
     * Retrieve cart items from storage
     */
    public function getItems(string $identifier, string $instance): array
    {
        $data = $this->session->get($this->getItemsKey($identifier, $instance));

        if (is_string($data)) {
            return json_decode($data, true) ?: [];
        }

        return $data ?: [];
    }

    /**
     * Retrieve cart conditions from storage
     */
    public function getConditions(string $identifier, string $instance): array
    {
        $data = $this->session->get($this->getConditionsKey($identifier, $instance));

        if (is_string($data)) {
            return json_decode($data, true) ?: [];
        }

        return $data ?: [];
    }

    /**
     * Store cart items in storage
     */
    public function putItems(string $identifier, string $instance, array $items): void
    {
        $this->session->put($this->getItemsKey($identifier, $instance), $items);
    }

    /**
     * Store cart conditions in storage
     */
    public function putConditions(string $identifier, string $instance, array $conditions): void
    {
        $this->session->put($this->getConditionsKey($identifier, $instance), $conditions);
    }

    /**
     * Store both items and conditions in storage
     */
    public function putBoth(string $identifier, string $instance, array $items, array $conditions): void
    {
        $this->putItems($identifier, $instance, $items);
        $this->putConditions($identifier, $instance, $conditions);
    }

    /**
     * Store cart metadata
     */
    public function putMetadata(string $identifier, string $instance, string $key, mixed $value): void
    {
        $metadataKey = $this->getMetadataKey($identifier, $instance, $key);
        $this->session->put($metadataKey, $value);
    }

    /**
     * Retrieve cart metadata
     */
    public function getMetadata(string $identifier, string $instance, string $key): mixed
    {
        $metadataKey = $this->getMetadataKey($identifier, $instance, $key);

        return $this->session->get($metadataKey);
    }

    /**
     * Get the full storage key (legacy for backward compatibility)
     */
    private function getKey(string $identifier, string $instance): string
    {
        return "{$this->keyPrefix}.{$identifier}.{$instance}";
    }

    /**
     * Get the items storage key
     */
    private function getItemsKey(string $identifier, string $instance): string
    {
        return "{$this->keyPrefix}.{$identifier}.{$instance}.items";
    }

    /**
     * Get the conditions storage key
     */
    private function getConditionsKey(string $identifier, string $instance): string
    {
        return "{$this->keyPrefix}.{$identifier}.{$instance}.conditions";
    }

    /**
     * Get the metadata storage key
     */
    private function getMetadataKey(string $identifier, string $instance, string $key): string
    {
        return "{$this->keyPrefix}.{$identifier}.{$instance}.metadata.{$key}";
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
            // Target cart exists - preserve it
            // Remove source cart since we're keeping the target
            if ($this->has($sourceIdentifier, $instance)) {
                $this->forget($sourceIdentifier, $instance);
            }
            return true;
        }
        
        // Target cart doesn't exist - check if source cart exists and has content
        if (!$this->has($sourceIdentifier, $instance)) {
            return false; // No cart to take over
        }
        
        // Get all data from the source identifier
        $items = $this->getItems($sourceIdentifier, $instance);
        $conditions = $this->getConditions($sourceIdentifier, $instance);
        
        // If source cart is empty, nothing to transfer
        if (empty($items) && empty($conditions)) {
            return false;
        }
        
        // Transfer source cart to target identifier
        $this->putBoth($targetIdentifier, $instance, $items, $conditions);
        
        // Remove data from source identifier
        $this->forget($sourceIdentifier, $instance);
        
        return true;
    }

    /**
     * @deprecated Use takeoverCart() instead. This method will be removed in a future version.
     */
    public function swapIdentifier(string $oldIdentifier, string $newIdentifier, string $instance): bool
    {
        return $this->takeoverCart($oldIdentifier, $newIdentifier, $instance);
    }
}
