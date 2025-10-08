<?php

declare(strict_types=1);

namespace MasyukAI\Invoice;

use MasyukAI\Invoice\Services\InvoiceService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class InvoiceServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('invoice')
            ->hasConfigFile()
            ->hasViews()
            ->discoversMigrations()
            ->runsMigrations();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(InvoiceService::class);
        $this->app->alias(InvoiceService::class, 'invoice');
    }

    /**
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            InvoiceService::class,
            'invoice',
        ];
    }
}
