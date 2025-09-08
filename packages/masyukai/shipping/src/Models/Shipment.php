<?php

declare(strict_types=1);

namespace MasyukAI\Shipping\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shippable_type',
        'shippable_id', 
        'provider',
        'method',
        'tracking_number',
        'status',
        'origin_address',
        'destination_address',
        'weight',
        'dimensions',
        'cost',
        'currency',
        'metadata',
        'shipped_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'origin_address' => 'array',
            'destination_address' => 'array',
            'dimensions' => 'array',
            'metadata' => 'array',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    /**
     * Get the shippable model (order, cart, etc.).
     */
    public function shippable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the tracking events for this shipment.
     */
    public function trackingEvents(): HasMany
    {
        return $this->hasMany(ShipmentTrackingEvent::class)->orderBy('event_date');
    }

    /**
     * Get the latest tracking event.
     */
    public function latestTrackingEvent(): ?ShipmentTrackingEvent
    {
        return $this->trackingEvents()->latest('event_date')->first();
    }

    /**
     * Check if the shipment is delivered.
     */
    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if the shipment is in transit.
     */
    public function isInTransit(): bool
    {
        return in_array($this->status, ['dispatched', 'in_transit']);
    }

    /**
     * Get formatted cost.
     */
    public function getFormattedCostAttribute(): string
    {
        return number_format($this->cost / 100, 2);
    }

    /**
     * Get table name from config.
     */
    public function getTable(): string
    {
        return config('shipping.database.shipments_table', 'shipments');
    }
}