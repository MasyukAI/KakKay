<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Models;

use Akaunting\Money\Money;
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
 */
class CartItem extends Model
{
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
    protected $table = 'cart_items';

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
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
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
     * Scope to filter by item name.
     */
    public function scopeByName($query, string $name): void
    {
        $query->where('name', 'like', "%{$name}%");
    }

    /**
     * Scope to filter by price range.
     */
    public function scopePriceBetween($query, float $min, float $max): void
    {
        // Convert dollars to cents for comparison
        $query->whereBetween('price', [$min * 100, $max * 100]);
    }

    /**
     * Scope to filter by quantity range.
     */
    public function scopeQuantityBetween($query, int $min, int $max): void
    {
        $query->whereBetween('quantity', [$min, $max]);
    }

    /**
     * Scope to get items with conditions.
     */
    public function scopeWithConditions($query): void
    {
        $query->whereNotNull('conditions')
            ->whereRaw("conditions::text != '[]'")
            ->whereRaw("conditions::text != '{}'");
    }

    /**
     * Scope to get items without conditions.
     */
    public function scopeWithoutConditions($query): void
    {
        $query->where(function ($q) {
            $q->whereNull('conditions')
                ->orWhereRaw("conditions::text = '[]'")
                ->orWhereRaw("conditions::text = '{}'");
        });
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
        return (string) Money::MYR($this->price);
    }

    /**
     * Get formatted subtotal.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return (string) Money::MYR($this->subtotal);
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
}
