<?php

declare(strict_types=1);

namespace MasyukAI\Chip;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use MasyukAI\Chip\Clients\ChipCollectClient;
use MasyukAI\Chip\Clients\ChipSendClient;
use MasyukAI\Chip\Commands\ChipHealthCheckCommand;
use MasyukAI\Chip\Services\ChipCollectService;
use MasyukAI\Chip\Services\ChipSendService;
use MasyukAI\Chip\Services\WebhookService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class ChipServiceProvider extends PackageServiceProvider
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

    protected function registerServices(): void
    {
        $this->app->singleton(function ($app): ChipCollectService {
            return new ChipCollectService(
                $app->make(ChipCollectClient::class),
                $app->make(CacheRepository::class)
            );
        });

        $this->app->singleton(function ($app): ChipSendService {
            return new ChipSendService(
                $app->make(ChipSendClient::class)
            );
        });

        $this->app->singleton(function ($app): WebhookService {
            return new WebhookService;
        });

        $this->app->alias(ChipCollectService::class, 'chip.collect');
        $this->app->alias(ChipSendService::class, 'chip.send');
        $this->app->alias(WebhookService::class, 'chip.webhook');
    }

    protected function registerClients(): void
    {
        $this->app->singleton(function (): ChipCollectClient {
            $baseUrlConfig = config('chip.collect.base_url', 'https://gate.chip-in.asia/api/v1/');
            $environment = config('chip.collect.environment', 'sandbox');

            if (is_array($baseUrlConfig)) {
                $baseUrl = $baseUrlConfig[$environment] ?? reset($baseUrlConfig);
            } else {
                $baseUrl = $baseUrlConfig;
            }

            return new ChipCollectClient(
                config('chip.collect.api_key'),
                config('chip.collect.brand_id'),
                (string) $baseUrl,
                config('chip.collect.timeout', 30),
                config('chip.collect.retry', [
                    'attempts' => 3,
                    'delay' => 1000,
                ])
            );
        });

        $this->app->singleton(function (): ChipSendClient {
            $environment = config('chip.send.environment', 'sandbox');

            return new ChipSendClient(
                apiKey: config('chip.send.api_key'),
                apiSecret: config('chip.send.api_secret'),
                environment: $environment,
                baseUrl: config("chip.send.base_url.{$environment}")
                    ?? config('chip.send.base_url.sandbox', 'https://staging-api.chip-in.asia/api'),
                timeout: config('chip.send.timeout', 30),
                retryConfig: config('chip.send.retry', [
                    'attempts' => 3,
                    'delay' => 1000,
                ])
            );
        });
    }
}
