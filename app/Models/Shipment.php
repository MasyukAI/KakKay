<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Shipment extends Model
{
    /** @phpstan-ignore-next-line */
    use HasFactory, HasUuids;

    protected $fillable = [
        'order_id',
        'carrier',
        'service',
        'tracking_number',
        'status',
        'shipped_at',
        'delivered_at',
        'note',
        'shipping_address',
        'estimated_delivery',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'shipping_address' => 'array',
        'estimated_delivery' => 'datetime',
    ];

    /** @phpstan-ignore-next-line */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
