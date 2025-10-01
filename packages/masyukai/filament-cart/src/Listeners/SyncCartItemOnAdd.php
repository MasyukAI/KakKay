<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Listeners;

use Illuminate\Support\Facades\Log;
use MasyukAI\Cart\Events\ItemAdded;
use MasyukAI\FilamentCart\Jobs\SyncCartItemJob;
use MasyukAI\FilamentCart\Models\Cart;
use MasyukAI\FilamentCart\Models\CartItem;

/**
 * Sync normalized CartItem when item is added to cart
 */
class SyncCartItemOnAdd
{
    public function handle(ItemAdded $event): void
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
            // Find or create cart record - this ensures we can sync items even for new carts
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

            // Create or update normalized cart item
            CartItem::updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'item_id' => $item->id,
                ],
                [
                    'name' => $item->name,
                    'price' => (int) $item->getRawPriceWithoutConditions(),
                    'quantity' => $item->quantity,
                    'attributes' => $item->attributes->toArray(),
                ]
            );
            Log::info('Cart item synchronized on add', [
                'cart_id' => $cart->id,
                'item_id' => $data['itemId'],
                'item_name' => $data['itemName'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to sync cart item on add', [
                'error' => $e->getMessage(),
                'cart_identifier' => $data['cartIdentifier'],
                'item_id' => $data['itemId'],
            ]);
        }
    }
}
