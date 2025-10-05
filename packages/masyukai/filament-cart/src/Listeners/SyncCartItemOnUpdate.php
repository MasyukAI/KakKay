<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Listeners;

use MasyukAI\Cart\Events\ItemUpdated;
use MasyukAI\FilamentCart\Services\CartSyncManager;

/**
 * Sync normalized cart snapshot when an item is updated.
 */
final class SyncCartItemOnUpdate
{
    public function __construct(private CartSyncManager $syncManager) {}

    public function handle(ItemUpdated $event): void
    {
        $this->syncManager->sync($event->cart);
    }
}
