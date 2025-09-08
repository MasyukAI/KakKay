<?php

declare(strict_types=1);

namespace MasyukAI\Shipping\Contracts;

interface TrackingServiceInterface
{
    /**
     * Update tracking information for a shipment.
     */
    public function updateTracking(string $trackingNumber): array;

    /**
     * Get the current status of a shipment.
     */
    public function getStatus(string $trackingNumber): string;

    /**
     * Check if a tracking number is valid.
     */
    public function isValidTrackingNumber(string $trackingNumber): bool;

    /**
     * Get tracking events for a shipment.
     */
    public function getTrackingEvents(string $trackingNumber): array;
}