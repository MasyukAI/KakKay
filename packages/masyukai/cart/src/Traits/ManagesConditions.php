<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Traits;

use MasyukAI\Cart\Collections\CartConditionCollection;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Exceptions\InvalidCartConditionException;

trait ManagesConditions
{
    /**
     * Add a cart condition
     */
    private function addCartCondition(CartCondition $condition): void
    {
        $conditions = $this->getConditions();
        $conditions->put($condition->getName(), $condition);
        $conditionsArray = $conditions->toArray();
        $this->storage->putConditions($this->getIdentifier(), $this->getStorageInstanceName(), $conditionsArray);
    }

    /**
     * Add condition to cart
     */
    public function condition(CartCondition|array $condition): static
    {
        $conditions = is_array($condition) ? $condition : [$condition];

        foreach ($conditions as $cond) {
            if (! $cond instanceof CartCondition) {
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
        $conditions = $this->storage->getConditions($this->getIdentifier(), $this->getStorageInstanceName());

        if (! $conditions || ! is_array($conditions)) {
            return new CartConditionCollection;
        }

        // Convert array back to CartConditionCollection
        $collection = new CartConditionCollection;
        foreach ($conditions as $conditionData) {
            if (is_array($conditionData) && isset($conditionData['name'])) {
                $condition = CartCondition::fromArray($conditionData);
                $collection->put($condition->getName(), $condition);
            }
        }

        return $collection;
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

        if (! $conditions->has($name)) {
            return false;
        }

        $conditions->forget($name);
        $conditionsArray = $conditions->toArray();
        $this->storage->putConditions($this->getIdentifier(), $this->getStorageInstanceName(), $conditionsArray);

        return true;
    }

    /**
     * Clear all cart conditions
     */
    public function clearConditions(): bool
    {
        $this->storage->putConditions($this->getIdentifier(), $this->getStorageInstanceName(), []);

        return true;
    }
    


    /**
     * Add condition to specific item
     */
    public function addItemCondition(string $itemId, CartCondition $condition): bool
    {
        $cartItems = $this->getItems();

        if (! $cartItems->has($itemId)) {
            return false;
        }

        $item = $cartItems->get($itemId);
        $item = $item->addCondition($condition);
        $cartItems->put($itemId, $item);
        $this->save($cartItems);

        return true;
    }

    /**
     * Remove condition from specific item
     */
    public function removeItemCondition(string $itemId, string $conditionName): bool
    {
        $cartItems = $this->getItems();

        if (! $cartItems->has($itemId)) {
            return false;
        }

        $item = $cartItems->get($itemId);

        // Check if the condition exists before removing
        if (! $item->conditions->has($conditionName)) {
            return false;
        }

        $item = $item->removeCondition($conditionName);
        $cartItems->put($itemId, $item);
        $this->save($cartItems);

        return true;
    }

    /**
     * Clear all conditions from specific item
     */
    public function clearItemConditions(string $itemId): bool
    {
        $cartItems = $this->getItems();

        if (! $cartItems->has($itemId)) {
            return false;
        }

        $item = $cartItems->get($itemId);
        $item = $item->clearConditions();
        $cartItems->put($itemId, $item);
        $this->save($cartItems);

        return true;
    }

    /**
     * Add a simple discount condition (shopping-cart style)
     */
    public function addDiscount(string $name, string $value, string $target = 'subtotal'): static
    {
        // Ensure discount values are negative
        if (! str_starts_with($value, '-')) {
            $value = '-'.$value;
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
     * Add a shipping condition (shopping-cart style)
     *
     * @param string $name The name of the shipping condition
     * @param string|float $value The value of the shipping fee (e.g. '15.00', '+15', etc.)
     * @param string $method The shipping method identifier (e.g. 'standard', 'express')
     * @param array $attributes Additional attributes to store with the condition
     * @return static
     */
    public function addShipping(string $name, string|float $value, string $method = 'standard', array $attributes = []): static
    {
        // Ensure value is prefixed with + if it's a string and doesn't start with an operator
        if (is_string($value) && ! preg_match('/^[+\-*\/%]/', $value)) {
            $value = '+'.$value;
        }

        // Merge the attributes with the shipping method
        $shippingAttributes = array_merge($attributes, [
            'method' => $method,
            'description' => $name
        ]);

        // Remove any existing shipping conditions first
        $this->removeShipping();

        // Create and add the condition
        $condition = new CartCondition(
            name: $name,
            type: 'shipping',
            target: 'subtotal',
            value: $value,
            attributes: $shippingAttributes
        );
        $this->condition($condition);

        return $this;
    }

    /**
     * Remove all shipping conditions from the cart
     * 
     * @return void
     */
    public function removeShipping(): void
    {
        // Get all conditions
        $conditions = $this->getConditions();
        
        // Find and remove shipping conditions
        foreach ($conditions as $condition) {
            if ($condition->getType() === 'shipping') {
                $this->removeCondition($condition->getName());
            }
        }
    }

    /**
     * Get the current shipping condition if any
     * 
     * @return \MasyukAI\Cart\Conditions\CartCondition|null
     */
    public function getShipping(): ?\MasyukAI\Cart\Conditions\CartCondition
    {
        // Get all conditions
        $conditions = $this->getConditions();
        
        // Find the first shipping condition
        foreach ($conditions as $condition) {
            if ($condition->getType() === 'shipping') {
                return $condition;
            }
        }
        
        return null;
    }
    
    /**
     * Get the shipping method from the cart condition
     * 
     * @return string|null
     */
    public function getShippingMethod(): ?string
    {
        $shipping = $this->getShipping();
        
        if ($shipping) {
            $attributes = $shipping->getAttributes();
            return $attributes['method'] ?? null;
        }
        
        return null;
    }

    /**
     * Get shipping condition value
     */
    public function getShippingValue(): ?float
    {
        $shipping = $this->getShipping();
        
        if ($shipping) {
            $value = $shipping->getValue();
            
            // Parse the value to remove operator and convert to float
            if (is_string($value) && preg_match('/^([+\-*\/%])(.+)$/', $value, $matches)) {
                $operator = $matches[1];
                $numericValue = (float) $matches[2];
                
                return $operator === '-' ? -$numericValue : $numericValue;
            }
            
            return (float) $value;
        }
        
        return null;
    }

    /**
     * Apply cart conditions to subtotal
     */
    private function applyCartConditions(float $subtotal): float
    {
        $conditions = $this->getConditions()
            ->filter(fn (CartCondition $condition) => in_array($condition->getTarget(), ['total', 'subtotal']));

        return $conditions->applyAll($subtotal);
    }
}
