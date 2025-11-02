<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\CodeGeneratorService;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

final class Order extends Model
{
    /** @phpstan-ignore-next-line */
    use HasFactory, HasUuids;

    protected $fillable = [
        'order_number',
        'user_id',
        'address_id',
        'cart_items',
        'delivery_method',
        'checkout_form_data',
        'status',
        'total',
        'invoice_path',
        'invoice_generated_at',
    ];

    protected $casts = [
        'cart_items' => 'array',
        'checkout_form_data' => 'array',
        'total' => 'integer',
        'invoice_generated_at' => 'datetime',
    ];

    /**
     * Generate order number
     */
    public static function generateOrderNumber(): string
    {
        return CodeGeneratorService::generateOrderCode();
    }

    /**
     * Get the user that owns this order
     *
     * @return BelongsTo<User>
     */
    /** @phpstan-ignore-next-line */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the address for this order
     *
     * @return BelongsTo<Address>
     */
    /** @phpstan-ignore-next-line */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * Get payments for this order
     *
     * @return HasMany<Payment>
     */
    /** @phpstan-ignore-next-line */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get order items for this order
     *
     * @return HasMany<OrderItem>
     */
    /** @phpstan-ignore-next-line */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<Shipment>
     */
    /** @phpstan-ignore-next-line */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    /**
     * Get invoices/docs for this order
     *
     * @return MorphMany<\AIArmada\Docs\Models\Doc>
     */
    /** @phpstan-ignore-next-line */
    public function docs(): MorphMany
    {
        return $this->morphMany(\AIArmada\Docs\Models\Doc::class, 'docable');
    }

    /**
     * Get invoices for this order
     *
     * @return MorphMany<\AIArmada\Docs\Models\Doc>
     */
    /** @phpstan-ignore-next-line */
    public function invoices(): MorphMany
    {
        return $this->docs()->where('doc_type', 'invoice');
    }

    /**
     * Get order status histories
     *
     * @return HasMany<OrderStatusHistory>
     */
    /** @phpstan-ignore-next-line */
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
        /** @var Payment|null $payment */
        $payment = $this->payments()->latest()->first();

        return $payment;
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
        /** @var \Illuminate\Database\Eloquent\Collection<int, OrderItem> $orderItems */
        $orderItems = $this->orderItems;

        return $orderItems->some(fn ($item) => $item->requiresShipping());
    }

    /**
     * Get subtotal from order items (without shipping/tax)
     */
    public function getSubtotalAttribute(): int
    {
        return $this->orderItems->sum('total_price');
    }

    public function latestShipment(): ?Shipment
    {
        /** @var Shipment|null $shipment */
        $shipment = $this->shipments()
            ->orderByDesc('shipped_at')
            ->orderByDesc('created_at')
            ->first();

        return $shipment;
    }
}
