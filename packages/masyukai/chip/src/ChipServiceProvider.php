<?php

declare(strict_types=1);

namespace MasyukAI\Chip;

use Illuminate\Support\ServiceProvider;
use MasyukAI\Chip\Clients\ChipCollectClient;
use MasyukAI\Chip\Clients\ChipSendClient;
use MasyukAI\Chip\Services\ChipCollectService;
use MasyukAI\Chip\Services\ChipSendService;
use MasyukAI\Chip\Services\WebhookService;

class ChipServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/chip.php',
            'chip'
        );

        $this->registerServices();
        $this->registerClients();
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/chip.php' => config_path('chip.php'),
        ], 'chip-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'chip-migrations');

        $this->loadRoutesFrom(__DIR__.'/routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
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
