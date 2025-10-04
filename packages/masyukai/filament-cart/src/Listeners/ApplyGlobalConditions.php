<?php

namespace MasyukAI\FilamentCart\Listeners;

use MasyukAI\Cart\Events\CartCreated;
use MasyukAI\FilamentCart\Models\Condition;
use MasyukAI\FilamentCart\Services\RuleConverter;

class ApplyGlobalConditions
{
    public function __construct(
        protected RuleConverter $ruleConverter
    ) {}

    /**
     * Handle cart created event.
     */
    public function handleCartCreated(CartCreated $event): void
    {
        if (! config('filament-cart.enable_global_conditions', true)) {
            return;
        }
        $this->applyGlobalConditions($event->cart);
    }

    /**
     * Handle cart updated event.
     */
    public function handleCartUpdated(\MasyukAI\Cart\Events\CartUpdated $event): void
    {
        if (! config('filament-cart.enable_global_conditions', true)) {
            return;
        }
        $this->applyGlobalConditions($event->cart);
    }

    /**
     * Apply all global conditions to the cart.
     */
    protected function applyGlobalConditions($cart): void
    {
        $globalConditions = Condition::global()->get();

        foreach ($globalConditions as $condition) {
            // Check if condition already exists in cart
            $existingConditions = method_exists($cart, 'getConditions') ? $cart->getConditions() : [];
            if (is_object($existingConditions) && method_exists($existingConditions, 'has')) {
                if ($existingConditions->has($condition->name)) {
                    continue;
                }
            } elseif (is_array($existingConditions) && isset($existingConditions[$condition->name])) {
                continue;
            }

            // Build condition data
            $conditionData = [
                'name' => $condition->name,
                'type' => $condition->type,
                'target' => $condition->target,
                'value' => $condition->value,
                'order' => $condition->order,
                'attributes' => [
                    'display_name' => $condition->display_name,
                    'description' => $condition->description,
                ],
            ];

            // Add rules if dynamic condition
            if ($condition->isDynamic()) {
                $conditionData['attributes']['rules'] = $condition->rules;

                // Evaluate rules before applying
                $rules = $this->ruleConverter::convertRules($condition->rules);
                foreach ($rules as $rule) {
                    if (! $rule($cart)) {
                        continue 2; // Skip this condition if any rule fails
                    }
                }
            }

            // Instantiate CartCondition and apply to cart
            $cartConditionClass = \MasyukAI\Cart\Conditions\CartCondition::class;
            $cartCondition = $cartConditionClass::fromArray($conditionData);
            $cart->addCondition($cartCondition);
        }
    }
}
