<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use MasyukAI\FilamentCart\Services\CartInstanceManager;
use MasyukAI\FilamentCart\Services\CartSyncManager;
use Throwable;

final class SyncNormalizedCartJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $identifier,
        public readonly string $instance,
    ) {
        $this->onQueue(config('filament-cart.synchronization.queue_name', 'cart-sync'));
        $this->onConnection(config('filament-cart.synchronization.queue_connection', 'default'));
    }

    public function handle(CartSyncManager $syncManager): void
    {
        try {
            $cart = app(CartInstanceManager::class)->resolve($this->instance, $this->identifier);
            $syncManager->sync($cart, force: true);
        } catch (Throwable $e) {
            Log::error('Failed to synchronize normalized cart snapshot', [
                'identifier' => $this->identifier,
                'instance' => $this->instance,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
