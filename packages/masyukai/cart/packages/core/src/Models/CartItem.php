<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use JsonSerializable;
use MasyukAI\Cart\Collections\CartConditionCollection;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Exceptions\InvalidCartItemException;
use MasyukAI\Cart\Exceptions\UnknownModelException;
use MasyukAI\Cart\Support\CartMoney;
use MasyukAI\Cart\Traits\ManagesPricing;
use MasyukAI\Cart\Traits\ManagesPriceTransformation;

readonly class CartItem implements Arrayable, Jsonable, JsonSerializable
{
    use ManagesPricing;
    use ManagesPriceTransformation;

    public CartConditionCollection $conditions;

    public Collection $attributes;

    public CartMoney $cartMoney;

    /**
     * Computed price property for collection compatibility
     */
    public readonly float $price;

    /**
     * Store precision for the price property
     */
    private ?int $precision;

    public function __construct(
        public string $id,
        public string $name,
        float|int|string|CartMoney $price,
        public int $quantity,
        array $attributes = [],
        array|Collection $conditions = [],
        public string|object|null $associatedModel = null,
        ?string $currency = null,
        ?int $precision = null
    ) {
        $this->attributes = new Collection($attributes);
        $this->conditions = $this->normalizeConditions($conditions);
        $this->precision = $precision;

        if ($price instanceof CartMoney) {
            $this->cartMoney = $price;
        } else {
            $this->cartMoney = $this->createMoney($price, $currency, $precision);
        }

        $this->price = $this->precision !== null
            ? round($this->cartMoney->getAmount(), $this->precision)
            : $this->cartMoney->getAmount(); // Set readonly property for collection compatibility
        $this->validateCartItem();
    }

    /**
     * Magic getter for price property
     */
    public function __get(string $name): mixed
    {
        if ($name === 'price') {
            return $this->precision !== null
                ? round($this->cartMoney->getAmount(), $this->precision)
                : $this->cartMoney->getAmount();
        }

        throw new InvalidArgumentException("Property '{$name}' does not exist on CartItem");
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
            $this->cartMoney,
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
            $this->cartMoney,
            $this->quantity,
            $this->attributes->toArray(),
            $this->conditions->toArray(),
            $this->associatedModel
        );
    }

    /**
     * Set item price
     */
    public function setPrice(float|int|string|CartMoney $price): static
    {
        $money = $price instanceof CartMoney ? $price : $this->createMoney($price, null, $this->precision);

        if ($money->getCents() < 0) {
            throw new InvalidCartItemException('Price cannot be negative');
        }

        return new static(
            $this->id,
            $this->name,
            $money,
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
     * Get price sum (price × quantity) with item-level conditions applied (as CartMoney object)
     */
    public function getPriceSum(): CartMoney
    {
        return $this->getSumMoney();
    }

    /**
     * Get price sum (price × quantity) without conditions (as CartMoney object)
     */
    public function getPriceSumWithoutConditions(): CartMoney
    {
        return $this->getSumMoneyWithoutConditions();
    }

    /**
     * Get formatted price sum (price × quantity) with item-level conditions applied
     */
    public function getPriceSumFormatted(): string
    {
        return $this->formatMoney($this->getSumMoney());
    }

    /**
     * Get formatted price sum (price × quantity) without conditions
     */
    public function getPriceSumWithoutConditionsFormatted(): string
    {
        return $this->formatMoney($this->getSumMoneyWithoutConditions());
    }

    /**
     * Get raw price sum (price × quantity) with item-level conditions applied (internal use)
     */
    public function getRawPriceSum(): float
    {
        return $this->getSumMoney()->getAmount();
    }

    /**
     * Get raw price sum (price × quantity) without conditions (internal use)
     */
    public function getRawPriceSumWithoutConditions(): float
    {
        return $this->getSumMoneyWithoutConditions()->getAmount();
    }

    /**
     * Get raw single price without conditions (internal use)
     */
    public function getRawPriceWithoutConditions(): float
    {
        return $this->cartMoney->getAmount();
    }

    /**
     * Get raw single price with conditions applied (for internal use like calculations)
     */
    public function getRawPrice(): float
    {
        return $this->getMoney()->getAmount();
    }

    /**
     * Get sum as Money object (price × quantity) with conditions applied (internal calculations)
     */
    public function getSumMoney(): CartMoney
    {
        $price = $this->getMoney();

        return $price->multiply($this->quantity);
    }

    /**
     * Get sum as Money object (price × quantity) without conditions (internal calculations)
     */
    public function getSumMoneyWithoutConditions(): CartMoney
    {
        return $this->cartMoney->multiply($this->quantity);
    }

    /**
     * Get base price as Money object with conditions applied (internal calculations)
     */
    public function getMoney(): CartMoney
    {
        $price = $this->cartMoney;

        foreach ($this->conditions as $condition) {
            $priceFloat = $condition->apply($price->getAmount());
            // Use the higher precision from the condition calculation
            // The condition apply method now returns results with appropriate precision
            $price = $this->createMoney(max(0, $priceFloat), $price->getCurrency(), 3);
        }

        return $price;
    }

    /**
     * Get base price as Money object without conditions (internal calculations)
     */
    public function getMoneyWithoutConditions(): CartMoney
    {
        return $this->cartMoney;
    }

    /**
     * Get discount amount as CartMoney object
     */
    public function getDiscountAmount(): CartMoney
    {
        return $this->getDiscountMoney();
    }

    /**
     * Get discount amount as float (backward compatibility)
     */
    public function getRawDiscountAmount(): float
    {
        return $this->getDiscountMoney()->getAmount();
    }

    /**
     * Get discount as Money object (internal calculations)
     */
    public function getDiscountMoney(): CartMoney
    {
        $withoutConditions = $this->getSumMoneyWithoutConditions();
        $withConditions = $this->getSumMoney();

        // If precisions differ, convert to the higher precision for accurate calculation
        $maxPrecision = max($withoutConditions->getPrecision(), $withConditions->getPrecision());

        if ($withoutConditions->getPrecision() !== $maxPrecision) {
            $withoutConditions = CartMoney::fromAmount(
                $withoutConditions->getAmount(),
                $withoutConditions->getCurrency(),
                $maxPrecision
            );
        }

        if ($withConditions->getPrecision() !== $maxPrecision) {
            $withConditions = CartMoney::fromAmount(
                $withConditions->getAmount(),
                $withConditions->getCurrency(),
                $maxPrecision
            );
        }

        return $withoutConditions->subtract($withConditions);
    }

    /**
     * Get discount amount (alias for more intuitive API, as CartMoney object)
     */
    public function discountAmount(): CartMoney
    {
        return $this->getDiscountAmount();
    }

    /**
     * Get discount amount as float (alias for backward compatibility)
     */
    public function discountAmountRaw(): float
    {
        return $this->getRawDiscountAmount();
    }

    /**
     * Get item price with item-level conditions applied (as CartMoney object)
     */
    public function getPrice(): CartMoney
    {
        return $this->getMoney();
    }

    /**
     * Get item price without conditions (as CartMoney object)
     */
    public function getPriceWithoutConditions(): CartMoney
    {
        return $this->getMoneyWithoutConditions();
    }

    /**
     * Get formatted item price with item-level conditions applied
     */
    public function getPriceFormatted(): string
    {
        return $this->formatMoney($this->getMoney());
    }

    /**
     * Get formatted item price without conditions
     */
    public function getPriceWithoutConditionsFormatted(): string
    {
        return $this->formatMoney($this->getMoneyWithoutConditions());
    }

    /**
     * Get item subtotal (price * quantity, with item-level conditions applied, as CartMoney object)
     */
    public function subtotal(): CartMoney
    {
        return $this->getSumMoney();
    }

    /**
     * Get item subtotal without conditions (price * quantity, as CartMoney object)
     */
    public function subtotalWithoutConditions(): CartMoney
    {
        return $this->getSumMoneyWithoutConditions();
    }

    /**
     * Get formatted item subtotal (price * quantity, with item-level conditions applied)
     */
    public function subtotalFormatted(): string
    {
        return $this->formatMoney($this->getSumMoney());
    }

    /**
     * Get formatted item subtotal without conditions (price * quantity)
     */
    public function subtotalWithoutConditionsFormatted(): string
    {
        return $this->formatMoney($this->getSumMoneyWithoutConditions());
    }

    /**
     * Get total for this item (alias for subtotal, as CartMoney object)
     */
    public function total(): CartMoney
    {
        return $this->subtotal();
    }

    /**
     * Get formatted total for this item (alias for subtotal)
     */
    public function totalFormatted(): string
    {
        return $this->subtotalFormatted();
    }

    // ===== DISPLAY METHODS (Using Price Transformer) =====

    /**
     * Get display price (transformed from storage for UI)
     */
    public function getDisplayPrice(): float
    {
        return $this->transformFromStorage($this->getRawPrice());
    }

    /**
     * Get display price without conditions (transformed from storage for UI)
     */
    public function getDisplayPriceWithoutConditions(): float
    {
        return $this->transformFromStorage($this->getRawPriceWithoutConditions());
    }

    /**
     * Get display subtotal (transformed from storage for UI)
     */
    public function getDisplaySubtotal(): float
    {
        return $this->transformFromStorage($this->getRawPriceSum());
    }

    /**
     * Get display subtotal without conditions (transformed from storage for UI)
     */
    public function getDisplaySubtotalWithoutConditions(): float
    {
        return $this->transformFromStorage($this->getRawPriceSumWithoutConditions());
    }

    /**
     * Get formatted display price (for UI display)
     */
    public function getDisplayPriceFormatted(): string
    {
        return number_format($this->getDisplayPrice(), $this->getPricePrecision());
    }

    /**
     * Get formatted display subtotal (for UI display)
     */
    public function getDisplaySubtotalFormatted(): string
    {
        return number_format($this->getDisplaySubtotal(), $this->getPricePrecision());
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
            $attributes['price'] ?? $this->cartMoney,
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
            'price' => $this->cartMoney->getAmount(), // Store raw price (without conditions), not calculated price
            'quantity' => $this->quantity,
            'subtotal' => $this->subtotal(),
            'attributes' => $this->attributes->toArray(),
            'conditions' => $this->conditions->map(fn (CartCondition $condition) => $condition->toArray())->toArray(),
            'associated_model' => $this->getAssociatedModelArray(),
        ];
    }

    /**
     * Convert to JSON
     *
     * @param  int  $options  JSON encode options
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
            '%s (ID: %s, Price: %.2f, Quantity: %d)',
            $this->name,
            $this->id,
            $this->cartMoney->getAmount(),
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

        // Check string length limits
        $maxStringLength = config('cart.limits.max_string_length', 255);
        if (strlen($this->id) > $maxStringLength) {
            throw new InvalidCartItemException("Cart item ID cannot exceed {$maxStringLength} characters");
        }

        if (strlen($this->name) > $maxStringLength) {
            throw new InvalidCartItemException("Cart item name cannot exceed {$maxStringLength} characters");
        }

        if ($this->cartMoney->getAmount() < 0) {
            throw new InvalidCartItemException('Cart item price cannot be negative');
        }

        if ($this->quantity < 1) {
            throw new InvalidCartItemException('Cart item quantity must be at least 1');
        }

        // Check quantity limits
        $maxQuantity = config('cart.limits.max_item_quantity', 10000);
        if ($this->quantity > $maxQuantity) {
            throw new InvalidCartItemException("Cart item quantity cannot exceed {$maxQuantity}");
        }

        if (is_string($this->associatedModel) && ! empty($this->associatedModel)) {
            if (! class_exists($this->associatedModel)) {
                throw new UnknownModelException("Model class '{$this->associatedModel}' does not exist");
            }
        }

        // Validate attributes size
        if (! empty($this->attributes)) {
            $attributesArray = $this->attributes instanceof Collection ? $this->attributes->toArray() : $this->attributes;
            $this->validateDataSize($attributesArray, 'item attributes');
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

    /**
     * Validate data size to prevent memory issues
     */
    private function validateDataSize(array $data, string $type): void
    {
        $maxDataSize = config('cart.limits.max_data_size_bytes', 1024 * 1024); // 1MB default

        try {
            $jsonSize = strlen(json_encode($data, JSON_THROW_ON_ERROR));
            if ($jsonSize > $maxDataSize) {
                $maxSizeMB = round($maxDataSize / (1024 * 1024), 2);
                throw new InvalidCartItemException("Cart item {$type} data size ({$jsonSize} bytes) exceeds maximum allowed size of {$maxSizeMB}MB");
            }
        } catch (\JsonException $e) {
            throw new InvalidCartItemException("Cannot validate {$type} data size: ".$e->getMessage());
        }
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

    // ============================================================================
    // CLEAN ALIASES FOR INTUITIVE API
    // ============================================================================
    // These provide intuitive method names for common operations

    /**
     * Get base price as Money object (clean alias for getMoney)
     */
    public function money(): CartMoney
    {
        return $this->getMoney();
    }

    /**
     * Get base price as Money object without conditions (clean alias)
     */
    public function moneyWithoutConditions(): CartMoney
    {
        return $this->getMoneyWithoutConditions();
    }

    /**
     * Get sum as Money object (clean alias for getSumMoney)
     */
    public function sumMoney(): CartMoney
    {
        return $this->getSumMoney();
    }

    /**
     * Get sum as Money object without conditions (clean alias)
     */
    public function sumMoneyWithoutConditions(): CartMoney
    {
        return $this->getSumMoneyWithoutConditions();
    }

    /**
     * Get discount as Money object (clean alias for getDiscountMoney)
     */
    public function discountMoney(): CartMoney
    {
        return $this->getDiscountMoney();
    }
}
