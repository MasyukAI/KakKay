<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Models;

use Akaunting\Money\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasyukAI\FilamentCart\Services\RuleConverter;

/**
 * Condition model for creating reusable condition configurations.
 *
 * This model stores conditions that can be used to quickly create cart
 * conditions with predefined settings.
 *
 * @property string $name
 * @property string|null $display_name
 * @property string|null $description
 * @property string $type
 * @property string $target
 * @property string $value
 * @property string|null $operator
 * @property bool $is_charge
 * @property bool $is_dynamic
 * @property bool $is_discount
 * @property bool $is_percentage
 * @property string|null $parsed_value
 * @property int $order
 * @property array<mixed>|null $attributes
 * @property array<mixed>|null $rules
 * @property bool $is_active
 * @property bool $is_global
 */
final class Condition extends Model
{
    /** @use HasFactory<\MasyukAI\FilamentCart\Database\Factories\ConditionFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = true;

    /**
     * The table associated with the model.
     */
    protected $table = 'conditions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'type',
        'target',
        'value',
        'operator',
        'is_charge',
        'is_dynamic',
        'is_discount',
        'is_percentage',
        'parsed_value',
        'order',
        'attributes',
        'rules',
        'is_active',
        'is_global',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'order' => 'integer',
        'attributes' => 'array',
        'rules' => 'array',
        'is_charge' => 'boolean',
        'is_dynamic' => 'boolean',
        'is_discount' => 'boolean',
        'is_percentage' => 'boolean',
        'is_active' => 'boolean',
        'is_global' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Compute derived fields from the value.
     */
    public function computeDerivedFields(): void
    {
        // Parse the value to extract operator and numeric value
        $value = (string) $this->value;

        // Determine operator
        if (str_contains($value, '%')) {
            $this->operator = '%';
            $this->is_percentage = true;
            $this->parsed_value = (string) ((float) mb_rtrim($value, '%') / 100);
        } elseif (str_starts_with($value, '+')) {
            $this->operator = '+';
            $this->is_percentage = false;
            $this->parsed_value = mb_ltrim($value, '+');
        } elseif (str_starts_with($value, '-')) {
            $this->operator = '-';
            $this->is_percentage = false;
            $this->parsed_value = $value;
        } elseif (str_starts_with($value, '*')) {
            $this->operator = '*';
            $this->is_percentage = false;
            $this->parsed_value = mb_ltrim($value, '*');
        } elseif (str_starts_with($value, '/')) {
            $this->operator = '/';
            $this->is_percentage = false;
            $this->parsed_value = mb_ltrim($value, '/');
        } else {
            // No operator, assume addition
            $this->operator = '+';
            $this->is_percentage = false;
            $this->parsed_value = $value;
        }

        // Determine if it's a discount or charge
        $numericValue = (float) $this->parsed_value;
        if ($this->operator === '%') {
            $this->is_discount = $numericValue < 0;
            $this->is_charge = $numericValue > 0;
        } else {
            $this->is_discount = $this->operator === '-';
            $this->is_charge = $this->operator === '+';
        }

        // Check if dynamic (has rules)
        $this->is_dynamic = ! empty($this->rules);
    }

    /**
     * Check if this condition is a discount type.
     */
    public function isDiscount(): bool
    {
        return $this->is_discount;
    }

    /**
     * Check if this condition is a charge/fee.
     */
    public function isCharge(): bool
    {
        return $this->is_charge;
    }

    /**
     * Check if the condition is a fee.
     */
    public function isFee(): bool
    {
        return in_array($this->type, ['fee', 'surcharge']);
    }

    /**
     * Check if the condition is a tax.
     */
    public function isTax(): bool
    {
        return $this->type === 'tax';
    }

    /**
     * Check if the condition is for shipping.
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
        return $this->is_percentage;
    }

    /**
     * Check if this is a dynamic condition.
     */
    public function isDynamic(): bool
    {
        return $this->is_dynamic;
    }

    /**
     * Check if this condition is global (auto-applied).
     */
    public function isGlobal(): bool
    {
        return $this->is_global;
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
        $normalized = mb_ltrim($rawValue, '+');
        $money = Money::{$this->resolveCurrency()}($normalized);

        return str_starts_with($rawValue, '+')
            ? '+'.$money
            : (string) $money;
    }

    /**
     * Convert condition to CartCondition data array.
     */
    /**
     * @return array<string, mixed>
     */
    public function toConditionArray(?string $customName = null): array
    {
        return [
            'name' => $customName ?? $this->display_name,
            'type' => $this->type,
            'target' => $this->target,
            'value' => $this->value,
            'order' => $this->order,
            'attributes' => array_merge($this->attributes ?? [], [
                'condition_id' => $this->id,
                'condition_name' => $this->name,
                'is_global' => $this->is_global,
            ]),
            'rules' => $this->is_dynamic ? $this->rules : null,
            'is_global' => $this->is_global,
        ];
    }

    /**
     * Create a CartCondition instance from this condition.
     */
    public function createCondition(?string $customName = null): \MasyukAI\Cart\Conditions\CartCondition
    {
        $data = $this->toConditionArray($customName);

        // Convert JSON rules to callable functions if this is a dynamic condition
        $rules = null;
        if ($this->is_dynamic && ! empty($data['rules'])) {
            $rules = RuleConverter::convertRules($data['rules']);
        }

        return new \MasyukAI\Cart\Conditions\CartCondition(
            name: $data['name'],
            type: $data['type'],
            target: $data['target'],
            value: $data['value'],
            attributes: $data['attributes'],
            order: $data['order'],
            rules: $rules
        );
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \MasyukAI\FilamentCart\Database\Factories\ConditionFactory
    {
        return \MasyukAI\FilamentCart\Database\Factories\ConditionFactory::new();
    }

    /**
     * Boot the model and set up event listeners.
     */
    protected static function booted(): void
    {
        self::saving(function (Condition $condition) {
            $condition->computeDerivedFields();
        });
    }

    /**
     * Scope to filter active conditions.
     *
     * @param  Builder<self>  $query
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope to filter by condition type.
     *
     * @param  Builder<self>  $query
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function ofType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }

    /**
     * Scope to filter by condition target.
     *
     * @param  Builder<self>  $query
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function forItems(Builder $query): void
    {
        $query->where('target', 'item');
    }

    /**
     * Scope to filter discounts.
     *
     * @param  Builder<self>  $query
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function discounts(Builder $query): void
    {
        $query->where('is_discount', true);
    }

    /**
     * Scope to filter charges/fees.
     *
     * @param  Builder<self>  $query
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function charges(Builder $query): void
    {
        $query->where('is_charge', true);
    }

    /**
     * Scope to filter dynamic conditions.
     *
     * @param  Builder<self>  $query
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function dynamic(Builder $query): void
    {
        $query->where('is_dynamic', true);
    }

    /**
     * Scope to filter global conditions.
     *
     * @param  Builder<self>  $query
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function global(Builder $query): void
    {
        $query->where('is_global', true)
            ->where('is_active', true);
    }

    /**
     * Scope to filter percentage-based conditions.
     *
     * @param  Builder<self>  $query
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function percentageBased(Builder $query): void
    {
        $query->where('is_percentage', true);
    }

    private function resolveCurrency(): string
    {
        return mb_strtoupper(config('cart.money.default_currency', 'USD'));
    }
}
