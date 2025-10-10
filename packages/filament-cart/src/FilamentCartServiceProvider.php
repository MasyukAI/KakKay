<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart;

use MasyukAI\Cart\Contracts\RulesFactoryInterface;
use MasyukAI\Cart\Events\CartCleared;
use MasyukAI\Cart\Events\CartConditionAdded as ConditionAdded;
use MasyukAI\Cart\Events\CartConditionRemoved as ConditionRemoved;
use MasyukAI\Cart\Events\CartCreated;
use MasyukAI\Cart\Events\CartMerged;
use MasyukAI\Cart\Events\ItemAdded;
use MasyukAI\Cart\Events\ItemConditionAdded;
use MasyukAI\Cart\Events\ItemConditionRemoved;
use MasyukAI\Cart\Events\ItemRemoved;
use MasyukAI\Cart\Events\ItemUpdated;
use MasyukAI\Cart\Services\BuiltInRulesFactory;
use MasyukAI\FilamentCart\Listeners\ApplyGlobalConditions;
use MasyukAI\FilamentCart\Listeners\CleanupSnapshotOnCartMerged;
use MasyukAI\FilamentCart\Listeners\SyncCartOnEvent;
use MasyukAI\FilamentCart\Services\CartConditionBatchRemoval;
use MasyukAI\FilamentCart\Services\CartConditionValidator;
use MasyukAI\FilamentCart\Services\CartInstanceManager;
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
        if (! $this->app->bound(RulesFactoryInterface::class)) {
            $this->app->singleton(function ($app): RulesFactoryInterface {
                $factoryClass = (string) config(
                    'filament-cart.dynamic_rules_factory',
                    BuiltInRulesFactory::class
                );

                return $app->make($factoryClass);
            });
        }

        $this->app->singleton(CartInstanceManager::class);
        $this->app->singleton(NormalizedCartSynchronizer::class);
        $this->app->singleton(CartSyncManager::class);
        $this->app->singleton(CartConditionValidator::class);
        $this->app->singleton(CartConditionBatchRemoval::class);
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
        // Apply global conditions on cart creation and item changes
        // Note: We listen to specific events (ItemAdded, ItemUpdated, ItemRemoved) instead of CartUpdated
        // to avoid infinite loops when applying conditions triggers CartConditionAdded → CartUpdated
        $this->app['events']->listen(CartCreated::class, [ApplyGlobalConditions::class, 'handleCartCreated']);
        $this->app['events']->listen(ItemAdded::class, [ApplyGlobalConditions::class, 'handleItemChanged']);
        $this->app['events']->listen(ItemUpdated::class, [ApplyGlobalConditions::class, 'handleItemChanged']);
        $this->app['events']->listen(ItemRemoved::class, [ApplyGlobalConditions::class, 'handleItemChanged']);

        // Unified sync listener for all cart state changes
        // Handles: CartCreated, CartCleared, ItemAdded, ItemUpdated, ItemRemoved,
        //          CartConditionAdded, CartConditionRemoved, ItemConditionAdded, ItemConditionRemoved
        $this->app['events']->listen(
            [
                CartCreated::class,
                CartCleared::class,
                ItemAdded::class,
                ItemUpdated::class,
                ItemRemoved::class,
                ConditionAdded::class,
                ConditionRemoved::class,
                ItemConditionAdded::class,
                ItemConditionRemoved::class,
            ],
            SyncCartOnEvent::class
        );

        // Cart merge cleanup
        $this->app['events']->listen(CartMerged::class, CleanupSnapshotOnCartMerged::class);
    }
}
