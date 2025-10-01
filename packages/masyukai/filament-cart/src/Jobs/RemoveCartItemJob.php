<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use MasyukAI\FilamentCart\Models\Cart;
use MasyukAI\FilamentCart\Models\CartItem;

/**
 * Job to remove cart item from normalized table
 */
class RemoveCartItemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $cartIdentifier,
        public string $cartInstance,
        public string $itemId,
    ) {
        $this->onQueue(config('filament-cart.synchronization.queue_name', 'cart-sync'));
        $this->onConnection(config('filament-cart.synchronization.queue_connection', 'default'));
    }

    public function handle(): void
    {
        try {
            $cart = Cart::where('identifier', $this->cartIdentifier)
                ->where('instance', $this->cartInstance)
                ->first();

            if (! $cart) {
                Log::warning('Cart not found for item removal', [
                    'identifier' => $this->cartIdentifier,
                    'instance' => $this->cartInstance,
                ]);

                return;
            }

            // Remove normalized cart item
            $deleted = CartItem::where('cart_id', $cart->id)
                ->where('item_id', $this->itemId)
                ->delete();

            Log::info('Cart item removed via job', [
                'cart_id' => $cart->id,
                'item_id' => $this->itemId,
                'deleted_count' => $deleted,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to remove cart item via job', [
                'error' => $e->getMessage(),
                'cart_identifier' => $this->cartIdentifier,
                'item_id' => $this->itemId,
            ]);

            throw $e;
        }
    }
}
