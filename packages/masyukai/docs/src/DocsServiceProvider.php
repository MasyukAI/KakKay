<?php

declare(strict_types=1);

namespace MasyukAI\Docs;

use MasyukAI\Docs\Services\DocumentService;
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
        // Register Document Service
        $this->app->singleton(DocumentService::class);
        $this->app->alias(DocumentService::class, 'document');

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
            DocumentService::class,
            'document',
            // ReceiptService::class,
            // 'receipt',
        ];
    }
}
