<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Traits;

use MasyukAI\Cart\Collections\CartCollection;
use MasyukAI\Cart\Events\CartCreated;
use MasyukAI\Cart\Events\ItemAdded;
use MasyukAI\Cart\Events\ItemRemoved;
use MasyukAI\Cart\Events\ItemUpdated;
use MasyukAI\Cart\Exceptions\InvalidCartItemException;
use MasyukAI\Cart\Exceptions\UnknownModelException;
use MasyukAI\Cart\Models\CartItem;

trait ManagesItems
{
    /**
     * Evaluate dynamic conditions if the method exists
     */
    private function evaluateDynamicConditionsIfAvailable(): void
    {
        $this->evaluateDynamicConditions();
    }

    /**
     * Add item(s) to the cart
     *
     * @param  string|int|array<array<string, mixed>>  $id
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>|object|null  $conditions
     */
    public function add(
        string|int|array $id,
        ?string $name = null,
        float|int|string|null $price = null,
        int $quantity = 1,
        array $attributes = [],
        array|object|null $conditions = null,
        string|object|null $associatedModel = null
    ): CartItem|CartCollection {
        // Handle array input for multiple items
        if (is_array($id)) {
            return $this->addMultiple($id);
        }

        // Normalize ID to string for consistent handling
        $id = (string) $id;

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
        $cartItems = $this->getItems();
        $isFirstItem = $cartItems->isEmpty();

        if ($cartItems->has($id)) {
            // Update existing item quantity
            $existingItem = $cartItems->get($id);
            assert($existingItem !== null, 'Item should exist since we checked has()');
            $item = $item->setQuantity($existingItem->quantity + $quantity);
        }

        // Store in cart
        $cartItems->put($id, $item);
        $this->save($cartItems);

        // Dispatch CartCreated event only when adding the first item to an empty cart
        if ($isFirstItem && $this->eventsEnabled && $this->events) {
            $this->events->dispatch(new CartCreated($this));
        }

        // Dispatch ItemAdded event
        if ($this->eventsEnabled && $this->events) {
            $this->events->dispatch(new ItemAdded($item, $this));
        }

        // Evaluate dynamic conditions after adding item
        $this->evaluateDynamicConditionsIfAvailable();

        return $item;
    }

    /**
     * Add multiple items to cart
     *
     * @param  array<array<string, mixed>>  $items
     */
    private function addMultiple(array $items): CartCollection
    {
        $cartItems = new CartCollection;

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

            assert($cartItem instanceof CartItem, 'add() should return CartItem when called with scalar id');
            $cartItems->put($cartItem->id, $cartItem);
        }

        return $cartItems;
    }

    /**
     * Update cart item
     *
     * @param  array<string, mixed>  $data
     */
    public function update(string|int $id, array $data): ?CartItem
    {
        // Normalize ID to string for consistent handling
        $id = (string) $id;

        $cartItems = $this->getItems();

        if (! $cartItems->has($id)) {
            return null;
        }

        $item = $cartItems->get($id);
        assert($item !== null, 'Item should exist since we checked has()');

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

            // Check for removal BEFORE creating new CartItem to avoid exceptions
            if ($newQuantity <= 0) {
                return $this->remove($id);
            }

            $item = $item->setQuantity($newQuantity);
        }

        // Update other properties
        foreach (['name', 'price', 'attributes'] as $property) {
            if (isset($data[$property])) {
                $method = 'set'.ucfirst($property);
                $value = $property === 'price' ? $this->normalizePrice($data[$property]) : $data[$property];
                $item = $item->$method($value);
            }
        }

        // Update cart
        $cartItems->put($id, $item);
        $this->save($cartItems);

        if ($this->eventsEnabled && $this->events) {
            $this->events->dispatch(new ItemUpdated($item, $this));
        }

        // Evaluate dynamic conditions after updating item
        $this->evaluateDynamicConditionsIfAvailable();

        return $item;
    }

    /**
     * Remove item from cart
     */
    public function remove(string|int $id): ?CartItem
    {
        // Normalize ID to string for consistent handling
        $id = (string) $id;

        $cartItems = $this->getItems();

        if (! $cartItems->has($id)) {
            return null;
        }

        $item = $cartItems->get($id);
        assert($item !== null, 'Item should exist since we checked has()');
        $cartItems->forget($id);
        $this->save($cartItems);

        if ($this->eventsEnabled && $this->events) {
            $this->events->dispatch(new ItemRemoved($item, $this));
        }

        // Evaluate dynamic conditions after removing item
        $this->evaluateDynamicConditionsIfAvailable();

        return $item;
    }

    /**
     * Get cart item by ID
     */
    public function get(string|int $id): ?CartItem
    {
        // Normalize ID to string for consistent handling
        $id = (string) $id;

        return $this->getItems()->get($id);
    }

    /**
     * Check if cart has item with given ID
     */
    public function has(string|int $id): bool
    {
        // Normalize ID to string for consistent handling
        $id = (string) $id;

        return $this->getItems()->has($id);
    }

    /**
     * Search cart content with callback
     */
    public function search(callable $callback): CartCollection
    {
        return $this->getItems()->filter($callback);
    }

    /**
     * Create a cart item from array data
     *
     * @param  array<string, mixed>  $data
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
     *
     * @param  array<string, mixed>  $data
     */
    private function validateCartItem(array $data): void
    {
        if (empty($data['id'])) {
            throw new InvalidCartItemException('Cart item ID is required');
        }

        if (empty($data['name'])) {
            throw new InvalidCartItemException('Cart item name is required');
        }

        if (! is_numeric($data['price']) || $data['price'] < 0) {
            throw new InvalidCartItemException('Cart item price must be a positive number');
        }

        if (! is_int($data['quantity']) || $data['quantity'] < 1) {
            throw new InvalidCartItemException('Cart item quantity must be a positive integer');
        }

        // Validate associated model if provided
        if (isset($data['associated_model']) && is_string($data['associated_model'])) {
            if (! class_exists($data['associated_model'])) {
                throw new UnknownModelException("Model {$data['associated_model']} does not exist");
            }
        }
    }

    /**
     * Normalize price input (sanitization only, no transformation)
     */
    private function normalizePrice(float|int|string|null $price): float|int
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
}
