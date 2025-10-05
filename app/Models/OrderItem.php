<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getTotalPriceAttribute(): int
    {
        return $this->unit_price * $this->quantity;
    }

    public function getFormattedUnitPriceAttribute(): string
    {
        return 'RM '.number_format($this->unit_price / 100, 2);
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return 'RM '.number_format($this->total_price / 100, 2);
    }

    public function getTotalWeightAttribute(): float
    {
        return $this->product ? ($this->product->weight * $this->quantity) : 0;
    }

    public function requiresShipping(): bool
    {
        return $this->product ? $this->product->requiresShipping() : true;
    }
}
