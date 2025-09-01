<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Traits;

use MasyukAI\Cart\Collections\CartCollection;
use MasyukAI\Cart\Models\CartItem;

trait ManagesStorage
{
    /**
     * Get all cart items
     */
    public function getItems(): CartCollection
    {
        $items = $this->storage->getItems($this->getIdentifier(), $this->getStorageInstanceName());

        if (! $items || ! is_array($items)) {
            return new CartCollection;
        }

        // Convert array back to CartCollection
        $collection = new CartCollection;
        foreach ($items as $itemData) {
            if (is_array($itemData) && isset($itemData['id'])) {
                $item = new CartItem(
                    $itemData['id'],
                    $itemData['name'],
                    $itemData['price'],
                    $itemData['quantity'],
                    $itemData['attributes'] ?? [],
                    $itemData['conditions'] ?? [],
                    $itemData['associatedModel'] ?? null
                );
                $collection->put($item->id, $item);
            }
        }

        return $collection;
    }

    /**
     * Check if cart is empty
     */
    public function isEmpty(): bool
    {
        return $this->getItems()->isEmpty();
    }

    /**
     * Merge another cart instance with this one (shopping-cart style)
     */
    public function merge(string $instanceName): static
    {
        // Get the content from the other instance
        $otherContent = $this->storage->getItems($this->getIdentifier(), $instanceName);

        if ($otherContent && is_array($otherContent)) {
            // Convert array back to CartCollection items and merge
            foreach ($otherContent as $itemData) {
                if (is_array($itemData) && isset($itemData['id'])) {
                    $this->add(
                        $itemData['id'],
                        $itemData['name'],
                        $itemData['price'],
                        $itemData['quantity'],
                        $itemData['attributes'] ?? [],
                        $itemData['conditions'] ?? [],
                        $itemData['associatedModel'] ?? null
                    );
                }
            }

            // Clear the merged cart
            $this->storage->forget($this->getIdentifier(), $instanceName);
        }

        return $this;
    }

    /**
     * Store cart data (shopping-cart style)
     */
    public function store(): void
    {
        // Cart is automatically stored, but this provides explicit control
        $this->save($this->getItems());

        // Note: No specific event needed for store operation
    }

    /**
     * Restore cart data (shopping-cart style)
     */
    public function restore(): void
    {
        // Cart is automatically loaded, but this provides explicit control
        $this->getItems();

        // Note: No specific event needed for restore operation
    }

    /**
     * Convert cart to array
     */
    public function toArray(): array
    {
        return [
            'instance' => $this->instanceName,
            'items' => $this->getItems()->toArray(),
            'conditions' => $this->getConditions()->toArray(),
            'subtotal' => $this->getSubTotal(),
            'subtotal_with_conditions' => $this->getSubTotalWithConditions(),
            'total' => $this->getTotal(),
            'quantity' => $this->getTotalQuantity(),
            'count' => $this->count(),
            'is_empty' => $this->isEmpty(),
        ];
    }

    /**
     * Save cart items to storage
     */
    private function save(CartCollection $items): void
    {
        $itemsArray = $items->toArray();
        $this->storage->putItems($this->getIdentifier(), $this->getStorageInstanceName(), $itemsArray);
    }
}
