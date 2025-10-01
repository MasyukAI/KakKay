<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;

/**
 * Event dispatched when a condition is added to a specific cart item
 *
 * This event is fired whenever a new condition (discount, tax, fee, etc.)
 * is added to a specific item in the cart. Useful for tracking item-level
 * promotions, analytics, and triggering related business logic.
 *
 * @since 2.0.0
 */
final class ItemConditionAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new item condition added event
     *
     * @param  CartCondition  $condition  The condition that was added to the item
     * @param  Cart  $cart  The cart instance containing the item
     * @param  string  $itemId  The ID of the item the condition was added to
     */
    public function __construct(
        public readonly CartCondition $condition,
        public readonly Cart $cart,
        public readonly string $itemId,
    ) {
        //
    }

    /**
     * Get the condition's calculated impact on the item
     *
     * @return float The monetary impact of the condition (positive for charges, negative for discounts)
     */
    public function getConditionImpact(): float
    {
        $item = $this->cart->get($this->itemId);
        $baseValue = $item?->getRawSubtotal() ?? 0;

        return $this->condition->getCalculatedValue($baseValue);
    }

    /**
     * Get event data for broadcasting or logging
     *
     * @return array Event data suitable for serialization
     */
    public function toArray(): array
    {
        $item = $this->cart->get($this->itemId);

        return [
            'condition' => [
                'name' => $this->condition->getName(),
                'type' => $this->condition->getType(),
                'value' => $this->condition->getValue(),
                'target' => $this->condition->getTarget(),
            ],
            'item' => [
                'id' => $this->itemId,
                'name' => $item?->name,
                'quantity' => $item?->quantity,
                'subtotal' => $item?->getRawSubtotal(),
            ],
            'cart' => [
                'instance' => $this->cart->instance(),
                'items_count' => $this->cart->countItems(),
                'total_quantity' => $this->cart->getTotalQuantity(),
                'subtotal' => $this->cart->getRawSubtotal(),
                'total' => $this->cart->getRawTotal(),
            ],
            'impact' => $this->getConditionImpact(),
            'timestamp' => now()->toISOString(),
        ];
    }
}
