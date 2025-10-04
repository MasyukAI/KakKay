<?php

declare(strict_types=1);

namespace MasyukAI\Chip;

use MasyukAI\Chip\Clients\ChipCollectClient;
use MasyukAI\Chip\Clients\ChipSendClient;
use MasyukAI\Chip\Commands\ChipHealthCheckCommand;
use MasyukAI\Chip\Services\ChipCollectService;
use MasyukAI\Chip\Services\ChipSendService;
use MasyukAI\Chip\Services\WebhookService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ChipServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('chip')
            ->hasConfigFile()
            ->hasMigrations([
                'create_chip_purchases_table',
                'create_chip_payments_table',
                'create_chip_webhooks_table',
                'create_chip_bank_accounts_table',
                'create_chip_clients_table',
                'create_chip_send_instructions_table',
            ])
            ->hasRoute('api')
            ->hasCommand(ChipHealthCheckCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->registerServices();
        $this->registerClients();
    }

    protected function registerServices(): void
    {
        $this->app->singleton(ChipCollectService::class, function ($app) {
            return new ChipCollectService(
                $app->make(ChipCollectClient::class),
                $app->make(WebhookService::class)
            );
        });

        $this->app->singleton(ChipSendService::class, function ($app) {
            return new ChipSendService(
                $app->make(ChipSendClient::class)
            );
        });

        $this->app->singleton(WebhookService::class, function ($app) {
            return new WebhookService;
        });

        $this->app->alias(ChipCollectService::class, 'chip.collect');
        $this->app->alias(ChipSendService::class, 'chip.send');
        $this->app->alias(WebhookService::class, 'chip.webhook');
    }

    protected function registerClients(): void
    {
        $this->app->singleton(ChipCollectClient::class, function () {
            return new ChipCollectClient(
                config('chip.collect.api_key'),
                config('chip.collect.brand_id'),
                config('chip.collect.timeout', 30),
                config('chip.collect.retry', [
                    'attempts' => 3,
                    'delay' => 1000,
                ])
            );
        });

        $this->app->singleton(ChipSendClient::class, function () {
            $environment = config('chip.send.environment', 'sandbox');
            $baseUrls = config('chip.send.base_url', [
                'sandbox' => 'https://staging-api.chip-in.asia/api',
                'production' => 'https://api.chip-in.asia/api',
            ]);
            $baseUrl = $baseUrls[$environment] ?? $baseUrls['sandbox'];

            return new ChipSendClient(
                apiKey: config('chip.send.api_key'),
                apiSecret: config('chip.send.api_secret'),
                environment: $environment,
                baseUrl: $baseUrl,
                timeout: config('chip.send.timeout', 30),
                retryConfig: config('chip.send.retry', [
                    'attempts' => 3,
                    'delay' => 1000,
                ])
            );
        });
    }

    /**
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            ChipCollectService::class,
            ChipSendService::class,
            WebhookService::class,
            ChipCollectClient::class,
            ChipSendClient::class,
            'chip.collect',
            'chip.send',
        ];
    }
}
