<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCartPlugin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartCondition extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \MasyukAI\FilamentCartPlugin\Database\Factories\CartConditionFactory
    {
        return \MasyukAI\FilamentCartPlugin\Database\Factories\CartConditionFactory::new();
    }

    /**
     * The table associated with the model.
     */
    protected $table = 'cart_conditions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'type',
        'target',
        'value',
        'attributes',
        'order',
        'is_global',
        'is_active',
        'description',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'attributes' => 'array',
        'is_global' => 'boolean',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Scope to get global conditions.
     */
    public function scopeGlobal($query): void
    {
        $query->where('is_global', true);
    }

    /**
     * Scope to get active conditions.
     */
    public function scopeActive($query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope to get conditions by type.
     */
    public function scopeByType($query, string $type): void
    {
        $query->where('type', $type);
    }

    /**
     * Scope to get conditions by target.
     */
    public function scopeByTarget($query, string $target): void
    {
        $query->where('target', $target);
    }

    /**
     * Check if condition is a discount.
     */
    public function isDiscount(): bool
    {
        $value = (string) $this->value;
        $operator = $value[0] ?? '';
        
        return $operator === '-' || (str_ends_with($value, '%') && (float) substr($value, 0, -1) < 0);
    }

    /**
     * Check if condition is a charge/fee.
     */
    public function isCharge(): bool
    {
        $value = (string) $this->value;
        $operator = $value[0] ?? '';
        
        return $operator === '+' || (str_ends_with($value, '%') && (float) substr($value, 0, -1) > 0);
    }

    /**
     * Check if condition is percentage-based.
     */
    public function isPercentage(): bool
    {
        return str_ends_with((string) $this->value, '%');
    }

    /**
     * Get the formatted value for display.
     */
    public function getFormattedValueAttribute(): string
    {
        if ($this->isPercentage()) {
            return $this->value;
        }
        
        $value = (string) $this->value;
        $operator = $value[0] ?? '';
        $amount = $operator ? substr($value, 1) : $value;
        
        return $operator . '$' . number_format((float) $amount, 2);
    }

    /**
     * Get condition type badge color.
     */
    public function getTypeBadgeColor(): string
    {
        return match ($this->type) {
            'static' => 'primary',
            'dynamic' => 'success',
            default => 'gray',
        };
    }

    /**
     * Get target badge color.
     */
    public function getTargetBadgeColor(): string
    {
        return match ($this->target) {
            'item' => 'warning',
            'subtotal' => 'info',
            'total' => 'success',
            default => 'gray',
        };
    }
}