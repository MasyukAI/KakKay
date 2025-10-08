<?php

declare(strict_types=1);

namespace MasyukAI\Docs;

use MasyukAI\Docs\Services\InvoiceService;
use MasyukAI\Docs\Services\ReceiptService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class DocsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('docs')
            ->hasConfigFile()
            ->hasViews()
            ->discoversMigrations()
            ->runsMigrations();
    }

    public function packageRegistered(): void
    {
        // Register Invoice Service
        $this->app->singleton(InvoiceService::class);
        $this->app->alias(InvoiceService::class, 'invoice');
        
        // Register Receipt Service (placeholder for future implementation)
        // $this->app->singleton(ReceiptService::class);
        // $this->app->alias(ReceiptService::class, 'receipt');
    }

    /**
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            InvoiceService::class,
            'invoice',
            // ReceiptService::class,
            // 'receipt',
        ];
    }
}
