<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Collections;

use Illuminate\Support\Collection;
use MasyukAI\Cart\Conditions\CartCondition;

class CartConditionCollection extends Collection
{
    /**
     * Add a condition to the collection
     */
    public function addCondition(CartCondition $condition): static
    {
        return $this->put($condition->getName(), $condition);
    }

    /**
     * Remove a condition from the collection
     */
    public function removeCondition(string $name): static
    {
        return $this->forget($name);
    }

    /**
     * Get condition by name
     */
    public function getCondition(string $name): ?CartCondition
    {
        return $this->get($name);
    }

    /**
     * Check if condition exists
     */
    public function hasCondition(string $name): bool
    {
        return $this->has($name);
    }

    /**
     * Filter conditions by type
     */
    public function byType(string $type): static
    {
        return $this->filter(fn (CartCondition $condition) => $condition->getType() === $type);
    }

    /**
     * Filter conditions by target
     */
    public function byTarget(string $target): static
    {
        return $this->filter(fn (CartCondition $condition) => $condition->getTarget() === $target);
    }

    /**
     * Get only discount conditions
     */
    public function discounts(): static
    {
        return $this->filter(fn (CartCondition $condition) => $condition->isDiscount());
    }

    /**
     * Get only charge/fee conditions
     */
    public function charges(): static
    {
        return $this->filter(fn (CartCondition $condition) => $condition->isCharge());
    }

    /**
     * Get only percentage-based conditions
     */
    public function percentages(): static
    {
        return $this->filter(fn (CartCondition $condition) => $condition->isPercentage());
    }

    /**
     * Sort conditions by order
     */
    public function sortByOrder(): static
    {
        return $this->sortBy(fn (CartCondition $condition) => $condition->getOrder());
    }

    /**
     * Apply all conditions to a value
     */
    public function applyAll(float $value): float
    {
        $result = $this->sortByOrder()->reduce(
            fn (float $carry, CartCondition $condition) => $condition->apply($carry),
            $value
        );

        return (float) $result;
    }

    /**
     * Get total discount amount
     */
    public function getTotalDiscount(float $baseValue): float
    {
        return $this->discounts()->sum(fn (CartCondition $condition) => abs($condition->getCalculatedValue($baseValue)));
    }

    /**
     * Get total charges amount
     */
    public function getTotalCharges(float $baseValue): float
    {
        return $this->charges()->sum(fn (CartCondition $condition) => $condition->getCalculatedValue($baseValue));
    }

    /**
     * Get conditions summary
     */
    public function getSummary(float $baseValue = 0): array
    {
        return [
            'total_conditions' => $this->count(),
            'discounts' => $this->discounts()->count(),
            'charges' => $this->charges()->count(),
            'percentages' => $this->percentages()->count(),
            'total_discount_amount' => $baseValue > 0 ? $this->getTotalDiscount($baseValue) : 0,
            'total_charges_amount' => $baseValue > 0 ? $this->getTotalCharges($baseValue) : 0,
            'net_adjustment' => $baseValue > 0 ? $this->applyAll($baseValue) - $baseValue : 0,
        ];
    }

    /**
     * Convert collection to array with detailed information
     */
    public function toDetailedArray(float $baseValue = 0): array
    {
        return [
            'conditions' => $this->map(fn (CartCondition $condition) => [
                ...$condition->toArray(),
                'calculated_value' => $baseValue > 0 ? $condition->getCalculatedValue($baseValue) : 0,
            ])->toArray(),
            'summary' => $this->getSummary($baseValue),
        ];
    }

    /**
     * Create conditions from array
     */
    public static function fromArray(array $conditions): static
    {
        $collection = new static;

        foreach ($conditions as $condition) {
            if ($condition instanceof CartCondition) {
                $collection->addCondition($condition);
            } elseif (is_array($condition)) {
                $collection->addCondition(CartCondition::fromArray($condition));
            }
        }

        return $collection;
    }

    /**
     * Group conditions by type
     */
    public function groupByType(): Collection
    {
        return $this->groupBy(fn (CartCondition $condition) => $condition->getType());
    }

    /**
     * Group conditions by target
     */
    public function groupByTarget(): Collection
    {
        return $this->groupBy(fn (CartCondition $condition) => $condition->getTarget());
    }

    /**
     * Check if collection has any discount conditions
     */
    public function hasDiscounts(): bool
    {
        return $this->contains(fn (CartCondition $condition) => $condition->isDiscount());
    }

    /**
     * Check if collection has any charge conditions
     */
    public function hasCharges(): bool
    {
        return $this->contains(fn (CartCondition $condition) => $condition->isCharge());
    }

    /**
     * Get conditions with specific attribute
     */
    public function withAttribute(string $key, mixed $value = null): static
    {
        return $this->filter(function (CartCondition $condition) use ($key, $value) {
            if ($value === null) {
                return $condition->hasAttribute($key);
            }

            return $condition->getAttribute($key) === $value;
        });
    }

    /**
     * Find condition by attribute
     */
    public function findByAttribute(string $key, mixed $value): ?CartCondition
    {
        return $this->first(fn (CartCondition $condition) => $condition->getAttribute($key) === $value);
    }

    /**
     * Remove conditions by type
     */
    public function removeByType(string $type): static
    {
        return $this->reject(fn (CartCondition $condition) => $condition->getType() === $type);
    }

    /**
     * Remove conditions by target
     */
    public function removeByTarget(string $target): static
    {
        return $this->reject(fn (CartCondition $condition) => $condition->getTarget() === $target);
    }

    /**
     * Clone collection with new conditions
     */
    public function merge($items): static
    {
        $merged = clone $this;

        if ($items instanceof static) {
            foreach ($items as $condition) {
                $merged->addCondition($condition);
            }
        } elseif (is_array($items)) {
            foreach ($items as $condition) {
                if ($condition instanceof CartCondition) {
                    $merged->addCondition($condition);
                }
            }
        }

        return $merged;
    }
}
