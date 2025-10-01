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
 * Job to sync cart item data to normalized table
 */
class SyncCartItemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $cartIdentifier,
        public string $cartInstance,
        public string $itemId,
        public string $itemName,
        public float $itemPrice,
        public int $itemQuantity,
        public array $itemAttributes = [],
        public array $itemConditions = [],
        public ?string $associatedModel = null,
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
                Log::warning('Cart not found for item sync', [
                    'identifier' => $this->cartIdentifier,
                    'instance' => $this->cartInstance,
                ]);

                return;
            }

            // Create or update normalized cart item
            CartItem::updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'item_id' => $this->itemId,
                ],
                [
                    'name' => $this->itemName,
                    'price' => $this->itemPrice,
                    'quantity' => $this->itemQuantity,
                    'attributes' => empty($this->itemAttributes) ? null : $this->itemAttributes,
                    'conditions' => empty($this->itemConditions) ? null : $this->itemConditions,
                    'associated_model' => $this->associatedModel,
                    'instance' => $this->cartInstance,
                    'identifier' => $this->cartIdentifier,
                ]
            );

            Log::info('Cart item synchronized via job', [
                'cart_id' => $cart->id,
                'item_id' => $this->itemId,
                'item_name' => $this->itemName,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to sync cart item via job', [
                'error' => $e->getMessage(),
                'cart_identifier' => $this->cartIdentifier,
                'item_id' => $this->itemId,
            ]);

            throw $e;
        }
    }
}
