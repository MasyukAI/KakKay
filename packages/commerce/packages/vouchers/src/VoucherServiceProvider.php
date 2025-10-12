<?php

declare(strict_types=1);

namespace AIArmada\Vouchers;

use AIArmada\Vouchers\Services\VoucherService;
use AIArmada\Vouchers\Services\VoucherValidator;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class VoucherServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('vouchers')
            ->hasConfigFile()
            ->discoversMigrations()
            ->runsMigrations();
    }

    public function packageRegistered(): void
    {
        // Register services as singletons
        $this->app->singleton(VoucherService::class);
        $this->app->singleton(VoucherValidator::class);

        // Bind facade accessor
        $this->app->alias(VoucherService::class, 'voucher');
    }

    /**
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            VoucherService::class,
            VoucherValidator::class,
            'voucher',
        ];
    }
}
