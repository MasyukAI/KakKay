<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCartPlugin\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasyukAI\Cart\Collections\CartCollection;
use MasyukAI\Cart\Models\CartItem;

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
     * Get the total weight of items in the cart.
     */
    protected function totalWeight(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!is_array($this->items)) {
                    return 0;
                }
                
                return array_sum(array_map(function ($item) {
                    $weight = $item['attributes']['weight'] ?? 0;
                    return (float) $weight * ($item['quantity'] ?? 0);
                }, $this->items));
            },
        );
    }

    /**
     * Get the total calculated with conditions applied.
     */
    protected function total(): Attribute
    {
        return Attribute::make(
            get: function () {
                $subtotal = $this->subtotal;
                
                if (!is_array($this->conditions)) {
                    return $subtotal;
                }
                
                foreach ($this->conditions as $condition) {
                    if (isset($condition['type']) && $condition['type'] === 'cart') {
                        $value = $condition['value'] ?? 0;
                        $operator = substr($value, 0, 1);
                        $amount = (float) substr($value, 1);
                        
                        switch ($operator) {
                            case '+':
                                $subtotal += $amount;
                                break;
                            case '-':
                                $subtotal -= $amount;
                                break;
                            case '*':
                                $subtotal *= $amount;
                                break;
                            case '/':
                                if ($amount > 0) $subtotal /= $amount;
                                break;
                        }
                    }
                }
                
                return max(0, $subtotal);
            },
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
     * Get formatted total.
     */
    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total, 2);
    }

    /**
     * Get unique product IDs in the cart.
     */
    public function getProductIdsAttribute(): array
    {
        if (!is_array($this->items)) {
            return [];
        }
        
        return array_unique(array_column($this->items, 'id'));
    }

    /**
     * Get condition names applied to the cart.
     */
    public function getConditionNamesAttribute(): array
    {
        if (!is_array($this->conditions)) {
            return [];
        }
        
        return array_column($this->conditions, 'name');
    }

    /**
     * Check if cart has specific product.
     */
    public function hasProduct(string $productId): bool
    {
        return in_array($productId, $this->product_ids);
    }

    /**
     * Check if cart has any of the specified products.
     */
    public function hasAnyProduct(array $productIds): bool
    {
        return !empty(array_intersect($this->product_ids, $productIds));
    }

    /**
     * Check if cart has specific condition.
     */
    public function hasCondition(string $conditionName): bool
    {
        return in_array($conditionName, $this->condition_names);
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

    /**
     * Scope to filter by item count.
     */
    public function scopeWithItemCount($query, int $count, string $operator = '='): void
    {
        $query->whereRaw("JSON_LENGTH(items) {$operator} ?", [$count]);
    }

    /**
     * Scope to filter by total quantity.
     */
    public function scopeWithTotalQuantity($query, int $quantity, string $operator = '='): void
    {
        $query->whereRaw("
            JSON_EXTRACT(items, '$[*].quantity') REGEXP CONCAT('^\\\\[.*', ?, '.*\\\\]$')
        ", [$quantity]);
    }

    /**
     * Scope to filter by subtotal range.
     */
    public function scopeWithSubtotalBetween($query, float $min, float $max): void
    {
        $query->whereRaw("
            (
                SELECT SUM(
                    CAST(JSON_EXTRACT(item.value, '$.price') AS DECIMAL(10,2)) * 
                    CAST(JSON_EXTRACT(item.value, '$.quantity') AS UNSIGNED)
                )
                FROM JSON_TABLE(items, '$[*]' COLUMNS (value JSON PATH '$')) AS item
            ) BETWEEN ? AND ?
        ", [$min, $max]);
    }

    /**
     * Scope to filter by product ID.
     */
    public function scopeWithProduct($query, string $productId): void
    {
        $query->whereRaw("JSON_SEARCH(items, 'one', ?) IS NOT NULL", [$productId]);
    }

    /**
     * Scope to filter by any of the provided product IDs.
     */
    public function scopeWithAnyProduct($query, array $productIds): void
    {
        $conditions = [];
        $bindings = [];
        
        foreach ($productIds as $productId) {
            $conditions[] = "JSON_SEARCH(items, 'one', ?) IS NOT NULL";
            $bindings[] = $productId;
        }
        
        if (!empty($conditions)) {
            $query->whereRaw('(' . implode(' OR ', $conditions) . ')', $bindings);
        }
    }

    /**
     * Scope to filter by condition name.
     */
    public function scopeWithCondition($query, string $conditionName): void
    {
        $query->whereRaw("JSON_SEARCH(conditions, 'one', ?, NULL, '$[*].name') IS NOT NULL", [$conditionName]);
    }

    /**
     * Scope to filter by condition type.
     */
    public function scopeWithConditionType($query, string $conditionType): void
    {
        $query->whereRaw("JSON_SEARCH(conditions, 'one', ?, NULL, '$[*].type') IS NOT NULL", [$conditionType]);
    }

    /**
     * Scope to filter by condition value.
     */
    public function scopeWithConditionValue($query, string $conditionValue): void
    {
        $query->whereRaw("JSON_SEARCH(conditions, 'one', ?, NULL, '$[*].value') IS NOT NULL", [$conditionValue]);
    }

    /**
     * Scope to get carts with dynamic conditions.
     */
    public function scopeWithDynamicConditions($query): void
    {
        $query->whereRaw("JSON_SEARCH(conditions, 'one', 'dynamic', NULL, '$[*].type') IS NOT NULL");
    }

    /**
     * Scope to get carts with static conditions.
     */
    public function scopeWithStaticConditions($query): void
    {
        $query->whereRaw("JSON_SEARCH(conditions, 'one', 'static', NULL, '$[*].type') IS NOT NULL");
    }

    /**
     * Get cart items as a CartCollection for advanced filtering.
     * Leverages built-in collection filtering methods from the core cart package.
     */
    public function getItemsCollection(): CartCollection
    {
        $collection = new CartCollection();
        
        if (!is_array($this->items)) {
            return $collection;
        }
        
        foreach ($this->items as $itemData) {
            $item = new CartItem(
                id: $itemData['id'] ?? '',
                name: $itemData['name'] ?? '',
                price: (float) ($itemData['price'] ?? 0),
                quantity: (int) ($itemData['quantity'] ?? 0),
                attributes: $itemData['attributes'] ?? [],
                conditions: $itemData['conditions'] ?? []
            );
            
            $collection->addItem($item);
        }
        
        return $collection;
    }

    /**
     * Filter cart items by condition type using built-in collection methods.
     * Alternative to database-level filtering for loaded cart data.
     */
    public function getItemsByConditionType(string $type): CartCollection
    {
        return $this->getItemsCollection()->filterByConditionType($type);
    }

    /**
     * Filter cart items by condition target using built-in collection methods.
     * Alternative to database-level filtering for loaded cart data.
     */
    public function getItemsByConditionTarget(string $target): CartCollection
    {
        return $this->getItemsCollection()->filterByConditionTarget($target);
    }

    /**
     * Filter cart items by condition value using built-in collection methods.
     * Alternative to database-level filtering for loaded cart data.
     */
    public function getItemsByConditionValue(string|float $value): CartCollection
    {
        return $this->getItemsCollection()->filterByConditionValue($value);
    }

    /**
     * Filter cart items by condition name using built-in collection methods.
     * Alternative to database-level filtering for loaded cart data.
     */
    public function getItemsByCondition(string $conditionName): CartCollection
    {
        return $this->getItemsCollection()->filterByCondition($conditionName);
    }
}