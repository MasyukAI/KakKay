<?php

declare(strict_types=1);

namespace MasyukAI\Shipping\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentTrackingEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'status',
        'description',
        'location',
        'event_date',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the shipment this tracking event belongs to.
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * Get table name from config.
     */
    public function getTable(): string
    {
        return config('shipping.database.tracking_events_table', 'shipment_tracking_events');
    }
}