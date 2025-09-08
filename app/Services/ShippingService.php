<?php

namespace App\Services;

use MasyukAI\Shipping\Facades\Shipping;

class ShippingService
{
    /**
     * Calculate shipping cost based on delivery method
     */
    public function calculateShipping(string $method, array $options = []): int
    {
        // Delegate to the new shipping package
        $items = $options['items'] ?? [];
        $destination = $options['destination'] ?? [];
        
        return Shipping::calculateCost($items, $method, $destination);
    }

    /**
     * Get available shipping methods
     */
    public function getAvailableShippingMethods(): array
    {
        return Shipping::getShippingMethods();
    }

    /**
     * Get shipping method by ID
     */
    public function getShippingMethodById(string $id): ?array
    {
        $methods = $this->getAvailableShippingMethods();

        return $methods[$id] ?? null;
    }

    /**
     * Calculate shipping based on weight and dimensions
     */
    public function calculateShippingByWeight(int $totalWeight, string $method = 'standard'): int
    {
        $items = [['weight' => $totalWeight, 'quantity' => 1]];
        
        return Shipping::calculateCost($items, $method);
    }

    /**
     * Check if shipping is required for the order
     */
    public function isShippingRequired(array $cartItems): bool
    {
        foreach ($cartItems as $item) {
            // If any item requires shipping, the whole order requires shipping
            if (! ($item['is_digital'] ?? false)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get shipping quotes for the given items
     */
    public function getShippingQuotes(array $items, array $destination = []): array
    {
        return Shipping::getQuotes($items, $destination);
    }
}
