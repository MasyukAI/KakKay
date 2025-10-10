<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Listeners;

use MasyukAI\Cart\Events\CartCleared;
use MasyukAI\Cart\Events\CartConditionAdded;
use MasyukAI\Cart\Events\CartConditionRemoved;
use MasyukAI\Cart\Events\CartCreated;
use MasyukAI\Cart\Events\ItemAdded;
use MasyukAI\Cart\Events\ItemConditionAdded;
use MasyukAI\Cart\Events\ItemConditionRemoved;
use MasyukAI\Cart\Events\ItemRemoved;
use MasyukAI\Cart\Events\ItemUpdated;
use MasyukAI\FilamentCart\Services\CartSyncManager;

/**
 * Unified listener that syncs normalized cart snapshot whenever cart state changes.
 *
 * This listener handles all cart events that require database synchronization:
 * - Cart lifecycle: CartCreated, CartCleared
 * - Item operations: ItemAdded, ItemUpdated, ItemRemoved
 * - Cart conditions: CartConditionAdded, CartConditionRemoved
 * - Item conditions: ItemConditionAdded, ItemConditionRemoved
 */
final class SyncCartOnEvent
{
    public function __construct(private CartSyncManager $syncManager) {}

    public function handle(
        CartCreated|CartCleared|ItemAdded|ItemUpdated|ItemRemoved|CartConditionAdded|CartConditionRemoved|ItemConditionAdded|ItemConditionRemoved $event
    ): void {
        $this->syncManager->sync($event->cart);
    }
}
