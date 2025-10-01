<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Listeners;

use Illuminate\Support\Facades\Log;
use MasyukAI\Cart\Events\ItemConditionRemoved;
use MasyukAI\FilamentCart\Jobs\RemoveCartConditionJob;
use MasyukAI\FilamentCart\Models\Cart;
use MasyukAI\FilamentCart\Models\CartCondition;
use MasyukAI\FilamentCart\Models\CartItem;

/**
 * Remove normalized CartCondition when condition is removed from cart or item
 */
class SyncCartConditionOnRemove
{
    public function handle($event): void
    {
        $condition = $event->condition;
        $isItemCondition = $event instanceof ItemConditionRemoved;
        $itemId = $isItemCondition ? $event->itemId : null;

        // Extract serializable data
        $data = [
            'cartIdentifier' => $event->cart->getIdentifier(),
            'cartInstance' => $event->cart->instance(),
            'conditionName' => $condition->getName(),
            'itemId' => $itemId,
        ];

        // Check if we should queue the sync or run it synchronously
        if (config('filament-cart.synchronization.queue_sync', false)) {
            RemoveCartConditionJob::dispatch(
                $data['cartIdentifier'],
                $data['cartInstance'],
                $data['conditionName'],
                $data['itemId']
            );
        } else {
            $this->removeCartCondition($data);
        }
    }

    /**
     * Synchronously remove cart condition
     */
    private function removeCartCondition(array $data): void
    {
        try {
            $cart = Cart::where('identifier', $data['cartIdentifier'])
                ->where('instance', $data['cartInstance'])
                ->first();

            if (! $cart) {
                Log::warning('Cart not found for condition removal sync', [
                    'identifier' => $data['cartIdentifier'],
                    'instance' => $data['cartInstance'],
                ]);

                return;
            }

            $isItemCondition = $data['itemId'] !== null;

            // Build the query to find the condition
            $query = CartCondition::where('cart_id', $cart->id)
                ->where('name', $data['conditionName']);

            // If this is an item-level condition, add item constraints
            if ($isItemCondition) {
                $cartItem = CartItem::where('cart_id', $cart->id)
                    ->where('item_id', $data['itemId'])
                    ->first();

                if ($cartItem) {
                    $query->where('cart_item_id', $cartItem->id);
                }
            } else {
                // Cart-level condition
                $query->whereNull('cart_item_id');
            }

            $deleted = $query->delete();

            if ($deleted) {
                Log::info('Cart condition synchronized on remove', [
                    'cart_id' => $cart->id,
                    'condition_name' => $data['conditionName'],
                    'is_item_condition' => $isItemCondition,
                    'target' => $data['itemId'],
                    'deleted_count' => $deleted,
                ]);

                // Check if cart is now empty and clean up if so
                $this->cleanupEmptyCart($cart);
            } else {
                Log::warning('Cart condition not found for removal sync', [
                    'cart_id' => $cart->id,
                    'condition_name' => $data['conditionName'],
                    'is_item_condition' => $isItemCondition,
                    'target' => $data['itemId'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync cart condition on remove', [
                'error' => $e->getMessage(),
                'cart_identifier' => $data['cartIdentifier'],
                'condition_name' => $data['conditionName'],
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
            $hasConditions = CartCondition::where('cart_id', $cart->id)->exists();

            // If no items and no conditions, clean up the cart
            if (! $hasItems && ! $hasConditions) {
                $cart->delete();

                Log::info('Empty cart cleaned up after condition removal', [
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
