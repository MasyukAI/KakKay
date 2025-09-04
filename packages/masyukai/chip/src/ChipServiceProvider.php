<?php

declare(strict_types=1);

namespace Masyukai\Chip;

use Illuminate\Support\ServiceProvider;
use Masyukai\Chip\Clients\ChipCollectClient;
use Masyukai\Chip\Clients\ChipSendClient;
use Masyukai\Chip\Services\ChipCollectService;
use Masyukai\Chip\Services\ChipSendService;
use Masyukai\Chip\Services\WebhookService;

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
            return new WebhookService();
        });

        $this->app->alias(ChipCollectService::class, 'chip.collect');
        $this->app->alias(ChipSendService::class, 'chip.send');
        $this->app->alias(WebhookService::class, 'chip.webhook');
    }

    protected function registerClients(): void
    {
        $this->app->singleton(ChipCollectClient::class, function () {
            return new ChipCollectClient(
                apiKey: config('chip.collect.api_key'),
                brandId: config('chip.collect.brand_id'),
                environment: 'production', // Set a default since both use same URL
                baseUrl: config('chip.collect.base_url'),
                timeout: config('chip.collect.timeout', 30),
                retryConfig: config('chip.collect.retry', [
                    'attempts' => 3,
                    'delay' => 1000,
                ])
            );
        });

        $this->app->singleton(ChipSendClient::class, function () {
            $environment = config('chip.send.environment');
            $baseUrl = config("chip.send.base_url.{$environment}");
            
            return new ChipSendClient(
                apiKey: config('chip.send.api_key'),
                apiSecret: config('chip.send.api_secret'),
                environment: $environment,
                baseUrl: $baseUrl,
                timeout: config('chip.send.timeout'),
                retryConfig: config('chip.send.retry')
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
