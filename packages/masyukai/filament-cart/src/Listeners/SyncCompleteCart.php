<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Listeners;

use Illuminate\Support\Facades\Log;
use MasyukAI\Cart\Events\CartCreated;
use MasyukAI\Cart\Events\CartUpdated;
use MasyukAI\FilamentCart\Models\Cart;
use MasyukAI\FilamentCart\Models\CartCondition;
use MasyukAI\FilamentCart\Models\CartItem;

/**
 * Comprehensive cart synchronization for cart created/updated events
 */
class SyncCompleteCart
{
    public function handle(CartCreated|CartUpdated $event): void
    {
        try {
            $cartInstance = $event->cart;

            // If cart is empty, check if we need to clean up existing database record
            if ($cartInstance->getItems()->isEmpty() && $cartInstance->getConditions()->isEmpty()) {
                // Find and delete existing cart record if it exists
                $existingCart = Cart::where('identifier', $cartInstance->getIdentifier())
                    ->where('instance', $cartInstance->instance())
                    ->first();

                if ($existingCart) {
                    // Clean up related records
                    CartItem::where('cart_id', $existingCart->id)->delete();
                    CartCondition::where('cart_id', $existingCart->id)->delete();
                    $existingCart->delete();

                    Log::info('Empty cart cleaned up from database', [
                        'cart_id' => $existingCart->id,
                        'identifier' => $cartInstance->getIdentifier(),
                        'instance' => $cartInstance->instance(),
                        'event_type' => get_class($event),
                    ]);
                } else {
                    Log::debug('Skipping sync for empty cart (no database record exists)', [
                        'identifier' => $cartInstance->getIdentifier(),
                        'instance' => $cartInstance->instance(),
                        'event_type' => get_class($event),
                    ]);
                }

                return;
            }

            // Find or create the cart record
            $cart = Cart::updateOrCreate(
                [
                    'identifier' => $cartInstance->getIdentifier(),
                    'instance' => $cartInstance->instance(),
                ],
                [
                    'items' => $cartInstance->getItems()->toArray(),
                    'conditions' => $cartInstance->getConditions()->isEmpty() ? null : $cartInstance->getConditions()->toArray(),
                    'metadata' => null,
                ]
            );

            // Sync all items
            $this->syncCartItems($cart, $cartInstance);

            // Sync all conditions
            $this->syncCartConditions($cart, $cartInstance);

            Log::info('Complete cart synchronized', [
                'cart_id' => $cart->id,
                'event_type' => get_class($event),
                'items_count' => $cartInstance->countItems(),
                'conditions_count' => $cartInstance->getConditions()->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to sync complete cart', [
                'error' => $e->getMessage(),
                'event_type' => get_class($event),
                'cart_identifier' => $event->cart->getIdentifier(),
            ]);
        }
    }

    private function syncCartItems(Cart $cart, $cartInstance): void
    {
        $existingItemIds = [];

        foreach ($cartInstance->getItems() as $item) {
            $existingItemIds[] = $item->id;

            CartItem::updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'item_id' => $item->id,
                ],
                [
                    'name' => $item->name,
                    'price' => (int) $item->getRawPriceWithoutConditions(),
                    'quantity' => $item->quantity,
                    'attributes' => $item->attributes->isEmpty() ? null : $item->attributes->toArray(),
                    'conditions' => $item->conditions->isEmpty() ? null : $item->conditions->toArray(),
                    'associated_model' => $item->associatedModel,
                ]
            );
        }

        // Remove items that are no longer in the cart
        CartItem::where('cart_id', $cart->id)
            ->whereNotIn('item_id', $existingItemIds)
            ->delete();
    }

    private function syncCartConditions(Cart $cart, $cartInstance): void
    {
        $existingConditionKeys = [];

        // Sync cart-level conditions
        foreach ($cartInstance->getConditions() as $condition) {
            $key = $condition->getName().'_cart_level';
            $existingConditionKeys[] = $key;

            $conditionData = $condition->toArray();

            CartCondition::updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'name' => $condition->getName(),
                    'cart_item_id' => null,
                    'item_id' => null,
                ],
                [
                    'type' => $conditionData['type'],
                    'target' => $conditionData['target'],
                    'value' => $conditionData['value'],
                    'order' => $conditionData['order'],
                    'attributes' => empty($conditionData['attributes']) ? null : $conditionData['attributes'],
                    'operator' => $conditionData['operator'],
                    'is_charge' => $conditionData['is_charge'],
                    'is_dynamic' => $conditionData['is_dynamic'],
                    'is_discount' => $conditionData['is_discount'],
                    'is_percentage' => $conditionData['is_percentage'],
                    'parsed_value' => $conditionData['parsed_value'],
                    'rules' => $conditionData['rules'],
                ]
            );
        }

        // Sync item-level conditions
        foreach ($cartInstance->getItems() as $item) {
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('item_id', $item->id)
                ->first();

            if ($cartItem) {
                foreach ($item->conditions as $condition) {
                    $key = $condition->getName().'_item_'.$item->id;
                    $existingConditionKeys[] = $key;

                    $conditionData = $condition->toArray();

                    CartCondition::updateOrCreate(
                        [
                            'cart_id' => $cart->id,
                            'name' => $condition->getName(),
                            'cart_item_id' => $cartItem->id,
                            'item_id' => $item->id,
                        ],
                        [
                            'type' => $conditionData['type'],
                            'target' => $conditionData['target'],
                            'value' => $conditionData['value'],
                            'order' => $conditionData['order'],
                            'attributes' => empty($conditionData['attributes']) ? null : $conditionData['attributes'],
                            'operator' => $conditionData['operator'],
                            'is_charge' => $conditionData['is_charge'],
                            'is_dynamic' => $conditionData['is_dynamic'],
                            'is_discount' => $conditionData['is_discount'],
                            'is_percentage' => $conditionData['is_percentage'],
                            'parsed_value' => $conditionData['parsed_value'],
                            'rules' => $conditionData['rules'],
                        ]
                    );
                }
            }
        }

        // Remove conditions that are no longer in the cart
        // This is more complex since we need to check both cart and item conditions
        $cartConditionNames = $cartInstance->getConditions()->pluck('name')->toArray();

        // Remove cart-level conditions not in current cart
        CartCondition::where('cart_id', $cart->id)
            ->whereNull('cart_item_id')
            ->whereNull('item_id')
            ->whereNotIn('name', $cartConditionNames)
            ->delete();

        // Remove item-level conditions for items that no longer exist or conditions that were removed
        $currentItemIds = $cartInstance->getItems()->pluck('id')->toArray();
        CartCondition::where('cart_id', $cart->id)
            ->where(function ($query) {
                $query->whereNotNull('cart_item_id')
                    ->orWhereNotNull('item_id');
            })
            ->whereNotIn('item_id', $currentItemIds)
            ->delete();
    }
}
