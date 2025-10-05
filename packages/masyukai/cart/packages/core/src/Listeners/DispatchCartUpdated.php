<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Listeners;

use MasyukAI\Cart\Events\CartConditionAdded;
use MasyukAI\Cart\Events\CartConditionRemoved;
use MasyukAI\Cart\Events\CartMerged;
use MasyukAI\Cart\Events\CartUpdated;
use MasyukAI\Cart\Events\ItemAdded;
use MasyukAI\Cart\Events\ItemConditionAdded;
use MasyukAI\Cart\Events\ItemConditionRemoved;
use MasyukAI\Cart\Events\ItemRemoved;
use MasyukAI\Cart\Events\ItemUpdated;
use MasyukAI\Cart\Events\MetadataAdded;
use MasyukAI\Cart\Events\MetadataRemoved;

/**
 * Dispatches CartUpdated event when cart state changes
 *
 * This listener automatically fires CartUpdated whenever any cart modification occurs.
 * It consolidates cart update logic in one place and ensures consistency.
 */
final class DispatchCartUpdated
{
    /**
     * Handle item added event
     */
    public function handleItemAdded(ItemAdded $event): void
    {
        event(new CartUpdated($event->cart));
    }

    /**
     * Handle item updated event
     */
    public function handleItemUpdated(ItemUpdated $event): void
    {
        event(new CartUpdated($event->cart));
    }

    /**
     * Handle item removed event
     */
    public function handleItemRemoved(ItemRemoved $event): void
    {
        event(new CartUpdated($event->cart));
    }

    /**
     * Handle cart-level condition added event
     */
    public function handleCartConditionAdded(CartConditionAdded $event): void
    {
        event(new CartUpdated($event->cart));
    }

    /**
     * Handle cart-level condition removed event
     */
    public function handleCartConditionRemoved(CartConditionRemoved $event): void
    {
        event(new CartUpdated($event->cart));
    }

    /**
     * Handle item-level condition added event
     */
    public function handleItemConditionAdded(ItemConditionAdded $event): void
    {
        event(new CartUpdated($event->cart));
    }

    /**
     * Handle item-level condition removed event
     */
    public function handleItemConditionRemoved(ItemConditionRemoved $event): void
    {
        event(new CartUpdated($event->cart));
    }

    /**
     * Handle metadata added event
     */
    public function handleMetadataAdded(MetadataAdded $event): void
    {
        event(new CartUpdated($event->cart));
    }

    /**
     * Handle metadata removed event
     */
    public function handleMetadataRemoved(MetadataRemoved $event): void
    {
        event(new CartUpdated($event->cart));
    }

    /**
     * Handle cart merged event
     */
    public function handleCartMerged(CartMerged $event): void
    {
        // CartMerged updates the target cart
        event(new CartUpdated($event->targetCart));
    }

    /**
     * Register the listeners for the subscriber
     *
     * @return array<string, string>
     */
    public function subscribe(\Illuminate\Events\Dispatcher $events): array
    {
        return [
            ItemAdded::class => 'handleItemAdded',
            ItemUpdated::class => 'handleItemUpdated',
            ItemRemoved::class => 'handleItemRemoved',
            CartConditionAdded::class => 'handleCartConditionAdded',
            CartConditionRemoved::class => 'handleCartConditionRemoved',
            ItemConditionAdded::class => 'handleItemConditionAdded',
            ItemConditionRemoved::class => 'handleItemConditionRemoved',
            MetadataAdded::class => 'handleMetadataAdded',
            MetadataRemoved::class => 'handleMetadataRemoved',
            CartMerged::class => 'handleCartMerged',
        ];
    }
}
