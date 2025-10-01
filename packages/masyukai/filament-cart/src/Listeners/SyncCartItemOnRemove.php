<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Listeners;

use Illuminate\Support\Facades\Log;
use MasyukAI\Cart\Events\ItemRemoved;
use MasyukAI\FilamentCart\Jobs\RemoveCartItemJob;
use MasyukAI\FilamentCart\Models\Cart;
use MasyukAI\FilamentCart\Models\CartItem;

/**
 * Remove normalized CartItem when item is removed from cart
 */
class SyncCartItemOnRemove
{
    public function handle(ItemRemoved $event): void
    {
        $item = $event->item;

        // Extract serializable data
        $data = [
            'cartIdentifier' => $event->cart->getIdentifier(),
            'cartInstance' => $event->cart->instance(),
            'itemId' => $item->id,
        ];

        // Check if we should queue the sync or run it synchronously
        if (config('filament-cart.synchronization.queue_sync', false)) {
            RemoveCartItemJob::dispatch(
                $data['cartIdentifier'],
                $data['cartInstance'],
                $data['itemId']
            );
        } else {
            $this->removeCartItem($data);
        }
    }

    /**
     * Synchronously remove cart item
     */
    private function removeCartItem(array $data): void
    {
        try {
            $cart = Cart::where('identifier', $data['cartIdentifier'])
                ->where('instance', $data['cartInstance'])
                ->first();

            if (! $cart) {
                Log::warning('Cart not found for item removal sync', [
                    'identifier' => $data['cartIdentifier'],
                    'instance' => $data['cartInstance'],
                ]);

                return;
            }

            // Find the cart item first to get its database ID
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('item_id', $data['itemId'])
                ->first();

            if ($cartItem) {
                // Delete item-level conditions first (explicit cascade since SQLite can be unreliable)
                \MasyukAI\FilamentCart\Models\CartCondition::where('cart_item_id', $cartItem->id)->delete();

                // Then delete the cart item
                $cartItem->delete();

                Log::info('Cart item synchronized on remove', [
                    'cart_id' => $cart->id,
                    'item_id' => $data['itemId'],
                ]);

                // Check if cart is now empty and clean up if so
                $this->cleanupEmptyCart($cart);
            } else {
                Log::warning('Cart item not found for removal sync', [
                    'cart_id' => $cart->id,
                    'item_id' => $data['itemId'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync cart item on remove', [
                'error' => $e->getMessage(),
                'cart_identifier' => $data['cartIdentifier'],
                'item_id' => $data['itemId'],
            ]);
        }
    }

    /**
     * Clean up cart if it's empty (no items and no conditions)
     */
    private function cleanupEmptyCart(Cart $cart): void
    {
        try {
            // Check if cart has any remaining items
            $hasItems = CartItem::where('cart_id', $cart->id)->exists();

            // Check if cart has any conditions
            $hasConditions = \MasyukAI\FilamentCart\Models\CartCondition::where('cart_id', $cart->id)->exists();

            // If no items and no conditions, clean up the cart
            if (! $hasItems && ! $hasConditions) {
                $cart->delete();

                Log::info('Empty cart cleaned up after item removal', [
                    'cart_id' => $cart->id,
                    'identifier' => $cart->identifier,
                    'instance' => $cart->instance,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to cleanup empty cart', [
                'error' => $e->getMessage(),
                'cart_id' => $cart->id,
            ]);
        }
    }
}
