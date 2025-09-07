<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCartPlugin\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \MasyukAI\FilamentCartPlugin\Database\Factories\CartFactory
    {
        return \MasyukAI\FilamentCartPlugin\Database\Factories\CartFactory::new();
    }

    /**
     * The table associated with the model.
     */
    protected $table = 'carts';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'identifier',
        'instance',
        'items',
        'conditions',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'items' => 'array',
        'conditions' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the items count for the cart.
     */
    protected function itemsCount(): Attribute
    {
        return Attribute::make(
            get: fn () => is_array($this->items) ? count($this->items) : 0,
        );
    }

    /**
     * Get the total quantity of items in the cart.
     */
    protected function totalQuantity(): Attribute
    {
        return Attribute::make(
            get: fn () => is_array($this->items) 
                ? array_sum(array_column($this->items, 'quantity')) 
                : 0,
        );
    }

    /**
     * Get the subtotal of the cart.
     */
    protected function subtotal(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!is_array($this->items)) {
                    return 0;
                }
                
                return array_sum(array_map(function ($item) {
                    return ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
                }, $this->items));
            },
        );
    }

    /**
     * Check if cart is empty.
     */
    public function isEmpty(): bool
    {
        return $this->items_count === 0;
    }

    /**
     * Get formatted subtotal.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal, 2);
    }

    /**
     * Scope to filter by instance.
     */
    public function scopeInstance($query, string $instance): void
    {
        $query->where('instance', $instance);
    }

    /**
     * Scope to filter by identifier.
     */
    public function scopeByIdentifier($query, string $identifier): void
    {
        $query->where('identifier', $identifier);
    }

    /**
     * Scope to get non-empty carts.
     */
    public function scopeNotEmpty($query): void
    {
        $query->whereNotNull('items')
              ->where('items', '!=', '[]')
              ->where('items', '!=', '{}');
    }

    /**
     * Scope to get recent carts.
     */
    public function scopeRecent($query, int $days = 7): void
    {
        $query->where('updated_at', '>=', now()->subDays($days));
    }
}