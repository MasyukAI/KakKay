<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Listeners;

use MasyukAI\Cart\Events\CartCleared;
use MasyukAI\FilamentCart\Services\CartSyncManager;

/**
 * Sync normalized cart snapshot when a cart is cleared.
 */
final class SyncCartOnClear
{
    public function __construct(private CartSyncManager $syncManager) {}

    public function handle(CartCleared $event): void
    {
        $this->syncManager->sync($event->cart);
    }
}
