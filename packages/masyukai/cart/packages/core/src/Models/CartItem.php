<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use JsonSerializable;
use MasyukAI\Cart\Collections\CartConditionCollection;
use MasyukAI\Cart\Conditions\CartCondition;

readonly class CartItem implements Arrayable, Jsonable, JsonSerializable
{
    use Traits\AssociatedModelTrait;
    use Traits\AttributeTrait;
    use Traits\ConditionTrait;
    use Traits\MoneyTrait;
    use Traits\SerializationTrait;
    use Traits\ValidationTrait;

    public string $id;

    public CartConditionCollection $conditions;

    public Collection $attributes;

    private float|int $rawPrice;

    public function __construct(
        string|int $id,
        public string $name,
        float|int|string $price,
        public int $quantity,
        array $attributes = [],
        array|Collection $conditions = [],
        public string|object|null $associatedModel = null
    ) {
        // Normalize ID to string for consistent handling
        $this->id = (string) $id;

        $this->attributes = new Collection($attributes);
        $this->conditions = $this->normalizeConditions($conditions);

        // Store raw price as-is (no transformation)
        $this->rawPrice = is_string($price) ? $this->sanitizeStringPrice($price) : $price;

        $this->validateCartItem();
    }

    /**
     * Sanitize string price input
     */
    private function sanitizeStringPrice(string $price): float|int
    {
        if (is_null($price)) {
            return 0;
        }

        // Only sanitize string input - no transformation
        if (is_string($price)) {
            $price = str_replace([',', '$', '€', '£', '¥', '₹', 'RM', ' '], '', $price);

            return str_contains($price, '.') ? (float) $price : (int) $price;
        }

        // Return numeric values as-is
        return $price;
    }

    /**
     * Magic getter for properties - returns raw values for backward compatibility
     */
    public function __get(string $name): mixed
    {
        if ($name === 'price') {
            return $this->rawPrice;
        }

        throw new \InvalidArgumentException("Property '{$name}' does not exist on CartItem");
    }

    /**
     * Magic isset to support collection operations like pluck
     */
    public function __isset(string $name): bool
    {
        return $name === 'price';
    }

    /**
     * Set item quantity
     */
    public function setQuantity(int $quantity): static
    {
        if ($quantity < 1) {
            throw new \MasyukAI\Cart\Exceptions\InvalidCartItemException('Quantity must be at least 1');
        }

        return new static(
            $this->id,
            $this->name,
            $this->rawPrice,
            $quantity,
            $this->attributes->toArray(),
            $this->conditions->toArray(),
            $this->associatedModel
        );
    }

    /**
     * Check if two cart items are equal
     */
    public function equals(CartItem $other): bool
    {
        return $this->id === $other->id;
    }

    /**
     * Create a copy of the item with modified properties
     */
    public function with(array $attributes): static
    {
        return new static(
            $attributes['id'] ?? $this->id,
            $attributes['name'] ?? $this->name,
            $attributes['price'] ?? $this->rawPrice,
            $attributes['quantity'] ?? $this->quantity,
            $attributes['attributes'] ?? $this->attributes->toArray(),
            $attributes['conditions'] ?? $this->conditions->toArray(),
            $attributes['associated_model'] ?? $this->associatedModel
        );
    }

    /**
     * Normalize conditions to CartConditionCollection
     */
    private function normalizeConditions(array|Collection $conditions): CartConditionCollection
    {
        if ($conditions instanceof CartConditionCollection) {
            return $conditions;
        }

        $collection = new CartConditionCollection;

        if (is_array($conditions)) {
            foreach ($conditions as $key => $condition) {
                if ($condition instanceof CartCondition) {
                    $collection->put($condition->getName(), $condition);
                } elseif (is_array($condition)) {
                    $collection->put($key, CartCondition::fromArray($condition));
                }
            }
        } elseif ($conditions instanceof Collection) {
            foreach ($conditions as $key => $condition) {
                if ($condition instanceof CartCondition) {
                    $collection->put($condition->getName(), $condition);
                }
            }
        }

        return $collection;
    }
}
