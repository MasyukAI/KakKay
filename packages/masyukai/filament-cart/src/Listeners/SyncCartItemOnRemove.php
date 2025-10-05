<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Listeners;

use MasyukAI\Cart\Events\ItemRemoved;
use MasyukAI\FilamentCart\Services\CartSyncManager;

/**
 * Sync normalized cart snapshot when an item is removed.
 */
final class SyncCartItemOnRemove
{
    public function __construct(private CartSyncManager $syncManager) {}

    public function handle(ItemRemoved $event): void
    {
        $this->syncManager->sync($event->cart);
    }
}
