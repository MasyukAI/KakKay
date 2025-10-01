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
use MasyukAI\FilamentCart\Models\CartCondition;

/**
 * Job to remove cart condition from normalized table
 */
class RemoveCartConditionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $cartIdentifier,
        public string $cartInstance,
        public string $conditionName,
        public ?string $itemId = null,
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
                Log::warning('Cart not found for condition removal', [
                    'identifier' => $this->cartIdentifier,
                    'instance' => $this->cartInstance,
                ]);

                return;
            }

            $query = CartCondition::where('cart_id', $cart->id)
                ->where('name', $this->conditionName);

            // If item ID is provided, this is an item-level condition
            if ($this->itemId) {
                $cartItem = $cart->items()->where('item_id', $this->itemId)->first();
                if ($cartItem) {
                    $query->where('cart_item_id', $cartItem->id);
                }
            } else {
                // Cart-level condition
                $query->whereNull('cart_item_id');
            }

            $deleted = $query->delete();

            Log::info('Cart condition removed via job', [
                'cart_id' => $cart->id,
                'item_id' => $this->itemId,
                'condition_name' => $this->conditionName,
                'deleted_count' => $deleted,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to remove cart condition via job', [
                'error' => $e->getMessage(),
                'cart_identifier' => $this->cartIdentifier,
                'condition_name' => $this->conditionName,
            ]);

            throw $e;
        }
    }
}
