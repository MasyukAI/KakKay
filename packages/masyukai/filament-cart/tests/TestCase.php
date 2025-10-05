<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Register Str::ucwords macro if missing
        \Illuminate\Support\Str::macro('ucwords', function ($value) {
            return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
        });

        // Register the 'grid' macro on ComponentAttributeBag if missing
        if (! \Illuminate\View\ComponentAttributeBag::hasMacro('grid')) {
            \Illuminate\View\ComponentAttributeBag::macro('grid', function (...$args) {
                // No-op for test, just return $this
                return $this;
            });
        }
        // Register the 'gridColumn' macro on ComponentAttributeBag if missing
        if (! \Illuminate\View\ComponentAttributeBag::hasMacro('gridColumn')) {
            \Illuminate\View\ComponentAttributeBag::macro('gridColumn', function (...$args) {
                // No-op for test, just return $this;
                return $this;
            });
        }

        $this->registerEventListeners();
    }

    /**
     * Register event listeners for cart synchronization
     */
    protected function registerEventListeners(): void
    {
        // Global conditions should always register to mimic service provider
        \Illuminate\Support\Facades\Event::listen(
            \MasyukAI\Cart\Events\CartCreated::class,
            [\MasyukAI\FilamentCart\Listeners\ApplyGlobalConditions::class, 'handleCartCreated']
        );
        \Illuminate\Support\Facades\Event::listen(
            \MasyukAI\Cart\Events\CartUpdated::class,
            [\MasyukAI\FilamentCart\Listeners\ApplyGlobalConditions::class, 'handleCartUpdated']
        );

        // Item events
        \Illuminate\Support\Facades\Event::listen(
            \MasyukAI\Cart\Events\ItemAdded::class,
            \MasyukAI\FilamentCart\Listeners\SyncCartItemOnAdd::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \MasyukAI\Cart\Events\ItemUpdated::class,
            \MasyukAI\FilamentCart\Listeners\SyncCartItemOnUpdate::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \MasyukAI\Cart\Events\ItemRemoved::class,
            \MasyukAI\FilamentCart\Listeners\SyncCartItemOnRemove::class
        );

        // Condition events
        \Illuminate\Support\Facades\Event::listen(
            \MasyukAI\Cart\Events\CartConditionAdded::class,
            \MasyukAI\FilamentCart\Listeners\SyncCartConditionOnAdd::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \MasyukAI\Cart\Events\ItemConditionAdded::class,
            \MasyukAI\FilamentCart\Listeners\SyncCartConditionOnAdd::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \MasyukAI\Cart\Events\CartConditionRemoved::class,
            \MasyukAI\FilamentCart\Listeners\SyncCartConditionOnRemove::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \MasyukAI\Cart\Events\ItemConditionRemoved::class,
            \MasyukAI\FilamentCart\Listeners\SyncCartConditionOnRemove::class
        );

        // Cart events
        \Illuminate\Support\Facades\Event::listen(
            \MasyukAI\Cart\Events\CartCreated::class,
            \MasyukAI\FilamentCart\Listeners\SyncCompleteCart::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \MasyukAI\Cart\Events\CartUpdated::class,
            \MasyukAI\FilamentCart\Listeners\SyncCompleteCart::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \MasyukAI\Cart\Events\CartCleared::class,
            \MasyukAI\FilamentCart\Listeners\SyncCartOnClear::class
        );
    }

    protected function defineEnvironment($app): void
    {
        // Setup the test environment (mirroring cart package)
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('app.env', 'testing');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('cart.money.default_currency', 'USD');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('session.driver', 'array');
        $app['config']->set('session.lifetime', 120);
        $app['config']->set('session.expire_on_close', false);
        $app['config']->set('session.encrypt', false);
        $app['config']->set('session.files', storage_path('framework/sessions'));
        $app['config']->set('session.connection', null);
        $app['config']->set('session.table', 'sessions');
        $app['config']->set('session.store', null);
        $app['config']->set('session.lottery', [2, 100]);
        $app['config']->set('session.cookie', 'laravel_session');
        $app['config']->set('session.path', '/');
        $app['config']->set('session.domain', null);
        $app['config']->set('session.secure', false);
        $app['config']->set('session.http_only', true);
        $app['config']->set('session.same_site', 'lax');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('cache.stores.array', [
            'driver' => 'array',
            'serialize' => false,
        ]);
        $app['config']->set('cart.storage', 'database');
        $app['config']->set('cart.database.connection', 'testing');
        $app['config']->set('cart.database.table', 'carts');
        $app['config']->set('cart.events', true);

        // Set filament-cart config
        $app['config']->set('filament-cart.synchronization.queue_sync', false);

        // Register Filament Blade components namespaces for testing
        $componentsPath = base_path('vendor/filament/filament/resources/views/components');
        $app['view']->addNamespace('filament-components', $componentsPath);
        $app['view']->addNamespace('components', $componentsPath);

        // Set up Filament default panel for tests before app is booted
        \Filament\Facades\Filament::registerPanel(
            (new \Filament\Panel)
                ->default()
                ->id('admin')
                ->path('')
                ->brandName('Test Panel')
        );

        // Register Filament view namespaces for testing
        $app['view']->addNamespace('filament-schemas', base_path('vendor/filament/filament/resources/views/schemas'));
        $app['view']->addNamespace('filament-tables', base_path('vendor/filament/filament/resources/views/tables'));
        $app['view']->addNamespace('filament', base_path('vendor/filament/filament/resources/views'));
    }

    protected function getPackageProviders($app)
    {
        return [
            // Livewire core
            \Livewire\LivewireServiceProvider::class,
            // Filament core
            \Filament\FilamentServiceProvider::class,
            // Your FilamentCart package
            \MasyukAI\FilamentCart\FilamentCartServiceProvider::class,
            // MasyukAI Cart package (if it has a service provider)
            \MasyukAI\Cart\CartServiceProvider::class,
        ];
    }
}
