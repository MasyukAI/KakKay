<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use JsonSerializable;
use MasyukAI\Cart\Collections\CartConditionCollection;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Exceptions\InvalidCartItemException;
use MasyukAI\Cart\Exceptions\UnknownModelException;

readonly class CartItem implements Arrayable, Jsonable, JsonSerializable
{
    public CartConditionCollection $conditions;
    public Collection $attributes;

    public function __construct(
        public string $id,
        public string $name,
        public float $price,
        public int $quantity,
        array $attributes = [],
        array|Collection $conditions = [],
        public string|object|null $associatedModel = null
    ) {
        $this->validateCartItem();
        $this->attributes = new Collection($attributes);
        $this->conditions = $this->normalizeConditions($conditions);
    }

    /**
     * Set item quantity
     */
    public function setQuantity(int $quantity): static
    {
        if ($quantity < 0) {
            throw new InvalidCartItemException('Quantity cannot be negative');
        }

        return new static(
            $this->id,
            $this->name,
            $this->price,
            $quantity,
            $this->attributes->toArray(),
            $this->conditions->toArray(),
            $this->associatedModel
        );
    }

    /**
     * Set item name
     */
    public function setName(string $name): static
    {
        if (empty(trim($name))) {
            throw new InvalidCartItemException('Name cannot be empty');
        }

        return new static(
            $this->id,
            trim($name),
            $this->price,
            $this->quantity,
            $this->attributes->toArray(),
            $this->conditions->toArray(),
            $this->associatedModel
        );
    }

    /**
     * Set item price
     */
    public function setPrice(float $price): static
    {
        if ($price < 0) {
            throw new InvalidCartItemException('Price cannot be negative');
        }

        return new static(
            $this->id,
            $this->name,
            $price,
            $this->quantity,
            $this->attributes->toArray(),
            $this->conditions->toArray(),
            $this->associatedModel
        );
    }

    /**
     * Set item attributes
     */
    public function setAttributes(array $attributes): static
    {
        return new static(
            $this->id,
            $this->name,
            $this->price,
            $this->quantity,
            $attributes,
            $this->conditions->toArray(),
            $this->associatedModel
        );
    }

    /**
     * Add an attribute
     */
    public function addAttribute(string $key, mixed $value): static
    {
        $attributes = $this->attributes->toArray();
        $attributes[$key] = $value;

        return $this->setAttributes($attributes);
    }

    /**
     * Remove an attribute
     */
    public function removeAttribute(string $key): static
    {
        $attributes = $this->attributes->toArray();
        unset($attributes[$key]);

        return $this->setAttributes($attributes);
    }

    /**
     * Get attribute value
     */
    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes->get($key, $default);
    }

    /**
     * Check if attribute exists
     */
    public function hasAttribute(string $key): bool
    {
        return $this->attributes->has($key);
    }

    /**
     * Add condition to item
     */
    public function addCondition(CartCondition $condition): static
    {
        $conditions = $this->conditions->toArray();
        $conditions[$condition->getName()] = $condition;

        return new static(
            $this->id,
            $this->name,
            $this->price,
            $this->quantity,
            $this->attributes->toArray(),
            $conditions,
            $this->associatedModel
        );
    }

    /**
     * Remove condition from item
     */
    public function removeCondition(string $name): static
    {
        $conditions = $this->conditions->toArray();
        unset($conditions[$name]);

        return new static(
            $this->id,
            $this->name,
            $this->price,
            $this->quantity,
            $this->attributes->toArray(),
            $conditions,
            $this->associatedModel
        );
    }

    /**
     * Clear all conditions
     */
    public function clearConditions(): static
    {
        return new static(
            $this->id,
            $this->name,
            $this->price,
            $this->quantity,
            $this->attributes->toArray(),
            [],
            $this->associatedModel
        );
    }

    /**
     * Check if item has specific condition
     */
    public function hasCondition(string $name): bool
    {
        return $this->conditions->has($name);
    }

    /**
     * Check if item has any conditions
     */
    public function hasConditions(): bool
    {
        return $this->conditions->isNotEmpty();
    }

    /**
     * Get condition by name
     */
    public function getCondition(string $name): ?CartCondition
    {
        return $this->conditions->get($name);
    }

    /**
     * Get all conditions
     */
    public function getConditions(): CartConditionCollection
    {
        return $this->conditions;
    }

    /**
     * Get price sum (price × quantity) without conditions
     */
    public function getPriceSum(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Get single price with conditions applied
     */
    public function getPriceWithConditions(): float
    {
        $price = $this->price;

        foreach ($this->conditions as $condition) {
            $price = $condition->apply($price);
        }

        return max(0, $price); // Ensure price doesn't go negative
    }

    /**
     * Get price sum with conditions applied
     */
    public function getPriceSumWithConditions(): float
    {
        return $this->getPriceWithConditions() * $this->quantity;
    }

    /**
     * Get discount amount
     */
    public function getDiscountAmount(): float
    {
        return $this->getPriceSum() - $this->getPriceSumWithConditions();
    }

    /**
     * Get discount amount (alias for more intuitive API)
     */
    public function discountAmount(): float
    {
        return $this->getDiscountAmount();
    }

    /**
     * Get final total for this item (alias for getPriceSumWithConditions)
     */
    public function finalTotal(): float
    {
        return $this->getPriceSumWithConditions();
    }

    /**
     * Update quantity using withQuantity (shopping-cart style)
     */
    public function withQuantity(int $quantity): static
    {
        return $this->setQuantity($quantity);
    }

    /**
     * Check if item is associated with a model
     */
    public function isAssociatedWith(string $modelClass): bool
    {
        if (is_string($this->associatedModel)) {
            return $this->associatedModel === $modelClass;
        }

        if (is_object($this->associatedModel)) {
            return $this->associatedModel instanceof $modelClass;
        }

        return false;
    }

    /**
     * Get associated model instance
     */
    public function getAssociatedModel(): object|string|null
    {
        return $this->associatedModel;
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
            $attributes['price'] ?? $this->price,
            $attributes['quantity'] ?? $this->quantity,
            $attributes['attributes'] ?? $this->attributes->toArray(),
            $attributes['conditions'] ?? $this->conditions->toArray(),
            $attributes['associated_model'] ?? $this->associatedModel
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'attributes' => $this->attributes->toArray(),
            'conditions' => $this->conditions->map(fn (CartCondition $condition) => $condition->toArray())->toArray(),
            'associated_model' => $this->getAssociatedModelArray(),
            'price_sum' => $this->getPriceSum(),
            'price_with_conditions' => $this->getPriceWithConditions(),
            'price_sum_with_conditions' => $this->getPriceSumWithConditions(),
            'discount_amount' => $this->getDiscountAmount(),
        ];
    }

    /**
     * Convert to JSON
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * JSON serialize
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert to string representation
     */
    public function __toString(): string
    {
        return sprintf(
            '%s (ID: %s, Price: %.2f, Qty: %d)',
            $this->name,
            $this->id,
            $this->price,
            $this->quantity
        );
    }

    /**
     * Validate cart item properties
     */
    private function validateCartItem(): void
    {
        if (empty(trim($this->id))) {
            throw new InvalidCartItemException('Cart item ID cannot be empty');
        }

        if (empty(trim($this->name))) {
            throw new InvalidCartItemException('Cart item name cannot be empty');
        }

        if ($this->price < 0) {
            throw new InvalidCartItemException('Cart item price cannot be negative');
        }

        if ($this->quantity < 1) {
            throw new InvalidCartItemException('Cart item quantity must be at least 1');
        }

        if (is_string($this->associatedModel) && !empty($this->associatedModel)) {
            if (!class_exists($this->associatedModel)) {
                throw new UnknownModelException("Model class '{$this->associatedModel}' does not exist");
            }
        }
    }

    /**
     * Normalize conditions to CartConditionCollection
     */
    private function normalizeConditions(array|Collection $conditions): CartConditionCollection
    {
        if ($conditions instanceof CartConditionCollection) {
            return $conditions;
        }

        $collection = new CartConditionCollection();

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

    /**
     * Get associated model as array representation
     */
    private function getAssociatedModelArray(): array|string|null
    {
        if (is_string($this->associatedModel)) {
            return $this->associatedModel;
        }

        if (is_object($this->associatedModel)) {
            return [
                'class' => get_class($this->associatedModel),
                'id' => $this->associatedModel->id ?? null,
                'data' => method_exists($this->associatedModel, 'toArray') ? $this->associatedModel->toArray() : (array) $this->associatedModel,
            ];
        }

        return null;
    }
}
