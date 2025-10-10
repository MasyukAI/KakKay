<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Services;

use MasyukAI\Cart\Cart as BaseCart;
use MasyukAI\FilamentCart\Jobs\SyncNormalizedCartJob;

final class CartSyncManager
{
    public function __construct(
        private NormalizedCartSynchronizer $synchronizer,
        private CartInstanceManager $cartInstances,
    ) {}

    public function sync(BaseCart $cart, bool $force = false): void
    {
        $cart = $this->cartInstances->prepare($cart);

        if (! $force && config('filament-cart.synchronization.queue_sync', false)) {
            SyncNormalizedCartJob::dispatch(
                identifier: $cart->getIdentifier(),
                instance: $cart->instance()
            );

            return;
        }

        $this->synchronizer->syncFromCart($cart);
    }

    public function syncByIdentity(string $instance, string $identifier): void
    {
        $cart = $this->cartInstances->resolve($instance, $identifier);
        $this->sync($cart, force: true);
    }
}
