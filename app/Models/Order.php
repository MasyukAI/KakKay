<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MasyukAI\Shipping\Traits\HasShipping;

class Order extends Model
{
    use HasFactory, HasShipping;

    protected $fillable = [
        'order_number',
        'user_id',
        'address_id',
        'cart_items',
        'delivery_method',
        'checkout_form_data',
        'status',
        'total',
    ];

    protected $casts = [
        'cart_items' => 'array',
        'checkout_form_data' => 'array',
        'total' => 'integer',
    ];

    /**
     * Get the user that owns this order
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the address for this order
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * Get payments for this order
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get order items for this order
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get order status histories
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'RM '.number_format($this->total / 100, 2);
    }

    /**
     * Get the latest payment
     */
    public function latestPayment(): ?Payment
    {
        return $this->payments()->latest()->first();
    }

    /**
     * Check if order is paid
     */
    public function isPaid(): bool
    {
        return $this->latestPayment()?->status === 'completed';
    }

    /**
     * Check if order is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if order has failed payments
     */
    public function hasFailedPayments(): bool
    {
        return $this->payments()->where('status', 'failed')->exists();
    }

    /**
     * Generate order number
     */
    public static function generateOrderNumber(): string
    {
        return 'ORDER-'.now()->format('Ymd').'-'.strtoupper(uniqid());
    }

    /**
     * Get total weight of all items in the order
     */
    public function getTotalWeightAttribute(): float
    {
        return $this->orderItems->sum('total_weight');
    }

    /**
     * Get total quantity of all items in the order
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->orderItems->sum('quantity');
    }

    /**
     * Check if order requires shipping
     */
    public function requiresShipping(): bool
    {
        return $this->orderItems->some(fn ($item) => $item->requiresShipping());
    }

    /**
     * Get subtotal from order items (without shipping/tax)
     */
    public function getSubtotalAttribute(): int
    {
        return $this->orderItems->sum('total_price');
    }

    /**
     * Get shipping items for rate calculation.
     */
    public function getShippingItems(): array
    {
        return $this->orderItems->map(function ($item) {
            return [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'weight' => $item->weight ?? 100, // default 100g
                'is_digital' => $item->is_digital ?? false,
            ];
        })->toArray();
    }

    /**
     * Create a shipment for this order.
     */
    public function ship(string $method = 'standard', string $provider = 'local'): \MasyukAI\Shipping\Models\Shipment
    {
        $destination = $this->address ? [
            'name' => $this->address->name,
            'line1' => $this->address->address_line_1,
            'line2' => $this->address->address_line_2,
            'city' => $this->address->city,
            'state' => $this->address->state,
            'postal_code' => $this->address->postal_code,
            'country' => $this->address->country,
        ] : [];

        $shippingCost = $this->calculateShippingCost($method, $destination);

        return $this->createShipment([
            'provider' => $provider,
            'method' => $method,
            'destination_address' => $destination,
            'weight' => $this->total_weight,
            'cost' => $shippingCost,
        ]);
    }
}
