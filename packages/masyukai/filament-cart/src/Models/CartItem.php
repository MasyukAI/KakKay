<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Models;

use Akaunting\Money\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Cart Item Model
 *
 * Normalized representation of cart items for efficient querying.
 * This model is readonly and only updated via cart events
 * to maintain data consistency with the cart package.
 *
 * @property int $cart_id
 * @property string $item_id
 * @property string $name
 * @property int $price
 * @property int $quantity
 * @property array<mixed>|null $attributes
 * @property array<mixed>|null $conditions
 * @property string|null $associated_model
 */
class CartItem extends Model
{
    /** @use HasFactory<\MasyukAI\FilamentCart\Database\Factories\CartItemFactory> */
    use HasFactory;
    use HasUuids;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \MasyukAI\FilamentCart\Database\Factories\CartItemFactory
    {
        return \MasyukAI\FilamentCart\Database\Factories\CartItemFactory::new();
    }

    /**
     * The table associated with the model.
     */
    protected $table = 'cart_snapshot_items';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'cart_id',
        'item_id',
        'name',
        'price',
        'quantity',
        'attributes',
        'conditions',
        'associated_model',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'price' => 'integer',
        'quantity' => 'integer',
        'attributes' => 'array',
        'conditions' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = true;

    /**
     * Get the computed subtotal (price × quantity).
     */
    public function getSubtotalAttribute(): int
    {
        return $this->price * $this->quantity;
    }

    /**
     * Get the cart that owns this item.
     * @return BelongsTo<Cart, CartItem>
     */
    /** @phpstan-ignore return.type, missingType.generics */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Scope to filter by cart instance.
     * @param Builder<self> $query
     */
    public function scopeInstance(Builder $query, string $instance): void
    {
        $query->whereHas('cart', function ($q) use ($instance) {
            $q->where('instance', $instance);
        });
    }

    /**
     * Scope to filter by cart identifier.
     * @param Builder<self> $query
     */
    public function scopeByIdentifier(Builder $query, string $identifier): void
    {
        $query->whereHas('cart', function ($q) use ($identifier) {
            $q->where('identifier', $identifier);
        });
    }

    /**
     * Scope to filter by item name.
     * @param Builder<self> $query
     */
    public function scopeByName(Builder $query, string $name): void
    {
        $query->where('name', 'like', "%{$name}%");
    }

    /**
     * Scope to filter by price range.
     * @param Builder<self> $query
     */
    public function scopePriceBetween(Builder $query, float $min, float $max): void
    {
        // Convert dollars to cents for comparison
        $query->whereBetween('price', [$min * 100, $max * 100]);
    }

    /**
     * Scope to filter by quantity range.
     * @param Builder<self> $query
     */
    public function scopeQuantityBetween(Builder $query, int $min, int $max): void
    {
        $query->whereBetween('quantity', [$min, $max]);
    }

    /**
     * Scope to get items with conditions.
     * @param Builder<self> $query
     */
    public function scopeWithConditions(Builder $query): void
    {
        $query->whereNotNull('conditions')
            ->whereJsonLength('conditions', '>', 0);
    }

    /**
     * Scope to get items without conditions.
     * @param Builder<self> $query
     */
    public function scopeWithoutConditions(Builder $query): void
    {
        $query->whereNull('conditions')
            ->orWhereJsonLength('conditions', '=', 0);
    }

    /**
     * Get price in dollars (converted from cents).
     */
    public function getPriceInDollarsAttribute(): float
    {
        return $this->price / 100;
    }

    /**
     * Get subtotal in dollars (converted from cents).
     */
    public function getSubtotalInDollarsAttribute(): float
    {
        return $this->subtotal / 100;
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->formatMoney($this->price);
    }

    /**
     * Get formatted subtotal.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return $this->formatMoney($this->subtotal);
    }

    /**
     * Check if item has conditions.
     */
    public function hasConditions(): bool
    {
        return ! empty($this->conditions) && is_array($this->conditions) && count($this->conditions) > 0;
    }

    /**
     * Get conditions count.
     */
    public function getConditionsCountAttribute(): int
    {
        return $this->hasConditions() ? count($this->conditions) : 0;
    }

    /**
     * Get attributes count.
     */
    public function getAttributesCountAttribute(): int
    {
        return ! empty($this->attributes) && is_array($this->attributes) ? count($this->attributes) : 0;
    }

    private function formatMoney(int $amount): string
    {
        $currency = strtoupper($this->cart->currency ?? config('cart.money.default_currency', 'USD'));

        return (string) Money::{$currency}($amount);
    }
}
