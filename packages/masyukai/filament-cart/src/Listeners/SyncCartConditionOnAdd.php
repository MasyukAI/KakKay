<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Listeners;

use MasyukAI\Cart\Events\CartConditionAdded;
use MasyukAI\Cart\Events\ItemConditionAdded;
use MasyukAI\FilamentCart\Services\CartSyncManager;

/**
 * Sync normalized cart snapshot when a condition is added.
 */
final class SyncCartConditionOnAdd
{
    public function __construct(private CartSyncManager $syncManager) {}

    public function handle(CartConditionAdded|ItemConditionAdded $event): void
    {
        $this->syncManager->sync($event->cart);
    }
}
