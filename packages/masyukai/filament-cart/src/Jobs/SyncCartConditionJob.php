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
use MasyukAI\FilamentCart\Models\CartItem;

/**
 * Job to sync cart condition data to normalized table
 */
class SyncCartConditionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $cartIdentifier,
        public string $cartInstance,
        public string $conditionName,
        public string $conditionType,
        public ?string $conditionTarget,
        public float $conditionValue,
        public array $conditionAttributes = [],
        public ?string $itemId = null,
        public ?string $operator = null,
        public bool $isCharge = false,
        public bool $isDynamic = false,
        public bool $isDiscount = false,
        public bool $isPercentage = false,
        public ?string $parsedValue = null,
        public ?array $rules = null,
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
                Log::warning('Cart not found for condition sync', [
                    'identifier' => $this->cartIdentifier,
                    'instance' => $this->cartInstance,
                ]);

                return;
            }

            // Find cart item if this is an item-level condition
            $cartItem = null;
            if ($this->itemId) {
                $cartItem = CartItem::where('cart_id', $cart->id)
                    ->where('item_id', $this->itemId)
                    ->first();

                if (! $cartItem) {
                    Log::warning('Cart item not found for condition sync', [
                        'cart_id' => $cart->id,
                        'item_id' => $this->itemId,
                    ]);

                    return;
                }
            }

            // Create or update normalized cart condition
            CartCondition::updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'cart_item_id' => $cartItem?->id,
                    'name' => $this->conditionName,
                ],
                [
                    'type' => $this->conditionType,
                    'target' => $this->conditionTarget,
                    'value' => $this->conditionValue,
                    'attributes' => empty($this->conditionAttributes) ? null : $this->conditionAttributes,
                    'instance' => $this->cartInstance,
                    'identifier' => $this->cartIdentifier,
                    'operator' => $this->operator,
                    'is_charge' => $this->isCharge,
                    'is_dynamic' => $this->isDynamic,
                    'is_discount' => $this->isDiscount,
                    'is_percentage' => $this->isPercentage,
                    'parsed_value' => $this->parsedValue,
                    'rules' => $this->rules,
                ]
            );

            Log::info('Cart condition synchronized via job', [
                'cart_id' => $cart->id,
                'cart_item_id' => $cartItem?->id,
                'condition_name' => $this->conditionName,
                'condition_type' => $this->conditionType,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to sync cart condition via job', [
                'error' => $e->getMessage(),
                'cart_identifier' => $this->cartIdentifier,
                'condition_name' => $this->conditionName,
            ]);

            throw $e;
        }
    }
}
