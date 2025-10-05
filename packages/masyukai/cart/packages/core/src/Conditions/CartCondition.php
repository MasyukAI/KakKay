<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Conditions;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use MasyukAI\Cart\Exceptions\InvalidCartConditionException;

final class CartCondition implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>|null  $rules
     */
    public function __construct(
        private string $name,
        private string $type,
        private string $target,
        private string|float $value,
        private array $attributes = [],
        private int $order = 0,
        private ?array $rules = null
    ) {
        $this->validateCondition();
    }

    /**
     * Create condition from array
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            name: $data['name'] ?? throw new InvalidCartConditionException('Condition name is required'),
            type: $data['type'] ?? throw new InvalidCartConditionException('Condition type is required'),
            target: $data['target'] ?? 'subtotal',
            value: $data['value'] ?? throw new InvalidCartConditionException('Condition value is required'),
            attributes: $data['attributes'] ?? [],
            order: $data['order'] ?? 0,
            rules: $data['rules'] ?? null
        );
    }

    /**
     * Get condition name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get condition type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get condition target
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * Get condition value
     */
    public function getValue(): string|float
    {
        return $this->value;
    }

    /**
     * Get condition attributes
     *
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get specific attribute
     */
    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Check if attribute exists
     */
    public function hasAttribute(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Get condition order
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * Apply condition to a value using Money arithmetic for precision
     */
    public function apply(float $value): float
    {
        $conditionValue = $this->parseValue();

        $result = match ($this->getOperator()) {
            '+' => $value + $conditionValue,
            '-' => $value - $conditionValue,
            '*' => $value * abs($conditionValue),
            '/' => abs($conditionValue) > 0 ? $value / abs($conditionValue) : $value,
            '%' => $this->applyPercentage($value, $conditionValue),
            default => $value,
        };

        return max(0, $result); // Ensure result is not negative
    }

    /**
     * Get calculated value for display
     */
    public function getCalculatedValue(float $baseValue): float
    {
        return $this->apply($baseValue) - $baseValue;
    }

    /**
     * Check if condition is a discount
     */
    public function isDiscount(): bool
    {
        $operator = $this->getOperator();
        $value = $this->parseValue();

        return ($operator === '-') || ($operator === '%' && $value < 0);
    }

    /**
     * Check if condition is a charge/fee
     */
    public function isCharge(): bool
    {
        $operator = $this->getOperator();
        $value = $this->parseValue();

        return ($operator === '+') || ($operator === '%' && $value > 0);
    }

    /**
     * Check if condition is percentage-based
     */
    public function isPercentage(): bool
    {
        return $this->getOperator() === '%';
    }

    /**
     * Create a modified copy of the condition
     *
     * @param  array<string, mixed>  $changes
     */
    public function with(array $changes): static
    {
        return new static(
            name: $changes['name'] ?? $this->name,
            type: $changes['type'] ?? $this->type,
            target: $changes['target'] ?? $this->target,
            value: $changes['value'] ?? $this->value,
            attributes: $changes['attributes'] ?? $this->attributes,
            order: $changes['order'] ?? $this->order,
            rules: $changes['rules'] ?? $this->rules
        );
    }

    /**
     * Check if this is a dynamic condition
     */
    public function isDynamic(): bool
    {
        return $this->rules !== null && ! empty($this->rules);
    }

    /**
     * Get the rules for this condition
     *
     * @return ?array<callable>
     */
    public function getRules(): ?array
    {
        return $this->rules;
    }

    /**
     * Evaluate if the condition should apply based on its rules
     */
    public function shouldApply(\MasyukAI\Cart\Cart $cart, ?\MasyukAI\Cart\Models\CartItem $item = null): bool
    {
        if (! $this->isDynamic()) {
            return true; // Static conditions always apply
        }

        if ($this->rules === null) {
            return true;
        }

        foreach ($this->rules as $rule) {
            if (! $rule($cart, $item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create a copy of this condition without rules (for static application)
     */
    public function withoutRules(): static
    {
        return new static(
            name: $this->name,
            type: $this->type,
            target: $this->target,
            value: $this->value,
            attributes: $this->attributes,
            order: $this->order,
            rules: null
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'target' => $this->target,
            'value' => $this->value,
            'attributes' => $this->attributes,
            'order' => $this->order,
            'rules' => $this->rules,
            'operator' => $this->getOperator(),
            'parsed_value' => $this->parseValue(),
            'is_discount' => $this->isDiscount(),
            'is_charge' => $this->isCharge(),
            'is_percentage' => $this->isPercentage(),
            'is_dynamic' => $this->isDynamic(),
        ];
    }

    /**
     * Convert to JSON
     *
     * @param  int  $options  JSON encode options
     */
    public function toJson($options = 0): string
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if ($json === false) {
            throw new \JsonException('Failed to encode condition to JSON');
        }

        return $json;
    }

    /**
     * JSON serialize
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        return sprintf(
            '%s (%s): %s',
            $this->name,
            $this->type,
            $this->value
        );
    }

    /**
     * Validate condition data
     */
    private function validateCondition(): void
    {
        if (empty(trim($this->name))) {
            throw new InvalidCartConditionException('Condition name cannot be empty');
        }

        if (empty(trim($this->type))) {
            throw new InvalidCartConditionException('Condition type cannot be empty');
        }

        if (empty(trim($this->target))) {
            throw new InvalidCartConditionException('Condition target cannot be empty');
        }

        if (! in_array($this->target, ['subtotal', 'total', 'item'])) {
            throw new InvalidCartConditionException('Condition target must be one of: subtotal, total, item');
        }

        if ($this->value === '') {
            throw new InvalidCartConditionException('Condition value cannot be empty');
        }

        // Validate value format
        $this->parseValue(); // This will throw exception if invalid
    }

    /**
     * Get the operator from value
     */
    private function getOperator(): string
    {
        $value = (string) $this->value;

        if (str_ends_with($value, '%')) {
            return '%';
        }

        return match ($value[0] ?? '') {
            '+' => '+',
            '-' => '-',
            '*' => '*',
            '/' => '/',
            default => '+', // Default to addition if no operator
        };
    }

    /**
     * Parse the numeric value from the condition value
     */
    private function parseValue(): float
    {

        $value = (string) $this->value;

        // Handle percentage
        if (str_ends_with($value, '%')) {
            return $this->parsePercentValue($value);
        }

        // Handle operators
        if (in_array($value[0] ?? '', ['+', '-', '*', '/'])) {
            $numericValue = (float) substr($value, 1);
        } else {
            $numericValue = (float) $value;
        }

        // Explicitly check for INF, -INF, NAN as strings
        if (
            ! is_finite($numericValue) ||
            in_array(strtoupper(trim($value)), ['INF', '-INF', 'INFINITY', '-INFINITY', 'NAN'], true)
        ) {
            throw new InvalidCartConditionException("Invalid condition value: {$this->value}");
        }

        return $numericValue;
    }

    /**
     * Parse a percentage value string (e.g., '10%')
     */
    private function parsePercentValue(string $value): float
    {
        $numericValue = (float) substr($value, 0, -1);
        if (! is_finite($numericValue)) {
            throw new InvalidCartConditionException("Invalid condition value: {$this->value}");
        }

        return $numericValue / 100;
    }

    /**
     * Apply percentage using Money arithmetic for precision
     */
    /**
     * Apply percentage calculation
     */
    private function applyPercentage(float $value, float $percentage): float
    {
        return $value + ($value * $percentage);
    }
}
