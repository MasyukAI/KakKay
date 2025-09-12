<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MasyukAI\Cart\Cart;

/**
 * Event fired when a cart is updated.
 *
 * This event is dispatched whenever the cart state changes, such as when items
 * are modified, conditions are applied/removed, or other cart operations occur.
 *
 * @example
 * ```php
 * CartUpdated::dispatch($cart, 'item_quantity_changed');
 *
 * // Listen for cart updates
 * Event::listen(CartUpdated::class, function (CartUpdated $event) {
 *     logger('Cart updated', [
 *         'identifier' => $event->cart->getIdentifier(),
 *         'reason' => $event->reason,
 *         'total' => $event->cart->getTotal(),
 *     ]);
 * });
 * ```
 */
final class CartUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new cart updated event instance.
     *
     * @param  Cart  $cart  The cart instance that was updated
     * @param  string|null  $reason  Optional reason for the update (e.g., 'item_added', 'condition_applied')
     */
    public function __construct(
        public readonly Cart $cart,
        public readonly ?string $reason = null,
    ) {
        //
    }

    /**
     * Get the event data as an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'identifier' => $this->cart->getIdentifier(),
            'instance_name' => $this->cart->instance(),
            'reason' => $this->reason,
            'items_count' => $this->cart->countItems(),
            'total_quantity' => $this->cart->getTotalQuantity(),
            'subtotal' => $this->cart->getRawSubtotal(),
            'total' => $this->cart->getRawTotal(),
            'conditions_count' => $this->cart->getConditions()->count(),
            'timestamp' => now()->toISOString(),
        ];
    }
}
