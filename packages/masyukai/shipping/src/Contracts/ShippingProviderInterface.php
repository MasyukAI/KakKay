<?php

declare(strict_types=1);

namespace MasyukAI\Shipping\Contracts;

use MasyukAI\Shipping\Models\Shipment;

interface ShippingProviderInterface
{
    /**
     * Get available shipping methods for this provider.
     */
    public function getShippingMethods(): array;

    /**
     * Calculate shipping cost for given parameters.
     */
    public function calculateCost(array $items, string $method, array $destination = []): int;

    /**
     * Create a shipment with the provider.
     */
    public function createShipment(Shipment $shipment): array;

    /**
     * Get tracking information for a shipment.
     */
    public function getTrackingInfo(string $trackingNumber): array;

    /**
     * Get shipping quotes for given parameters.
     */
    public function getQuotes(array $items, array $destination = []): array;

    /**
     * Validate that the provider can handle the given destination.
     */
    public function canShipTo(array $destination): bool;
}