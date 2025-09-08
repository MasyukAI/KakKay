<?php

declare(strict_types=1);

namespace MasyukAI\Shipping\Traits;

use MasyukAI\Shipping\Facades\Shipping;
use MasyukAI\Shipping\Models\Shipment;

trait HasShipping
{
    /**
     * Get the shipments for this model.
     */
    public function shipments()
    {
        return $this->morphMany(Shipment::class, 'shippable');
    }

    /**
     * Create a shipment for this model.
     */
    public function createShipment(array $attributes = []): Shipment
    {
        $shipment = $this->shipments()->create($attributes);
        
        // Create the shipment with the provider
        Shipping::createShipment($shipment, $attributes['provider'] ?? null);
        
        return $shipment;
    }

    /**
     * Get the active shipment for this model.
     */
    public function activeShipment(): ?Shipment
    {
        return $this->shipments()
            ->whereNotIn('status', ['delivered', 'failed'])
            ->first();
    }

    /**
     * Get shipping items for rate calculation.
     */
    public function getShippingItems(): array
    {
        // Override in your model to provide shipping items
        return [];
    }

    /**
     * Calculate shipping cost for this model.
     */
    public function calculateShippingCost(string $method, array $destination = []): int
    {
        $items = $this->getShippingItems();
        
        return Shipping::calculateCost($items, $method, $destination);
    }

    /**
     * Get shipping quotes for this model.
     */
    public function getShippingQuotes(array $destination = []): array
    {
        $items = $this->getShippingItems();
        
        return Shipping::getQuotes($items, $destination);
    }
}