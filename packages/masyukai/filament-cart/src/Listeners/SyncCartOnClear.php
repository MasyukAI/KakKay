<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Listeners;

use Illuminate\Support\Facades\Log;
use MasyukAI\Cart\Events\CartCleared;
use MasyukAI\FilamentCart\Jobs\ClearCartJob;
use MasyukAI\FilamentCart\Models\Cart;

/**
 * Clear normalized cart data when cart is cleared
 */
class SyncCartOnClear
{
    public function handle(CartCleared $event): void
    {
        Log::info('SyncCartOnClear called', [
            'cart_identifier' => $event->cart->getIdentifier(),
        ]);

        // Extract serializable data
        $data = [
            'cartIdentifier' => $event->cart->getIdentifier(),
            'cartInstance' => $event->cart->instance(),
        ];

        // Check if we should queue the sync or run it synchronously
        if (config('filament-cart.synchronization.queue_sync', false)) {
            ClearCartJob::dispatch(
                $data['cartIdentifier'],
                $data['cartInstance']
            );
        } else {
            $this->clearCart($data);
        }
    }

    /**
     * Synchronously clear cart
     */
    private function clearCart(array $data): void
    {
        try {
            $cart = Cart::where('identifier', $data['cartIdentifier'])
                ->where('instance', $data['cartInstance'])
                ->first();

            if (! $cart) {
                Log::warning('Cart not found for clearing', [
                    'identifier' => $data['cartIdentifier'],
                    'instance' => $data['cartInstance'],
                ]);

                return;
            }

            // Clear all cart items and conditions
            $itemsDeleted = $cart->cartItems()->delete();
            $conditionsDeleted = $cart->cartConditions()->delete();

            Log::info('Cart cleared', [
                'cart_id' => $cart->id,
                'items_deleted' => $itemsDeleted,
                'conditions_deleted' => $conditionsDeleted,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear cart', [
                'error' => $e->getMessage(),
                'cart_identifier' => $data['cartIdentifier'],
            ]);
        }
    }
}
