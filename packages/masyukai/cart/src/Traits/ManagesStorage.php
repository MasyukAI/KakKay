<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Traits;

use MasyukAI\Cart\Collections\CartCollection;
use MasyukAI\Cart\Conditions\CartCondition;
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
                // Handle associated model restoration
                $associatedModel = null;
                if (isset($itemData['associated_model'])) {
                    $associatedModel = $this->restoreAssociatedModel($itemData['associated_model']);
                }

                $item = new CartItem(
                    $itemData['id'],
                    $itemData['name'],
                    (float) $itemData['price'], // Ensure price is float
                    $itemData['quantity'],
                    $itemData['attributes'] ?? [],
                    $itemData['conditions'] ?? [],
                    $associatedModel
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
     * Get complete cart content (items, conditions, totals, etc.)
     */
    public function content(): array
    {
        return [
            'instance' => $this->instanceName,
            'items' => $this->getItems()->toArray(),
            'conditions' => $this->getConditions()->toArray(),
            'subtotal' => $this->getSubTotal(),
            'subtotal_with_conditions' => $this->getSubTotalWithConditions(),
            'total' => $this->getTotal(),
            'quantity' => $this->getTotalQuantity(),
            'count' => $this->countItems(), // Number of unique items, not total quantity
            'is_empty' => $this->isEmpty(),
        ];
    }

    /**
     * Get complete cart content (alias for content())
     */
    public function getContent(): array
    {
        return $this->content();
    }

    /**
     * Merge another cart instance with this one (shopping-cart style)
     */
    public function merge(string $instanceName): static
    {
        // Get the items from the other instance
        $otherItems = $this->storage->getItems($this->getIdentifier(), $instanceName);

        // Get the conditions from the other instance
        $otherConditions = $this->storage->getConditions($this->getIdentifier(), $instanceName);

        // Merge items
        if ($otherItems && is_array($otherItems)) {
            foreach ($otherItems as $itemData) {
                if (is_array($itemData) && isset($itemData['id'])) {
                    $this->add(
                        $itemData['id'],
                        $itemData['name'],
                        $itemData['price'],
                        $itemData['quantity'],
                        $itemData['attributes'] ?? [],
                        $itemData['conditions'] ?? [],
                        $itemData['associated_model'] ?? null // Use snake_case to match storage format
                    );
                }
            }
        }

        // Merge conditions
        if ($otherConditions && is_array($otherConditions)) {
            foreach ($otherConditions as $conditionData) {
                if (is_array($conditionData) && isset($conditionData['name'])) {
                    $condition = CartCondition::fromArray($conditionData);
                    $this->condition($condition);
                }
            }
        }

        // Clear the merged cart
        $this->storage->forget($this->getIdentifier(), $instanceName);

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
     * Convert cart to array (alias for content())
     */
    public function toArray(): array
    {
        return $this->content();
    }

    /**
     * Save cart items to storage
     */
    private function save(CartCollection $items): void
    {
        $itemsArray = $items->toArray();
        $this->storage->putItems($this->getIdentifier(), $this->getStorageInstanceName(), $itemsArray);
    }

    /**
     * Restore associated model from array format
     */
    private function restoreAssociatedModel(mixed $associatedData): object|string|null
    {
        if (is_string($associatedData)) {
            return $associatedData;
        }

        if (is_array($associatedData) && isset($associatedData['class'])) {
            // Return just the class name if it exists, otherwise null
            $className = $associatedData['class'];
            if (class_exists($className)) {
                return $className;
            }
        }

        return null;
    }
}
