<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Listeners;

use Illuminate\Support\Facades\Log;
use MasyukAI\Cart\Events\ItemUpdated;
use MasyukAI\FilamentCart\Jobs\SyncCartItemJob;
use MasyukAI\FilamentCart\Models\Cart;
use MasyukAI\FilamentCart\Models\CartItem;

/**
 * Sync normalized CartItem when item is updated in cart
 */
class SyncCartItemOnUpdate
{
    public function handle(ItemUpdated $event): void
    {
        $item = $event->item;

        // Extract serializable data
        $data = [
            'cartIdentifier' => $event->cart->getIdentifier(),
            'cartInstance' => $event->cart->instance(),
            'itemId' => $item->id,
            'itemName' => $item->name,
            'itemPrice' => (int) ($item->getRawPriceWithoutConditions() * 100), // Convert dollars to cents
            'itemQuantity' => $item->quantity,
            'itemAttributes' => $item->attributes->toArray(),
            'itemConditions' => $item->conditions->toArray(),
            'associatedModel' => $item->associatedModel,
        ];

        // Check if we should queue the sync or run it synchronously
        if (config('filament-cart.synchronization.queue_sync', false)) {
            SyncCartItemJob::dispatch(
                $data['cartIdentifier'],
                $data['cartInstance'],
                $data['itemId'],
                $data['itemName'],
                $data['itemPrice'],
                $data['itemQuantity'],
                $data['itemAttributes'],
                $data['itemConditions'],
                $data['associatedModel']
            );
        } else {
            $this->syncCartItem($data);
        }
    }

    /**
     * Synchronously sync cart item
     */
    private function syncCartItem(array $data): void
    {
        try {
            $cart = Cart::where('identifier', $data['cartIdentifier'])
                ->where('instance', $data['cartInstance'])
                ->first();

            if (! $cart) {
                Log::warning('Cart not found for item update sync', [
                    'identifier' => $data['cartIdentifier'],
                    'instance' => $data['cartInstance'],
                ]);

                return;
            }

            // Update normalized cart item
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('item_id', $data['itemId'])
                ->first();

            if ($cartItem) {
                $dbItem->update([
                    'name' => $item->name,
                    'price' => (int) $item->getRawPriceWithoutConditions(),
                    'quantity' => $item->quantity,
                    'attributes' => $item->attributes->toArray(),
                ]);

                Log::info('Cart item synchronized on update', [
                    'cart_id' => $cart->id,
                    'item_id' => $data['itemId'],
                    'item_name' => $data['itemName'],
                ]);
            } else {
                Log::warning('Cart item not found for update sync', [
                    'cart_id' => $cart->id,
                    'item_id' => $data['itemId'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync cart item on update', [
                'error' => $e->getMessage(),
                'cart_identifier' => $data['cartIdentifier'],
                'item_id' => $data['itemId'],
            ]);
        }
    }
}
