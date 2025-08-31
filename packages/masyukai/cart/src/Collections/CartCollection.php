<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Collections;

use Illuminate\Support\Collection;
use MasyukAI\Cart\Models\CartItem;

class CartCollection extends Collection
{
    /**
     * Add a cart item to the collection
     */
    public function addItem(CartItem $item): static
    {
        return $this->put($item->id, $item);
    }

    /**
     * Remove a cart item from the collection
     */
    public function removeItem(string $id): static
    {
        return $this->forget($id);
    }

    /**
     * Get cart item by ID
     */
    public function getItem(string $id): ?CartItem
    {
        return $this->get($id);
    }

    /**
     * Check if cart item exists
     */
    public function hasItem(string $id): bool
    {
        return $this->has($id);
    }

    /**
     * Get total quantity of all items
     */
    public function getTotalQuantity(): int
    {
        return $this->sum('quantity');
    }

    /**
     * Get subtotal of all items
     */
    public function getSubTotal(): float
    {
        return $this->sum(fn (CartItem $item) => $item->getPriceSum());
    }

    /**
     * Get subtotal with conditions applied
     */
    public function getSubTotalWithConditions(): float
    {
        return $this->sum(fn (CartItem $item) => $item->getPriceSumWithConditions());
    }

    /**
     * Convert collection to array with formatted output
     */
    public function toFormattedArray(): array
    {
        return [
            'items' => $this->map(fn (CartItem $item) => $item->toArray())->toArray(),
            'total_quantity' => $this->getTotalQuantity(),
            'subtotal' => $this->getSubTotal(),
            'subtotal_with_conditions' => $this->getSubTotalWithConditions(),
            'count' => $this->count(),
            'is_empty' => $this->isEmpty(),
        ];
    }

    /**
     * Filter items by a condition
     */
    public function filterByCondition(string $conditionName): static
    {
        return $this->filter(fn (CartItem $item) => $item->hasCondition($conditionName));
    }

    /**
     * Get items with specific attribute
     */
    public function filterByAttribute(string $attribute, mixed $value = null): static
    {
        return $this->filter(function (CartItem $item) use ($attribute, $value) {
            if ($value === null) {
                return $item->hasAttribute($attribute);
            }

            return $item->getAttribute($attribute) === $value;
        });
    }

    /**
     * Get items associated with a specific model
     */
    public function filterByModel(string $modelClass): static
    {
        return $this->filter(fn (CartItem $item) => $item->isAssociatedWith($modelClass));
    }

    /**
     * Search items by name
     */
    public function searchByName(string $query): static
    {
        return $this->filter(fn (CartItem $item) => str_contains(
            strtolower($item->name),
            strtolower($query)
        ));
    }

    /**
     * Sort items by price
     */
    public function sortByPrice(string $direction = 'asc'): static
    {
        return $direction === 'desc'
            ? $this->sortByDesc('price')
            : $this->sortBy('price');
    }

    /**
     * Sort items by quantity
     */
    public function sortByQuantity(string $direction = 'asc'): static
    {
        return $direction === 'desc'
            ? $this->sortByDesc('quantity')
            : $this->sortBy('quantity');
    }

    /**
     * Sort items by name
     */
    public function sortByName(string $direction = 'asc'): static
    {
        return $direction === 'desc'
            ? $this->sortByDesc('name')
            : $this->sortBy('name');
    }

    /**
     * Get unique items by a specific property
     */
    public function uniqueBy(string $property): static
    {
        return $this->unique($property);
    }

    /**
     * Group items by a specific property
     */
    public function groupByProperty(string $property): Collection
    {
        return $this->groupBy($property);
    }

    /**
     * Get items with quantity greater than specified amount
     */
    public function whereQuantityGreaterThan(int $quantity): static
    {
        return $this->filter(fn (CartItem $item) => $item->quantity > $quantity);
    }

    /**
     * Get items with quantity less than specified amount
     */
    public function whereQuantityLessThan(int $quantity): static
    {
        return $this->filter(fn (CartItem $item) => $item->quantity < $quantity);
    }

    /**
     * Get items with price between range
     */
    public function wherePriceBetween(float $min, float $max): static
    {
        return $this->filter(fn (CartItem $item) => $item->price >= $min && $item->price <= $max);
    }

    /**
     * Check if collection contains items with conditions
     */
    public function hasItemsWithConditions(): bool
    {
        return $this->contains(fn (CartItem $item) => $item->hasConditions());
    }

    /**
     * Get total discount amount from all items
     */
    public function getTotalDiscount(): float
    {
        return $this->sum(fn (CartItem $item) => $item->getDiscountAmount());
    }

    /**
     * Get statistics about the cart
     */
    public function getStatistics(): array
    {
        return [
            'total_items' => $this->count(),
            'total_quantity' => $this->getTotalQuantity(),
            'average_price' => $this->avg('price'),
            'highest_price' => $this->max('price'),
            'lowest_price' => $this->min('price'),
            'total_value' => $this->getSubTotal(),
            'total_with_conditions' => $this->getSubTotalWithConditions(),
            'items_with_conditions' => $this->filter(fn (CartItem $item) => $item->hasConditions())->count(),
        ];
    }

    /**
     * Get items where quantity is above threshold
     */
    public function whereQuantityAbove(int $threshold): static
    {
        return $this->filter(fn (CartItem $item) => $item->quantity > $threshold);
    }

    /**
     * Group items by attribute value
     */
    public function groupByAttribute(string $attribute): \Illuminate\Support\Collection
    {
        return $this->groupBy(fn (CartItem $item) => $item->getAttribute($attribute));
    }

    /**
     * Find items by model type (if associated)
     */
    public function whereModel(string $modelClass): static
    {
        return $this->filter(fn (CartItem $item) => $item->isAssociatedWith($modelClass));
    }
}
