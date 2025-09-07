<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;

/**
 * Event dispatched when a condition is removed from a cart
 *
 * This event is fired whenever a condition (discount, tax, fee, etc.)
 * is removed from the cart. Useful for tracking promotional code removals,
 * analytics, and triggering related business logic.
 *
 * @example
 * ```php
 * Event::listen(ConditionRemoved::class, function (ConditionRemoved $event) {
 *     // Track promotional code removal
 *     if ($event->condition->getType() === 'discount') {
 *         Analytics::track('promo_code_removed', [
 *             'code' => $event->condition->getName(),
 *             'reason' => $event->reason ?? 'user_action',
 *             'savings_lost' => abs($event->getConditionImpact()),
 *             'cart_total' => $event->cart->getRawTotal()
 *         ]);
 *     }
 *
 *     // Log high-value condition removals
 *     if (abs($event->getConditionImpact()) > 50) {
 *         Log::info('High-value condition removed', $event->toArray());
 *     }
 * });
 * ```
 *
 * @since 2.0.0
 */
final class ConditionRemoved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new condition removed event
     *
     * @param  CartCondition  $condition  The condition that was removed
     * @param  Cart  $cart  The cart instance the condition was removed from
     * @param  string|null  $target  Optional target context (item ID for item conditions)
     * @param  string|null  $reason  Optional reason for removal ('expired', 'user_action', 'system', etc.)
     */
    public function __construct(
        public readonly CartCondition $condition,
        public readonly Cart $cart,
        public readonly ?string $target = null,
        public readonly ?string $reason = null
    ) {
        //
    }

    /**
     * Get the condition's former impact on the cart
     *
     * @return float The monetary impact the condition had (positive for charges, negative for discounts)
     */
    public function getConditionImpact(): float
    {
        $baseValue = $this->target
            ? $this->cart->get($this->target)?->getRawPriceSum() ?? 0
            : $this->cart->getRawSubtotal();

        return $this->condition->getCalculatedValue($baseValue);
    }

    /**
     * Check if this was an item-level condition
     *
     * @return bool True if applied to a specific item, false if cart-level
     */
    public function isItemCondition(): bool
    {
        return $this->target !== null;
    }

    /**
     * Get the savings that were lost (for discounts)
     *
     * @return float Positive amount representing lost savings, 0 for non-discounts
     */
    public function getLostSavings(): float
    {
        return $this->condition->isDiscount() ? abs($this->getConditionImpact()) : 0;
    }

    /**
     * Get event data for broadcasting or logging
     *
     * @return array Event data suitable for serialization
     */
    public function toArray(): array
    {
        return [
            'condition' => [
                'name' => $this->condition->getName(),
                'type' => $this->condition->getType(),
                'value' => $this->condition->getValue(),
                'target' => $this->condition->getTarget(),
            ],
            'cart' => [
                'instance' => $this->cart->getStorageInstanceName(),
                'items_count' => $this->cart->countItems(),
                'total_quantity' => $this->cart->getTotalQuantity(),
                'subtotal' => $this->cart->getRawSubtotal(),
                'total' => $this->cart->getRawTotal(),
            ],
            'impact' => $this->getConditionImpact(),
            'lost_savings' => $this->getLostSavings(),
            'target_item' => $this->target,
            'reason' => $this->reason,
            'timestamp' => now()->toISOString(),
        ];
    }
}
