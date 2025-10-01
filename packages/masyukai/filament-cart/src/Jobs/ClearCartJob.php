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

/**
 * Job to clear all cart data from normalized tables
 */
class ClearCartJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $cartIdentifier,
        public string $cartInstance,
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
                Log::warning('Cart not found for clearing', [
                    'identifier' => $this->cartIdentifier,
                    'instance' => $this->cartInstance,
                ]);

                return;
            }

            // Clear all cart items and conditions
            $itemsDeleted = $cart->items()->delete();
            $conditionsDeleted = $cart->conditions()->delete();

            Log::info('Cart cleared via job', [
                'cart_id' => $cart->id,
                'items_deleted' => $itemsDeleted,
                'conditions_deleted' => $conditionsDeleted,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear cart via job', [
                'error' => $e->getMessage(),
                'cart_identifier' => $this->cartIdentifier,
            ]);

            throw $e;
        }
    }
}
