<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart;

use MasyukAI\Cart\Events\CartCleared;
use MasyukAI\Cart\Events\CartConditionAdded as ConditionAdded;
use MasyukAI\Cart\Events\CartConditionRemoved as ConditionRemoved;
use MasyukAI\Cart\Events\CartCreated;
use MasyukAI\Cart\Events\CartUpdated;
use MasyukAI\Cart\Events\ItemAdded;
use MasyukAI\Cart\Events\ItemConditionAdded;
use MasyukAI\Cart\Events\ItemConditionRemoved;
use MasyukAI\Cart\Events\ItemRemoved;
use MasyukAI\Cart\Events\ItemUpdated;
use MasyukAI\FilamentCart\Listeners\ApplyGlobalConditions;
use MasyukAI\FilamentCart\Listeners\SyncCartConditionOnAdd;
use MasyukAI\FilamentCart\Listeners\SyncCartConditionOnRemove;
use MasyukAI\FilamentCart\Listeners\SyncCartItemOnAdd;
use MasyukAI\FilamentCart\Listeners\SyncCartItemOnRemove;
use MasyukAI\FilamentCart\Listeners\SyncCartItemOnUpdate;
use MasyukAI\FilamentCart\Listeners\SyncCartOnClear;
use MasyukAI\FilamentCart\Listeners\SyncCompleteCart;
use MasyukAI\FilamentCart\Services\CartSyncManager;
use MasyukAI\FilamentCart\Services\NormalizedCartSynchronizer;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class FilamentCartServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-cart')
            ->hasConfigFile('filament-cart')
            ->discoversMigrations()
            ->runsMigrations();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(NormalizedCartSynchronizer::class);
        $this->app->singleton(CartSyncManager::class);
    }

    public function packageBooted(): void
    {
        $this->registerEventListeners();
    }

    /**
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            NormalizedCartSynchronizer::class,
            CartSyncManager::class,
        ];
    }

    /**
     * Register event listeners for cart synchronization
     */
    protected function registerEventListeners(): void
    {
        // Apply global conditions
        $this->app['events']->listen(CartCreated::class, [ApplyGlobalConditions::class, 'handleCartCreated']);
        $this->app['events']->listen(CartUpdated::class, [ApplyGlobalConditions::class, 'handleCartUpdated']);

        // Item events
        $this->app['events']->listen(ItemAdded::class, SyncCartItemOnAdd::class);
        $this->app['events']->listen(ItemUpdated::class, SyncCartItemOnUpdate::class);
        $this->app['events']->listen(ItemRemoved::class, SyncCartItemOnRemove::class);

        // Condition events
        $this->app['events']->listen(ConditionAdded::class, SyncCartConditionOnAdd::class);
        $this->app['events']->listen(ItemConditionAdded::class, SyncCartConditionOnAdd::class);
        $this->app['events']->listen(ConditionRemoved::class, SyncCartConditionOnRemove::class);
        $this->app['events']->listen(ItemConditionRemoved::class, SyncCartConditionOnRemove::class);

        // Cart events
        $this->app['events']->listen(CartCreated::class, SyncCompleteCart::class);
        $this->app['events']->listen(CartUpdated::class, SyncCompleteCart::class);
        $this->app['events']->listen(CartCleared::class, SyncCartOnClear::class);
    }
}
