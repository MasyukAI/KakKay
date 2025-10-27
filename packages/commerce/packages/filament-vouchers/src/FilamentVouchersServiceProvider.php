<?php

declare(strict_types=1);

namespace AIArmada\FilamentVouchers;

use AIArmada\FilamentVouchers\Services\VoucherStatsAggregator;
use AIArmada\FilamentVouchers\Support\Integrations\FilamentCartBridge;
use AIArmada\FilamentVouchers\Support\OwnerTypeRegistry;
use Filament\Facades\Filament;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class FilamentVouchersServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-vouchers';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile('filament-vouchers');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(VoucherStatsAggregator::class);
        $this->app->singleton(OwnerTypeRegistry::class);
        $this->app->singleton(FilamentCartBridge::class);
    }

    public function packageBooted(): void
    {
        Filament::registerRenderHook('panels::body.start', static function (): void {
            // Registering the plugin implicitly ensures it is discoverable via Filament's panel registry.
        });

        Filament::serving(static function (): void {
            // The bridge lazily inspects whether Filament Cart is present. Resolving the singleton ensures
            // any integration hooks are prepared before Filament renders resources.
            app(FilamentCartBridge::class)->warm();
        });
    }
}
