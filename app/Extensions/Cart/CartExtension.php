<?php

namespace App\Extensions\Cart;

use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Facades\Cart;

/**
 * Extensions for the MasyukAI\Cart package
 */
class CartExtension
{
    /**
     * Add shipping fee to the cart as a condition
     * 
     * @param string $name The name of the shipping condition
     * @param string|float $value The value of the shipping fee (e.g. '15.00', '+15', etc.)
     * @param string $method The shipping method identifier (e.g. 'standard', 'express')
     * @param array $attributes Additional attributes to store with the condition
     * @return void
     */
    public static function addShipping(string $name, string|float $value, string $method = 'standard', array $attributes = []): void
    {
        // If the value doesn't start with + or -, add + to ensure it's treated as addition
        if (is_numeric($value) || (!str_starts_with($value, '+') && !str_starts_with($value, '-'))) {
            $value = '+' . $value;
        }
        
        // Merge the attributes with the shipping method
        $shippingAttributes = array_merge($attributes, [
            'method' => $method,
            'description' => $name
        ]);
        
        // Create a shipping condition
        $condition = new CartCondition(
            name: $name,
            type: 'shipping',
            target: 'subtotal',
            value: $value,
            attributes: $shippingAttributes
        );
        
        // Remove any existing shipping conditions
        self::removeShipping();
        
        // Add the condition to the cart
        Cart::condition($condition);
    }
    
    /**
     * Remove all shipping conditions from the cart
     * 
     * @return void
     */
    public static function removeShipping(): void
    {
        // Get all conditions
        $conditions = Cart::getConditions();
        
        // Find and remove shipping conditions
        foreach ($conditions as $condition) {
            if ($condition->getType() === 'shipping') {
                Cart::removeCartCondition($condition->getName());
            }
        }
    }
    
    /**
     * Get the current shipping condition if any
     * 
     * @return CartCondition|null
     */
    public static function getShipping(): ?CartCondition
    {
        // Get all conditions
        $conditions = Cart::getConditions();
        
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
    public static function getShippingMethod(): ?string
    {
        $shipping = self::getShipping();
        
        if ($shipping) {
            $attributes = $shipping->getAttributes();
            return $attributes['method'] ?? null;
        }
        
        return null;
    }
    
    /**
     * Get the shipping value (amount)
     * 
     * @return float|null
     */
    public static function getShippingValue(): ?float
    {
        $shipping = self::getShipping();
        
        if ($shipping) {
            $value = $shipping->getValue();
            
            // Strip + sign if present
            if (str_starts_with($value, '+')) {
                $value = substr($value, 1);
            }
            
            return (float) $value;
        }
        
        return null;
    }
}
