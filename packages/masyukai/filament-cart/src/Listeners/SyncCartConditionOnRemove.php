<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Listeners;

use MasyukAI\Cart\Events\CartConditionRemoved;
use MasyukAI\Cart\Events\ItemConditionRemoved;
use MasyukAI\FilamentCart\Services\CartSyncManager;

/**
 * Sync normalized cart snapshot when a condition is removed.
 */
final class SyncCartConditionOnRemove
{
    public function __construct(private CartSyncManager $syncManager) {}

    public function handle(CartConditionRemoved|ItemConditionRemoved $event): void
    {
        $this->syncManager->sync($event->cart);
    }
}
