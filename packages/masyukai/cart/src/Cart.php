<?php

declare(strict_types=1);

namespace MasyukAI\Cart;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;
use MasyukAI\Cart\Collections\CartCollection;
use MasyukAI\Cart\Collections\CartConditionCollection;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Events\CartCleared;
use MasyukAI\Cart\Events\CartCreated;
use MasyukAI\Cart\Events\CartUpdated;
use MasyukAI\Cart\Events\ItemAdded;
use MasyukAI\Cart\Events\ItemRemoved;
use MasyukAI\Cart\Events\ItemUpdated;
use MasyukAI\Cart\Exceptions\InvalidCartConditionException;
use MasyukAI\Cart\Exceptions\InvalidCartItemException;
use MasyukAI\Cart\Exceptions\UnknownModelException;
use MasyukAI\Cart\Models\CartItem;
use MasyukAI\Cart\Storage\StorageInterface;

readonly class Cart
{
    private string $instanceName;

    public function __construct(
        private StorageInterface $storage,
        private ?Dispatcher $events = null,
        string $instanceName = 'default',
        private bool $eventsEnabled = true,
        private array $config = []
    ) {
        $this->instanceName = $instanceName;

        if ($this->eventsEnabled && $this->events) {
            $this->events->dispatch(new CartCreated($this));
        }
    }

    /**
     * Get the current instance name
     */
    public function instance(): string
    {
        return $this->instanceName;
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
     * Add item(s) to the cart
     */
    public function add(
        string|array $id,
        ?string $name = null,
        float|string|null $price = null,
        int $quantity = 1,
        array $attributes = [],
        array|CartCondition|null $conditions = null,
        string|object|null $associatedModel = null
    ): CartItem|CartCollection {
        // Handle array input for multiple items
        if (is_array($id)) {
            return $this->addMultiple($id);
        }

        // Create cart item
        $item = $this->createCartItem([
            'id' => $id,
            'name' => $name,
            'price' => $this->normalizePrice($price),
            'quantity' => $quantity,
            'attributes' => $attributes,
            'conditions' => $conditions,
            'associated_model' => $associatedModel,
        ]);

        // Check if item already exists in cart
        $cartContent = $this->getContent();
        if ($cartContent->has($id)) {
            // Update existing item quantity
            $existingItem = $cartContent->get($id);
            $item = $item->setQuantity($existingItem->quantity + $quantity);
        }

        // Store in cart
        $cartContent->put($id, $item);
        $this->save($cartContent);

        if ($this->eventsEnabled && $this->events) {
            $this->events->dispatch(new ItemAdded($item, $this));
        }

        return $item;
    }

    /**
     * Add multiple items to cart
     */
    private function addMultiple(array $items): CartCollection
    {
        $cartItems = new CartCollection();

        foreach ($items as $item) {
            $cartItem = $this->add(
                $item['id'],
                $item['name'] ?? null,
                $item['price'] ?? null,
                $item['quantity'] ?? 1,
                $item['attributes'] ?? [],
                $item['conditions'] ?? null,
                $item['associated_model'] ?? null
            );

            $cartItems->put($cartItem->id, $cartItem);
        }

        return $cartItems;
    }

    /**
     * Update cart item
     */
    public function update(string $id, array $data): ?CartItem
    {
        $cartContent = $this->getContent();

        if (!$cartContent->has($id)) {
            return null;
        }

        $item = $cartContent->get($id);

        // Handle quantity updates
        if (isset($data['quantity'])) {
            $quantity = $data['quantity'];

            if (is_array($quantity)) {
                // Absolute quantity update
                $newQuantity = $quantity['value'] ?? 0;
            } else {
                // Relative quantity update (default behavior)
                $newQuantity = $item->quantity + $quantity;
            }

            $item = $item->setQuantity(max(0, $newQuantity));
        }

        // Update other properties
        foreach (['name', 'price', 'attributes'] as $property) {
            if (isset($data[$property])) {
                $method = 'set' . ucfirst($property);
                $value = $property === 'price' ? $this->normalizePrice($data[$property]) : $data[$property];
                $item = $item->$method($value);
            }
        }

        // Remove item if quantity is 0
        if ($item->quantity <= 0) {
            return $this->remove($id);
        }

        // Update cart
        $cartContent->put($id, $item);
        $this->save($cartContent);

        if ($this->eventsEnabled && $this->events) {
            $this->events->dispatch(new ItemUpdated($item, $this));
        }

        return $item;
    }

    /**
     * Remove item from cart
     */
    public function remove(string $id): ?CartItem
    {
        $cartContent = $this->getContent();

        if (!$cartContent->has($id)) {
            return null;
        }

        $item = $cartContent->get($id);
        $cartContent->forget($id);
        $this->save($cartContent);

        if ($this->eventsEnabled && $this->events) {
            $this->events->dispatch(new ItemRemoved($item, $this));
        }

        return $item;
    }

    /**
     * Get cart item by ID
     */
    public function get(string $id): ?CartItem
    {
        return $this->getContent()->get($id);
    }

    /**
     * Get all cart contents
     */
    public function getContent(): CartCollection
    {
        $content = $this->storage->get($this->getStorageKey());

        if (!$content instanceof CartCollection) {
            return new CartCollection();
        }

        return $content;
    }

    /**
     * Get all cart contents (alias for more intuitive API)
     */
    public function content(): CartCollection
    {
        return $this->getContent();
    }

    /**
     * Check if cart is empty
     */
    public function isEmpty(): bool
    {
        return $this->getContent()->isEmpty();
    }

    /**
     * Check if cart has item with given ID
     */
    public function has(string $id): bool
    {
        return $this->getContent()->has($id);
    }

    /**
     * Get total quantity of all items
     */
    public function getTotalQuantity(): int
    {
        return $this->getContent()->sum('quantity');
    }

    /**
     * Get count of unique items in cart
     */
    public function countItems(): int
    {
        return $this->getContent()->count();
    }

    /**
     * Search cart content with callback
     */
    public function search(callable $callback): CartCollection
    {
        return $this->getContent()->filter($callback);
    }

    /**
     * Get cart subtotal (before conditions)
     */
    public function getSubTotal(): float
    {
        return $this->getContent()->sum(fn (CartItem $item) => $item->getPriceSum());
    }

    /**
     * Get cart subtotal (alias for more intuitive API)
     */
    public function subtotal(): float
    {
        return $this->getSubTotal();
    }

    /**
     * Get cart subtotal with item conditions applied
     */
    public function getSubTotalWithConditions(): float
    {
        return $this->getContent()->sum(fn (CartItem $item) => $item->getPriceSumWithConditions());
    }

    /**
     * Get cart total with all conditions applied
     */
    public function getTotal(): float
    {
        $subtotal = $this->getSubTotalWithConditions();
        return $this->applyCartConditions($subtotal);
    }

    /**
     * Get cart total (alias for more intuitive API)
     */
    public function total(): float
    {
        return $this->getTotal();
    }

    /**
     * Clear the entire cart
     */
    public function clear(): bool
    {
        $this->storage->forget($this->getStorageKey());

        if ($this->eventsEnabled && $this->events) {
            $this->events->dispatch(new CartCleared($this));
        }

        return true;
    }

    /**
     * Merge another cart instance with this one (shopping-cart style)
     */
    public function merge(string $instanceName): static
    {
        // Get the content from the other instance
        $otherStorageKey = "cart.{$instanceName}";
        $otherContent = $this->storage->get($otherStorageKey);
        
        if ($otherContent instanceof CartCollection && !$otherContent->isEmpty()) {
            // Merge items
            foreach ($otherContent as $item) {
                $this->add(
                    $item->id,
                    $item->name,
                    $item->price,
                    $item->quantity,
                    $item->attributes->toArray(),
                    $item->conditions->toArray(),
                    $item->associatedModel
                );
            }

            // Clear the merged cart
            $this->storage->forget($otherStorageKey);
        }

        return $this;
    }

    /**
     * Store cart data (shopping-cart style)
     */
    public function store(): void
    {
        // Cart is automatically stored, but this provides explicit control
        $this->save($this->getContent());
        
        // Note: No specific event needed for store operation
    }

    /**
     * Restore cart data (shopping-cart style)
     */
    public function restore(): void
    {
        // Cart is automatically loaded, but this provides explicit control
        $this->getContent();
        
        // Note: No specific event needed for restore operation
    }

    /**
     * Add condition to cart
     */
    public function condition(CartCondition|array $condition): static
    {
        $conditions = is_array($condition) ? $condition : [$condition];

        foreach ($conditions as $cond) {
            if (!$cond instanceof CartCondition) {
                throw new InvalidCartConditionException('Condition must be an instance of CartCondition');
            }

            $this->addCartCondition($cond);
        }

        return $this;
    }

    /**
     * Get cart conditions
     */
    public function getConditions(): CartConditionCollection
    {
        $conditions = $this->storage->get($this->getConditionsStorageKey());

        if (!$conditions instanceof CartConditionCollection) {
            return new CartConditionCollection();
        }

        return $conditions;
    }

    /**
     * Get condition by name
     */
    public function getCondition(string $name): ?CartCondition
    {
        return $this->getConditions()->get($name);
    }

    /**
     * Remove cart condition by name
     */
    public function removeCondition(string $name): bool
    {
        $conditions = $this->getConditions();
        
        if (!$conditions->has($name)) {
            return false;
        }
        
        $conditions->forget($name);
        $this->storage->put($this->getConditionsStorageKey(), $conditions);

        return true;
    }

    /**
     * Clear all cart conditions
     */
    public function clearConditions(): bool
    {
        $this->storage->forget($this->getConditionsStorageKey());
        return true;
    }

    /**
     * Add condition to specific item
     */
    public function addItemCondition(string $itemId, CartCondition $condition): bool
    {
        $cartContent = $this->getContent();

        if (!$cartContent->has($itemId)) {
            return false;
        }

        $item = $cartContent->get($itemId);
        $item = $item->addCondition($condition);
        $cartContent->put($itemId, $item);
        $this->save($cartContent);

        return true;
    }

    /**
     * Remove condition from specific item
     */
    public function removeItemCondition(string $itemId, string $conditionName): bool
    {
        $cartContent = $this->getContent();

        if (!$cartContent->has($itemId)) {
            return false;
        }

        $item = $cartContent->get($itemId);
        
        // Check if the condition exists before removing
        if (!$item->conditions->has($conditionName)) {
            return false;
        }
        
        $item = $item->removeCondition($conditionName);
        $cartContent->put($itemId, $item);
        $this->save($cartContent);

        return true;
    }

    /**
     * Clear all conditions from specific item
     */
    public function clearItemConditions(string $itemId): bool
    {
        $cartContent = $this->getContent();

        if (!$cartContent->has($itemId)) {
            return false;
        }

        $item = $cartContent->get($itemId);
        $item = $item->clearConditions();
        $cartContent->put($itemId, $item);
        $this->save($cartContent);

        return true;
    }

    /**
     * Add a simple discount condition (shopping-cart style)
     */
    public function addDiscount(string $name, string $value, string $target = 'subtotal'): static
    {
        // Ensure discount values are negative
        if (!str_starts_with($value, '-')) {
            $value = '-' . $value;
        }
        $condition = new CartCondition($name, 'discount', $target, $value);
        $this->condition($condition);
        return $this;
    }

    /**
     * Add a simple fee condition (shopping-cart style)
     */
    public function addFee(string $name, string $value, string $target = 'subtotal'): static
    {
        $condition = new CartCondition($name, 'fee', $target, $value);
        $this->condition($condition);
        return $this;
    }

    /**
     * Add a simple tax condition (shopping-cart style)
     */
    public function addTax(string $name, string $value, string $target = 'subtotal'): static
    {
        $condition = new CartCondition($name, 'tax', $target, $value);
        $this->condition($condition);
        return $this;
    }

    /**
     * Count items in cart (total quantity, shopping-cart style)
     */
    public function count(): int
    {
        return $this->getTotalQuantity();
    }

    /**
     * Convert cart to array
     */
    public function toArray(): array
    {
        return [
            'instance' => $this->instanceName,
            'items' => $this->getContent()->toArray(),
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
     * Get the storage key for this cart instance
     */
    private function getStorageKey(): string
    {
        return "cart.{$this->instanceName}";
    }

    /**
     * Get the storage key for cart conditions
     */
    private function getConditionsStorageKey(): string
    {
        return "cart_conditions.{$this->instanceName}";
    }

    /**
     * Save cart content to storage
     */
    private function save(CartCollection $content): void
    {
        $this->storage->put($this->getStorageKey(), $content);
    }

    /**
     * Create a cart item from array data
     */
    private function createCartItem(array $data): CartItem
    {
        $this->validateCartItem($data);

        return new CartItem(
            id: $data['id'],
            name: $data['name'],
            price: $data['price'],
            quantity: $data['quantity'],
            attributes: $data['attributes'] ?? [],
            conditions: $data['conditions'] ?? [],
            associatedModel: $data['associated_model'] ?? null
        );
    }

    /**
     * Validate cart item data
     */
    private function validateCartItem(array $data): void
    {
        if (empty($data['id'])) {
            throw new InvalidCartItemException('Cart item ID is required');
        }

        if (empty($data['name'])) {
            throw new InvalidCartItemException('Cart item name is required');
        }

        if (!is_numeric($data['price']) || $data['price'] < 0) {
            throw new InvalidCartItemException('Cart item price must be a positive number');
        }

        if (!is_int($data['quantity']) || $data['quantity'] < 1) {
            throw new InvalidCartItemException('Cart item quantity must be a positive integer');
        }

        // Validate associated model if provided
        if (isset($data['associated_model']) && is_string($data['associated_model'])) {
            if (!class_exists($data['associated_model'])) {
                throw new UnknownModelException("Model {$data['associated_model']} does not exist");
            }
        }
    }

    /**
     * Normalize price to float
     */
    private function normalizePrice(float|string|null $price): float
    {
        if (is_null($price)) {
            return 0.0;
        }

        if (is_string($price)) {
            $price = (float) str_replace(',', '', $price);
        }

        return round($price, $this->config['decimals'] ?? 2);
    }

    /**
     * Add cart condition
     */
    private function addCartCondition(CartCondition $condition): void
    {
        $conditions = $this->getConditions();
        $conditions->put($condition->getName(), $condition);
        $this->storage->put($this->getConditionsStorageKey(), $conditions);
    }

    /**
     * Apply cart conditions to subtotal
     */
    private function applyCartConditions(float $subtotal): float
    {
        $conditions = $this->getConditions()
            ->filter(fn (CartCondition $condition) => in_array($condition->getTarget(), ['total', 'subtotal']))
            ->sortBy('order');

        foreach ($conditions as $condition) {
            $subtotal = $condition->apply($subtotal);
        }

        return $subtotal;
    }

    /**
     * Get the current instance name
     */
    public function getCurrentInstance(): string
    {
        return $this->instanceName;
    }
}
