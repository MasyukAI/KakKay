<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
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

final class FilamentCartServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/filament-cart.php',
            'filament-cart'
        );

        $this->app->singleton(NormalizedCartSynchronizer::class);
        $this->app->singleton(CartSyncManager::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/filament-cart.php' => config_path('filament-cart.php'),
        ], 'filament-cart-config');

        $this->publishes([
            __DIR__.'/../database/factories' => database_path('factories'),
        ], 'filament-cart-factories');

        $this->publishes([
            __DIR__.'/../database/seeders' => database_path('seeders'),
        ], 'filament-cart-seeders');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'filament-cart-migrations');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->registerEventListeners();
    }

    /**
     * Register event listeners for cart synchronization
     */
    protected function registerEventListeners(): void
    {
        Event::listen(CartCreated::class, [ApplyGlobalConditions::class, 'handleCartCreated']);
        Event::listen(CartUpdated::class, [ApplyGlobalConditions::class, 'handleCartUpdated']);

        // Item events
        Event::listen(ItemAdded::class, SyncCartItemOnAdd::class);
        Event::listen(ItemUpdated::class, SyncCartItemOnUpdate::class);
        Event::listen(ItemRemoved::class, SyncCartItemOnRemove::class);

        // Condition events
        Event::listen(ConditionAdded::class, SyncCartConditionOnAdd::class);
        Event::listen(ItemConditionAdded::class, SyncCartConditionOnAdd::class);
        Event::listen(ConditionRemoved::class, SyncCartConditionOnRemove::class);
        Event::listen(ItemConditionRemoved::class, SyncCartConditionOnRemove::class);

        // Cart events
        Event::listen(CartCreated::class, SyncCompleteCart::class);
        Event::listen(CartUpdated::class, SyncCompleteCart::class);
        Event::listen(CartCleared::class, SyncCartOnClear::class);
    }
}
