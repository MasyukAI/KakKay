<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Listeners;

use Illuminate\Support\Facades\Log;
use MasyukAI\Cart\Events\ItemConditionAdded;
use MasyukAI\FilamentCart\Jobs\SyncCartConditionJob;
use MasyukAI\FilamentCart\Models\Cart;
use MasyukAI\FilamentCart\Models\CartCondition;
use MasyukAI\FilamentCart\Models\CartItem;

/**
 * Sync normalized CartCondition when condition is added to cart or item
 */
class SyncCartConditionOnAdd
{
    public function handle($event): void
    {
        Log::info('SyncCartConditionOnAdd called', [
            'event_type' => get_class($event),
            'condition_name' => $event->condition->getName(),
        ]);

        $condition = $event->condition;
        $conditionArray = $condition->toArray();

        // Determine if this is an item-level or cart-level condition
        $isItemCondition = $event instanceof ItemConditionAdded;
        $itemId = $isItemCondition ? $event->itemId : null;

        // Extract serializable data
        $data = [
            'cartIdentifier' => $event->cart->getIdentifier(),
            'cartInstance' => $event->cart->instance(),
            'conditionName' => $condition->getName(),
            'conditionType' => $conditionArray['type'],
            'conditionTarget' => $conditionArray['target'],
            'conditionValue' => $conditionArray['value'],
            'conditionAttributes' => $conditionArray['attributes'] ?? [],
            'itemId' => $itemId,
            'operator' => $conditionArray['operator'] ?? null,
            'isCharge' => $conditionArray['is_charge'] ?? false,
            'isDynamic' => $conditionArray['is_dynamic'] ?? false,
            'isDiscount' => $conditionArray['is_discount'] ?? false,
            'isPercentage' => $conditionArray['is_percentage'] ?? false,
            'parsedValue' => $conditionArray['parsed_value'] ?? null,
            'rules' => $conditionArray['rules'] ?? [],
        ];

        // Check if we should queue the sync or run it synchronously
        if (config('filament-cart.synchronization.queue_sync', false)) {
            SyncCartConditionJob::dispatch(
                $data['cartIdentifier'],
                $data['cartInstance'],
                $data['conditionName'],
                $data['conditionType'],
                $data['conditionTarget'],
                $data['conditionValue'],
                $data['conditionAttributes'],
                $data['itemId'],
                $data['operator'],
                $data['isCharge'],
                $data['isDynamic'],
                $data['isDiscount'],
                $data['isPercentage'],
                $data['parsedValue'],
                $data['rules']
            );
        } else {
            $this->syncCartCondition($data);
        }
    }

    /**
     * Synchronously sync cart condition
     */
    private function syncCartCondition(array $data): void
    {
        try {
            // Run in a separate transaction to avoid being rolled back with cart operations
            \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
                // Find or create cart record - this ensures we can sync conditions even for new carts
                $cart = Cart::firstOrCreate(
                    [
                        'identifier' => $data['cartIdentifier'],
                        'instance' => $data['cartInstance'],
                    ],
                    [
                        'items' => [],
                        'conditions' => null,
                        'metadata' => null,
                    ]
                );

                $cartItemId = null;
                $isItemCondition = $data['itemId'] !== null;

                // If this is an item-level condition, find the cart item
                if ($isItemCondition) {
                    $cartItem = CartItem::where('cart_id', $cart->id)
                        ->where('item_id', $data['itemId'])
                        ->first();

                    if ($cartItem) {
                        $cartItemId = $cartItem->id;
                    }
                }

                // Create or update normalized cart condition
                CartCondition::updateOrCreate(
                    [
                        'cart_id' => $cart->id,
                        'name' => $data['conditionName'],
                        'cart_item_id' => $cartItemId,
                    ],
                    [
                        'type' => $data['conditionType'],
                        'target' => $data['conditionTarget'],
                        'value' => $data['conditionValue'],
                        'attributes' => empty($data['conditionAttributes']) ? null : $data['conditionAttributes'],
                        'item_id' => $data['itemId'], // Store the item_id for item-level conditions
                        'operator' => $data['operator'],
                        'is_charge' => $data['isCharge'],
                        'is_dynamic' => $data['isDynamic'],
                        'is_discount' => $data['isDiscount'],
                        'is_percentage' => $data['isPercentage'],
                        'parsed_value' => $data['parsedValue'],
                        'rules' => $data['rules'],
                    ]
                );

                Log::info('Cart condition synchronized on add', [
                    'cart_id' => $cart->id,
                    'condition_name' => $data['conditionName'],
                    'condition_type' => $data['conditionType'],
                    'is_item_condition' => $isItemCondition,
                    'target' => $data['itemId'],
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Failed to sync cart condition on add', [
                'error' => $e->getMessage(),
                'cart_identifier' => $data['cartIdentifier'],
                'condition_name' => $data['conditionName'],
            ]);
            throw $e; // Re-throw to see the error
        }
    }
}
