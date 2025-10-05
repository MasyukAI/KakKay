<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Listeners;

use MasyukAI\Cart\Events\CartCreated;
use MasyukAI\Cart\Events\CartUpdated;
use MasyukAI\FilamentCart\Services\CartSyncManager;

/**
 * Sync normalized cart snapshot when cart state is created or updated.
 */
final class SyncCompleteCart
{
    public function __construct(private CartSyncManager $syncManager) {}

    public function handle(CartCreated|CartUpdated $event): void
    {
        $this->syncManager->sync($event->cart);
    }
}
