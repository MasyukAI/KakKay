<?php

declare(strict_types=1);

namespace MasyukAI\Shipping\Services;

use MasyukAI\Shipping\Contracts\TrackingServiceInterface;
use MasyukAI\Shipping\Events\ShipmentStatusUpdated;
use MasyukAI\Shipping\Events\ShipmentDelivered;
use MasyukAI\Shipping\Models\Shipment;
use MasyukAI\Shipping\Models\ShipmentTrackingEvent;

class TrackingService implements TrackingServiceInterface
{
    /**
     * Update tracking information for a shipment.
     */
    public function updateTracking(string $trackingNumber): array
    {
        $shipment = Shipment::where('tracking_number', $trackingNumber)->first();
        
        if (! $shipment) {
            throw new \InvalidArgumentException("Shipment not found for tracking number: {$trackingNumber}");
        }

        // Get updated tracking info from the provider
        $trackingInfo = app('shipping')->getTrackingInfo($trackingNumber, $shipment->provider);
        
        $oldStatus = $shipment->status;
        $newStatus = $trackingInfo['status'] ?? $oldStatus;

        // Update shipment status if changed
        if ($oldStatus !== $newStatus) {
            $shipment->update(['status' => $newStatus]);
            
            // Fire events
            ShipmentStatusUpdated::dispatch($shipment, $oldStatus, $newStatus);
            
            if ($newStatus === 'delivered') {
                $shipment->update(['delivered_at' => now()]);
                ShipmentDelivered::dispatch($shipment);
            }
        }

        // Create tracking events from the tracking info
        if (isset($trackingInfo['events'])) {
            foreach ($trackingInfo['events'] as $eventData) {
                $this->createTrackingEvent($shipment, $eventData);
            }
        }

        return $trackingInfo;
    }

    /**
     * Get the current status of a shipment.
     */
    public function getStatus(string $trackingNumber): string
    {
        $shipment = Shipment::where('tracking_number', $trackingNumber)->first();
        
        return $shipment?->status ?? 'unknown';
    }

    /**
     * Check if a tracking number is valid.
     */
    public function isValidTrackingNumber(string $trackingNumber): bool
    {
        // Basic validation - could be enhanced per provider
        return ! empty($trackingNumber) && strlen($trackingNumber) >= 5;
    }

    /**
     * Get tracking events for a shipment.
     */
    public function getTrackingEvents(string $trackingNumber): array
    {
        $shipment = Shipment::where('tracking_number', $trackingNumber)->first();
        
        if (! $shipment) {
            return [];
        }

        return $shipment->trackingEvents()
            ->orderBy('event_date')
            ->get()
            ->map(function (ShipmentTrackingEvent $event) {
                return [
                    'status' => $event->status,
                    'description' => $event->description,
                    'location' => $event->location,
                    'timestamp' => $event->event_date->toISOString(),
                ];
            })
            ->toArray();
    }

    /**
     * Create a tracking event for a shipment.
     */
    protected function createTrackingEvent(Shipment $shipment, array $eventData): void
    {
        // Check if this event already exists
        $exists = $shipment->trackingEvents()
            ->where('status', $eventData['status'])
            ->where('event_date', $eventData['timestamp'])
            ->exists();

        if (! $exists) {
            ShipmentTrackingEvent::create([
                'shipment_id' => $shipment->id,
                'status' => $eventData['status'],
                'description' => $eventData['description'],
                'location' => $eventData['location'] ?? null,
                'event_date' => $eventData['timestamp'],
                'metadata' => $eventData['metadata'] ?? [],
            ]);
        }
    }
}