<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Listeners;

use MasyukAI\Cart\Events\ItemAdded;
use MasyukAI\FilamentCart\Services\CartSyncManager;

/**
 * Sync normalized cart snapshot when an item is added.
 */
final class SyncCartItemOnAdd
{
    public function __construct(private CartSyncManager $syncManager) {}

    public function handle(ItemAdded $event): void
    {
        $this->syncManager->sync($event->cart);
    }
}
