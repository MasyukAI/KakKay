<?php

namespace App\Services;

use MasyukAI\Cart\Facades\Cart;

class CheckoutHelperService
{
    protected ShippingService $shippingService;

    public function __construct(
        ?ShippingService $shippingService = null
    ) {
        $this->shippingService = $shippingService ?? new ShippingService;
    }

    /**
     * Apply shipping to the cart based on the selected method
     *
     * @param string $method The shipping method ID (e.g. 'standard', 'express')
     * @return void
     */
    public function applyShippingToCart(string $method = 'standard'): void
    {
        $availableMethods = $this->shippingService->getAvailableShippingMethods();
        
        // Find the selected shipping method
        $selectedMethod = null;
        foreach ($availableMethods as $availableMethod) {
            if ($availableMethod['id'] === $method) {
                $selectedMethod = $availableMethod;
                break;
            }
        }
        
        // If method found, apply it to the cart
        if ($selectedMethod) {
            Cart::addShipping(
                name: $selectedMethod['name'], 
                value: $selectedMethod['price'] / 100, // Convert from cents to whole number
                method: $selectedMethod['id'],
                attributes: [
                    'description' => $selectedMethod['description'],
                    'estimated_days' => $selectedMethod['estimated_days'],
                ]
            );
        } else {
            // If method not found, default to standard (free) shipping
            $standardMethod = $availableMethods[0] ?? null;
            
            if ($standardMethod) {
                Cart::addShipping(
                    name: $standardMethod['name'],
                    value: $standardMethod['price'] / 100,
                    method: $standardMethod['id'],
                    attributes: [
                        'description' => $standardMethod['description'],
                        'estimated_days' => $standardMethod['estimated_days'],
                    ]
                );
            }
        }
    }
    
    /**
     * Get the current shipping method from the cart
     *
     * @return string|null
     */
    public function getCurrentShippingMethod(): ?string
    {
        return Cart::getShippingMethod();
    }
    
    /**
     * Get the current shipping value
     *
     * @return float|null
     */
    public function getCurrentShippingValue(): ?float
    {
        return Cart::getShippingValue();
    }
}
