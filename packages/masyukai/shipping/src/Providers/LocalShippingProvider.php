<?php

declare(strict_types=1);

namespace MasyukAI\Shipping\Providers;

use MasyukAI\Shipping\Contracts\ShippingProviderInterface;
use MasyukAI\Shipping\Models\Shipment;

class LocalShippingProvider implements ShippingProviderInterface
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Get available shipping methods for this provider.
     */
    public function getShippingMethods(): array
    {
        return $this->config['methods'] ?? [];
    }

    /**
     * Calculate shipping cost for given parameters.
     */
    public function calculateCost(array $items, string $method, array $destination = []): int
    {
        $methods = $this->getShippingMethods();
        
        if (! isset($methods[$method])) {
            $method = 'standard'; // fallback to standard
        }

        $basePrice = $methods[$method]['price'] ?? 500;

        // Calculate weight-based surcharge
        $totalWeight = $this->calculateTotalWeight($items);
        $weightSurcharge = $this->calculateWeightSurcharge($totalWeight);

        return $basePrice + $weightSurcharge;
    }

    /**
     * Create a shipment with the provider.
     */
    public function createShipment(Shipment $shipment): array
    {
        // For local provider, generate a simple tracking number
        $trackingNumber = 'LOCAL-' . strtoupper(uniqid());
        
        $shipment->update([
            'tracking_number' => $trackingNumber,
            'status' => 'created',
        ]);

        return [
            'success' => true,
            'tracking_number' => $trackingNumber,
            'label_url' => null, // Local provider doesn't generate labels
        ];
    }

    /**
     * Get tracking information for a shipment.
     */
    public function getTrackingInfo(string $trackingNumber): array
    {
        // For local provider, return basic tracking info
        return [
            'tracking_number' => $trackingNumber,
            'status' => 'in_transit',
            'estimated_delivery' => now()->addDays(3)->format('Y-m-d'),
            'events' => [
                [
                    'status' => 'created',
                    'description' => 'Shipment created',
                    'location' => 'Origin',
                    'timestamp' => now()->subHours(2)->toISOString(),
                ],
                [
                    'status' => 'dispatched',
                    'description' => 'Package dispatched from origin',
                    'location' => 'Origin',
                    'timestamp' => now()->subHour()->toISOString(),
                ],
            ],
        ];
    }

    /**
     * Get shipping quotes for given parameters.
     */
    public function getQuotes(array $items, array $destination = []): array
    {
        $quotes = [];
        $methods = $this->getShippingMethods();

        foreach ($methods as $methodId => $method) {
            $cost = $this->calculateCost($items, $methodId, $destination);
            
            $quotes[] = [
                'method_id' => $methodId,
                'method_name' => $method['name'],
                'description' => $method['description'],
                'cost' => $cost,
                'estimated_days' => $method['estimated_days'],
                'provider' => 'local',
            ];
        }

        return $quotes;
    }

    /**
     * Validate that the provider can handle the given destination.
     */
    public function canShipTo(array $destination): bool
    {
        // Local provider can ship anywhere for now
        return true;
    }

    /**
     * Calculate total weight of items.
     */
    protected function calculateTotalWeight(array $items): int
    {
        $totalWeight = 0;
        
        foreach ($items as $item) {
            $weight = $item['weight'] ?? 100; // default 100g per item
            $quantity = $item['quantity'] ?? 1;
            $totalWeight += $weight * $quantity;
        }

        return $totalWeight;
    }

    /**
     * Calculate weight-based surcharge.
     */
    protected function calculateWeightSurcharge(int $totalWeight): int
    {
        $threshold = config('shipping.weight.threshold', 2000);
        $surcharge = config('shipping.weight.surcharge_per_kg', 500);

        if ($totalWeight <= $threshold) {
            return 0;
        }

        $extraWeight = $totalWeight - $threshold;
        $extraKg = ceil($extraWeight / 1000);

        return $extraKg * $surcharge;
    }
}