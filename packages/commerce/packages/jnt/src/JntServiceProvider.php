<?php

declare(strict_types=1);

namespace AIArmada\Jnt;

use AIArmada\Jnt\Console\Commands\ConfigCheckCommand;
use AIArmada\Jnt\Console\Commands\OrderCancelCommand;
use AIArmada\Jnt\Console\Commands\OrderCreateCommand;
use AIArmada\Jnt\Console\Commands\OrderPrintCommand;
use AIArmada\Jnt\Console\Commands\OrderTrackCommand;
use AIArmada\Jnt\Console\Commands\WebhookTestCommand;
use AIArmada\Jnt\Http\Middleware\VerifyWebhookSignature;
use AIArmada\Jnt\Services\JntExpressService;
use AIArmada\Jnt\Services\WebhookService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * J&T Express Service Provider
 *
 * Bootstraps the J&T Express package for Laravel integration using Spatie's package tools.
 * Handles service registration, configuration publishing, command registration,
 * webhook setup, and configuration validation.
 */
class JntServiceProvider extends PackageServiceProvider
{
    /**
     * Configure the package.
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('jnt')
            ->hasConfigFile()
            ->hasRoute('webhooks')
            ->hasCommands([
                ConfigCheckCommand::class,
                OrderCreateCommand::class,
                OrderTrackCommand::class,
                OrderCancelCommand::class,
                OrderPrintCommand::class,
                WebhookTestCommand::class,
            ]);
    }

    /**
     * Register package services.
     */
    public function registeringPackage(): void
    {
        $this->registerServices();
    }

    /**
     * Bootstrap package services.
     */
    public function bootingPackage(): void
    {
        $this->validateConfiguration();
        $this->registerMiddleware();
    }

    /**
     * Register package services in the container.
     */
    protected function registerServices(): void
    {
        // Register main J&T Express service
        $this->app->singleton(JntExpressService::class, function (Application $app): JntExpressService {
            $config = $app['config']['jnt'];

            return new JntExpressService(
                customerCode: $config['customer_code'],
                password: $config['password'],
                config: $config,
            );
        });

        // Register facade accessor alias
        $this->app->alias(JntExpressService::class, 'jnt-express');

        // Register webhook service
        $this->app->singleton(WebhookService::class, fn (Application $app): WebhookService => new WebhookService(
            privateKey: $app['config']['jnt']['private_key']
        ));
    }

    /**
     * Register package middleware.
     */
    protected function registerMiddleware(): void
    {
        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('jnt.verify.signature', VerifyWebhookSignature::class);
    }

    /**
     * Validate critical configuration values.
     *
     * This method performs non-blocking validation and logs warnings
     * for invalid configuration rather than throwing exceptions.
     * Use the jnt:config:check command for detailed validation.
     */
    protected function validateConfiguration(): void
    {
        $this->validateRequiredKeys();
        $this->validateEnvironment();
        $this->validateBaseUrl();
    }

    /**
     * Validate required configuration keys.
     */
    protected function validateRequiredKeys(): void
    {
        $required = ['customer_code', 'password', 'private_key'];

        foreach ($required as $key) {
            $value = config('jnt.'.$key);

            if (empty($value)) {
                Log::warning('J&T Express: Missing required configuration key: jnt.'.$key);
            }
        }
    }

    /**
     * Validate environment configuration.
     */
    protected function validateEnvironment(): void
    {
        $environment = config('jnt.environment', 'production');
        $validEnvironments = ['sandbox', 'production'];

        if (! in_array($environment, $validEnvironments, true)) {
            Log::warning(
                sprintf("J&T Express: Invalid environment: %s. Must be 'sandbox' or 'production'.", $environment)
            );
        }
    }

    /**
     * Validate base URL format.
     */
    protected function validateBaseUrl(): void
    {
        $baseUrl = config('jnt.base_url');

        if (empty($baseUrl)) {
            return;
        }

        if (! filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            Log::warning('J&T Express: Invalid base URL format: '.$baseUrl);
        }
    }
}
