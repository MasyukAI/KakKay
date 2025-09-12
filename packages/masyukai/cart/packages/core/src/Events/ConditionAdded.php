<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;

/**
 * Event dispatched when a condition is added to a cart
 *
 * This event is fired whenever a new condition (discount, tax, fee, etc.)
 * is added to the cart. Useful for tracking promotional usage, analytics,
 * and triggering related business logic.
 *
 * @example
 * ```php
 * Event::listen(ConditionAdded::class, function (ConditionAdded $event) {
 *     // Track promotional code usage
 *     if ($event->condition->getType() === 'discount') {
 *         Analytics::track('promo_code_applied', [
 *             'code' => $event->condition->getName(),
 *             'value' => $event->condition->getValue(),
 *             'cart_total' => $event->cart->getRawTotal()
 *         ]);
 *     }
 *
 *     // Send notification for high-value discounts
 *     if ($event->condition->isDiscount() &&
 *         abs($event->condition->getCalculatedValue($event->cart->getRawSubtotal())) > 100) {
 *         Notification::send(User::admins(), new HighValueDiscountApplied($event));
 *     }
 * });
 * ```
 *
 * @since 2.0.0
 */
final class ConditionAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new condition added event
     *
     * @param  CartCondition  $condition  The condition that was added
     * @param  Cart  $cart  The cart instance the condition was added to
     * @param  string|null  $target  Optional target context (item ID for item conditions)
     */
    public function __construct(
        public readonly CartCondition $condition,
        public readonly Cart $cart,
        public readonly ?string $target = null
    ) {
        //
    }

    /**
     * Get the condition's calculated impact on the cart
     *
     * @return float The monetary impact of the condition (positive for charges, negative for discounts)
     */
    public function getConditionImpact(): float
    {
        $baseValue = $this->target
            ? $this->cart->get($this->target)?->getRawSubtotal() ?? 0
            : $this->cart->getRawSubtotal();

        return $this->condition->getCalculatedValue($baseValue);
    }

    /**
     * Check if this is an item-level condition
     *
     * @return bool True if applied to a specific item, false if cart-level
     */
    public function isItemCondition(): bool
    {
        return $this->target !== null;
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
                'instance' => $this->cart->instance(),
                'items_count' => $this->cart->countItems(),
                'total_quantity' => $this->cart->getTotalQuantity(),
                'subtotal' => $this->cart->getRawSubtotal(),
                'total' => $this->cart->getRawTotal(),
            ],
            'impact' => $this->getConditionImpact(),
            'target_item' => $this->target,
            'timestamp' => now()->toISOString(),
        ];
    }
}
