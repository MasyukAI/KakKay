<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Models;

use Akaunting\Money\Money;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Normalized CartCondition model for performance and searchability
 *
 * This model is readonly and only updated via cart events
 * to maintain data consistency with the cart package.
 */
class CartCondition extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \MasyukAI\FilamentCart\Database\Factories\CartConditionFactory
    {
        return \MasyukAI\FilamentCart\Database\Factories\CartConditionFactory::new();
    }

    /**
     * The table associated with the model.
     */
    protected $table = 'cart_conditions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'cart_id',
        'cart_item_id',
        'name',
        'type',
        'target',
        'value',
        'order',
        'attributes',
        'item_id', // The cart item ID this condition applies to (if item-level)
        'operator',
        'is_charge',
        'is_dynamic',
        'is_discount',
        'is_percentage',
        'parsed_value',
        'rules',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'value' => 'string', // Keep as string to handle percentages and fixed amounts
        'parsed_value' => 'string',
        'order' => 'integer',
        'attributes' => 'array',
        'rules' => 'array',
        'is_charge' => 'boolean',
        'is_dynamic' => 'boolean',
        'is_discount' => 'boolean',
        'is_percentage' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = true;

    /**
     * Get the cart that owns this condition.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the cart item this condition applies to (if item-level).
     */
    public function cartItem(): BelongsTo
    {
        return $this->belongsTo(CartItem::class);
    }

    /**
     * Scope to filter by cart instance.
     */
    public function scopeInstance($query, string $instance): void
    {
        $query->whereHas('cart', function ($q) use ($instance) {
            $q->where('instance', $instance);
        });
    }

    /**
     * Scope to filter by cart identifier.
     */
    public function scopeByIdentifier($query, string $identifier): void
    {
        $query->whereHas('cart', function ($q) use ($identifier) {
            $q->where('identifier', $identifier);
        });
    }

    /**
     * Scope to filter by condition type.
     */
    public function scopeByType($query, string $type): void
    {
        $query->where('type', $type);
    }

    /**
     * Scope to filter by condition target.
     */
    public function scopeByTarget($query, string $target): void
    {
        $query->where('target', $target);
    }

    /**
     * Scope to get cart-level conditions.
     */
    public function scopeCartLevel($query): void
    {
        $query->whereNull('cart_item_id')->whereNull('item_id');
    }

    /**
     * Scope to get item-level conditions.
     */
    public function scopeItemLevel($query): void
    {
        $query->where(function ($q) {
            $q->whereNotNull('cart_item_id')->orWhereNotNull('item_id');
        });
    }

    /**
     * Scope to get discount conditions.
     */
    public function scopeDiscounts($query): void
    {
        $query->where('type', 'discount');
    }

    /**
     * Scope to get tax conditions.
     */
    public function scopeTaxes($query): void
    {
        $query->where('type', 'tax');
    }

    /**
     * Scope to get fee conditions.
     */
    public function scopeFees($query): void
    {
        $query->where('type', 'fee');
    }

    /**
     * Scope to get shipping conditions.
     */
    public function scopeShipping($query): void
    {
        $query->where('type', 'shipping');
    }

    /**
     * Check if this is a cart-level condition.
     */
    public function isCartLevel(): bool
    {
        return $this->cart_item_id === null && $this->item_id === null;
    }

    /**
     * Check if this is an item-level condition.
     */
    public function isItemLevel(): bool
    {
        return $this->cart_item_id !== null || $this->item_id !== null;
    }

    /**
     * Check if condition is a discount.
     */
    public function isDiscount(): bool
    {
        return $this->type === 'discount';
    }

    /**
     * Check if condition is a tax.
     */
    public function isTax(): bool
    {
        return $this->type === 'tax';
    }

    /**
     * Check if condition is a fee.
     */
    public function isFee(): bool
    {
        return $this->type === 'fee';
    }

    /**
     * Check if condition is shipping.
     */
    public function isShipping(): bool
    {
        return $this->type === 'shipping';
    }

    /**
     * Check if value is a percentage.
     */
    public function isPercentage(): bool
    {
        return str_contains($this->value, '%');
    }

    /**
     * Get the condition level label.
     */
    public function getLevelAttribute(): string
    {
        return $this->isItemLevel() ? 'Item' : 'Cart';
    }

    /**
     * Get formatted value for display.
     */
    public function getFormattedValueAttribute(): string
    {
        if ($this->isPercentage()) {
            return $this->value;
        }

        $rawValue = $this->value;
        $normalized = ltrim($rawValue, '+');
        $money = Money::MYR($normalized);

        return str_starts_with($rawValue, '+')
            ? '+'.$money
            : (string) $money;
    }

    /**
     * Get attributes count.
     */
    public function getAttributesCountAttribute(): int
    {
        return ! empty($this->attributes) && is_array($this->attributes) ? count($this->attributes) : 0;
    }
}
