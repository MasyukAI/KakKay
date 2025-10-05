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
    protected function applyGlobalConditions(\MasyukAI\Cart\Cart $cart): void
    {
        $globalConditions = Condition::global()->get();
        $hasDynamicConditions = false;

        foreach ($globalConditions as $condition) {
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
                    'is_global' => true,
                ],
            ];

            // Instantiate CartCondition
            $cartConditionClass = \MasyukAI\Cart\Conditions\CartCondition::class;

            // Handle dynamic vs static conditions differently
            if ($condition->isDynamic()) {
                $hasDynamicConditions = true;

                // Convert rules to callables
                $rules = $this->ruleConverter::convertRules($condition->rules);
                $conditionData['rules'] = $rules;

                // Create dynamic condition with rules
                $cartCondition = $cartConditionClass::fromArray($conditionData);

                // Check if already registered as dynamic condition
                if (! $cart->getDynamicConditions()->has($condition->name)) {
                    // Register as dynamic condition for automatic evaluation
                    // Note: registerDynamicCondition() automatically calls evaluateDynamicConditions()
                    $cart->registerDynamicCondition($cartCondition);
                }
            } else {
                // Static condition - add only if not already present
                if (! $cart->getConditions()->has($condition->name)) {
                    $cartCondition = $cartConditionClass::fromArray($conditionData);
                    $cart->addCondition($cartCondition);
                }
            }
        }

        // Re-evaluate all dynamic conditions after registration to ensure they're applied/removed correctly
        if ($hasDynamicConditions && method_exists($cart, 'evaluateDynamicConditions')) {
            $cart->evaluateDynamicConditions();
        }
    }
}
