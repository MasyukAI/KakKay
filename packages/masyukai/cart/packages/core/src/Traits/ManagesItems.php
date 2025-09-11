<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Traits;

use MasyukAI\Cart\Collections\CartCollection;
use MasyukAI\Cart\Events\ItemAdded;
use MasyukAI\Cart\Events\ItemRemoved;
use MasyukAI\Cart\Events\ItemUpdated;
use MasyukAI\Cart\Exceptions\InvalidCartItemException;
use MasyukAI\Cart\Exceptions\UnknownModelException;
use MasyukAI\Cart\Models\CartItem;
use MasyukAI\Cart\Support\CartMoney;

trait ManagesItems
{
    /**
     * Add item(s) to the cart
     */
    public function add(
        string|array $id,
        ?string $name = null,
        float|string|null $price = null,
        int $quantity = 1,
        array $attributes = [],
        array|object|null $conditions = null,
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
        $cartItems = $this->getItems();
        if ($cartItems->has($id)) {
            // Update existing item quantity
            $existingItem = $cartItems->get($id);
            $item = $item->setQuantity($existingItem->quantity + $quantity);
        }

        // Store in cart
        $cartItems->put($id, $item);
        $this->save($cartItems);

        // Track the last added item for associate() method compatibility
        $this->setLastAddedItemId($id);

        if ($this->eventsEnabled && $this->events) {
            $this->events->dispatch(new ItemAdded($item, $this));
        }

        // Evaluate dynamic conditions after adding item
        if (method_exists($this, 'evaluateDynamicConditions')) {
            $this->evaluateDynamicConditions();
        }

        return $item;
    }

    /**
     * Add multiple items to cart
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

            $cartItems->put($cartItem->id, $cartItem);
        }

        return $cartItems;
    }

    /**
     * Update cart item
     */
    public function update(string $id, array $data): ?CartItem
    {
        $cartItems = $this->getItems();

        if (! $cartItems->has($id)) {
            return null;
        }

        $item = $cartItems->get($id);

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
        if (method_exists($this, 'evaluateDynamicConditions')) {
            $this->evaluateDynamicConditions();
        }

        return $item;
    }

    /**
     * Remove item from cart
     */
    public function remove(string $id): ?CartItem
    {
        $cartItems = $this->getItems();

        if (! $cartItems->has($id)) {
            return null;
        }

        $item = $cartItems->get($id);
        $cartItems->forget($id);
        $this->save($cartItems);

        if ($this->eventsEnabled && $this->events) {
            $this->events->dispatch(new ItemRemoved($item, $this));
        }

        // Evaluate dynamic conditions after removing item
        if (method_exists($this, 'evaluateDynamicConditions')) {
            $this->evaluateDynamicConditions();
        }

        return $item;
    }

    /**
     * Get cart item by ID
     */
    public function get(string $id): ?CartItem
    {
        return $this->getItems()->get($id);
    }

    /**
     * Check if cart has item with given ID
     */
    public function has(string $id): bool
    {
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
     */
    private function createCartItem(array $data): CartItem
    {
        $this->validateCartItem($data);

        // Pass cart's decimals config as precision if available
        $precision = $this->config['decimals'] ?? null;

        return new CartItem(
            id: $data['id'],
            name: $data['name'],
            price: $data['price'],
            quantity: $data['quantity'],
            attributes: $data['attributes'] ?? [],
            conditions: $data['conditions'] ?? [],
            associatedModel: $data['associated_model'] ?? null,
            precision: $precision
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
     * Normalize price using PriceTransformer for consistent storage
     */
    private function normalizePrice(float|string|null $price): float
    {
        if (is_null($price)) {
            return 0.0;
        }

        // Use PriceTransformer for consistent price handling
        if (app()->bound(\MasyukAI\Cart\Contracts\PriceTransformerInterface::class)) {
            $transformer = app(\MasyukAI\Cart\Contracts\PriceTransformerInterface::class);
            
            // Clean string input first
            if (is_string($price)) {
                $price = str_replace([',', '$', '€', '£', ' '], '', $price);
                $price = (float) $price;
            }
            
            return $transformer->toStorage($price);
        }

        // Fallback: If cart has local decimals config, use simple rounding
        if (isset($this->config['decimals'])) {
            // Handle string normalization locally
            if (is_string($price)) {
                $price = str_replace([',', '$', '€', '£', ' '], '', $price);
            }

            return round((float) $price, $this->config['decimals']);
        }

        // Final fallback: Use CartMoney for proper parsing and normalization
        $money = CartMoney::fromAmount($price);
        return $money->getAmount();
    }
}
