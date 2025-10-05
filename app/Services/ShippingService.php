<?php

declare(strict_types=1);

namespace App\Services;

final class ShippingService
{
    /**
     * Calculate shipping cost based on delivery method
     */
    public function calculateShipping(string $method, array $options = []): int
    {
        return match ($method) {
            'express' => 4900, // RM49
            'fast' => 1500,    // RM15
            'pickup' => 0,     // Free pickup
            default => 500,    // RM5 Standard shipping
        };
    }

    /**
     * Get available shipping methods
     */
    public function getAvailableShippingMethods(): array
    {
        return [
            [
                'id' => 'standard',
                'name' => 'Standard Shipping',
                'description' => 'Standard shipping (3-5 business days)',
                'price' => 500, // RM5
                'estimated_days' => '3-5',
            ],
            [
                'id' => 'fast',
                'name' => 'Fast Shipping',
                'description' => 'Next day delivery',
                'price' => 1500, // RM15
                'estimated_days' => '1',
            ],
            [
                'id' => 'express',
                'name' => 'Express Shipping',
                'description' => 'Same day delivery',
                'price' => 4900, // RM49
                'estimated_days' => '0',
            ],
            [
                'id' => 'pickup',
                'name' => 'Store Pickup',
                'description' => 'Pick up from our store',
                'price' => 0,
                'estimated_days' => '0',
            ],
        ];
    }

    /**
     * Get shipping method by ID
     */
    public function getShippingMethodById(string $id): ?array
    {
        $methods = $this->getAvailableShippingMethods();

        foreach ($methods as $method) {
            if ($method['id'] === $id) {
                return $method;
            }
        }

        return null;
    }

    /**
     * Calculate shipping based on weight and dimensions
     */
    public function calculateShippingByWeight(int $totalWeight, string $method = 'standard'): int
    {
        $baseShipping = $this->calculateShipping($method);

        // Add weight-based surcharge for heavy items (over 2kg)
        if ($totalWeight > 2000) { // 2kg in grams
            $extraWeight = $totalWeight - 2000;
            $weightSurcharge = ceil($extraWeight / 1000) * 500; // RM5 per extra kg

            return (int) ($baseShipping + $weightSurcharge);
        }

        return $baseShipping;
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
}
