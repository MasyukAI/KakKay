<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Traits;

use InvalidArgumentException;
use MasyukAI\Cart\Collections\CartConditionCollection;
use MasyukAI\Cart\Conditions\CartCondition;

trait ManagesDynamicConditions
{
    /**
     * Collection of dynamic conditions that can be automatically applied/removed based on rules.
     */
    protected CartConditionCollection $dynamicConditions;

    /**
     * Register a dynamic condition that will be automatically applied/removed based on its rules.
     */
    public function registerDynamicCondition(CartCondition $condition): static
    {
        if (! $condition->isDynamic()) {
            throw new InvalidArgumentException('Only dynamic conditions (with rules) can be registered.');
        }

        $this->initializeDynamicConditions();
        $this->dynamicConditions->put($condition->getName(), $condition);
        $this->evaluateDynamicConditions();

        return $this;
    }

    /**
     * Get all registered dynamic conditions.
     */
    public function getDynamicConditions(): CartConditionCollection
    {
        $this->initializeDynamicConditions();

        return $this->dynamicConditions;
    }

    /**
     * Remove a dynamic condition.
     */
    public function removeDynamicCondition(string $name): static
    {
        $this->initializeDynamicConditions();
        $this->dynamicConditions->forget($name);

        // Also remove it from active conditions if it was applied
        $this->removeCondition($name);

        return $this;
    }

    /**
     * Evaluate all dynamic conditions and apply/remove them based on their rules.
     */
    public function evaluateDynamicConditions(): void
    {
        $this->initializeDynamicConditions();

        foreach ($this->dynamicConditions as $condition) {
            if (in_array($condition->getTarget(), ['total', 'subtotal'])) {
                // Cart-level condition (both 'total' and 'subtotal' targets)
                if ($condition->shouldApply($this)) {
                    if (! $this->getConditions()->has($condition->getName())) {
                        // Create a static version for application (without rules to avoid recursion)
                        $staticCondition = $condition->withoutRules();
                        $this->addCondition($staticCondition);
                    }
                } else {
                    $this->removeCondition($condition->getName());
                }
            } elseif ($condition->getTarget() === 'item') {
                // Item-level condition
                foreach ($this->getItems() as $item) {
                    if ($condition->shouldApply($this, $item)) {
                        if (! $item->conditions->has($condition->getName())) {
                            $staticCondition = $condition->withoutRules();
                            $this->addItemCondition($item->id, $staticCondition);
                        }
                    } else {
                        $this->removeItemCondition($item->id, $condition->getName());
                    }
                }
            }
        }
    }

    /**
     * Initialize the dynamic conditions collection if not already initialized.
     */
    protected function initializeDynamicConditions(): void
    {
        if (! isset($this->dynamicConditions)) {
            $this->dynamicConditions = new CartConditionCollection;
        }
    }
}
