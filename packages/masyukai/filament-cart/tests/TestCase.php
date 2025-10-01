<?php

namespace MasyukAI\FilamentCart\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase();

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

        // Manually register event listeners for synchronization
        $this->registerEventListeners();
    }

    /**
     * Register event listeners for cart synchronization
     */
    protected function registerEventListeners(): void
    {
        // Only register listeners if normalized models are enabled
        if (! config('filament-cart.enable_normalized_models', true)) {
            return;
        }

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

    protected function setUpDatabase(): void
    {
        // Enable foreign key constraints in SQLite
        DB::statement('PRAGMA foreign_keys=ON');

        // Drop tables if they exist (order matters for FKs)
        Schema::dropIfExists('cart_conditions');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');

        // Create carts table
        Schema::create('carts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('identifier')->index();
            $table->string('instance')->default('default')->index();
            $table->jsonb('items')->nullable();
            $table->jsonb('conditions')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->bigInteger('version')->default(1)->index();
            $table->timestamps();
            $table->unique(['identifier', 'instance']);
        });

        // Create cart_items table
        Schema::create('cart_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cart_id')->constrained('carts')->onDelete('cascade');
            $table->string('item_id')->index();
            $table->string('name');
            $table->integer('price'); // Price in cents (from Money object)
            $table->integer('quantity');
            $table->jsonb('attributes')->nullable();
            $table->jsonb('conditions')->nullable();
            $table->string('associated_model')->nullable();
            $table->timestamps();
            $table->index(['cart_id', 'item_id']);
            $table->index(['name']);
            $table->index(['price']);
            $table->index(['quantity']);
            $table->index(['created_at']);
            $table->index(['updated_at']);
        });

        // Create cart_conditions table
        Schema::create('cart_conditions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cart_id')->constrained('carts')->onDelete('cascade');
            $table->foreignUuid('cart_item_id')->nullable()->constrained('cart_items')->onDelete('cascade');
            $table->string('name');
            $table->string('type'); // discount, tax, fee, shipping, etc.
            $table->string('target'); // subtotal, total, price, etc.
            $table->string('value'); // percentage or fixed amount
            $table->string('operator')->nullable(); // +, -, *, /, %
            $table->boolean('is_charge')->default(false);
            $table->boolean('is_dynamic')->default(false);
            $table->boolean('is_discount')->default(false);
            $table->boolean('is_percentage')->default(false);
            $table->string('parsed_value')->nullable(); // Calculated value
            $table->jsonb('rules')->nullable(); // Additional rules
            $table->integer('order')->default(0);
            $table->jsonb('attributes')->nullable();
            $table->string('item_id')->nullable()->index(); // Cart item ID this applies to (if item-level)
            $table->timestamps();

            // Indexes for performance
            $table->index(['cart_id', 'name']);
            $table->index(['type']);
            $table->index(['target']);
            $table->index(['order']);
            $table->index(['is_discount']);
            $table->index(['is_percentage']);
            $table->index(['created_at']);
            $table->index(['updated_at']);
        });
    }

    protected function defineEnvironment($app): void
    {
        // Setup the test environment (mirroring cart package)
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('app.env', 'testing');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('cart.default_currency', 'USD');
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
        $app['config']->set('cart.strict_validation', true);

        // Set filament-cart config
        $app['config']->set('filament-cart.enable_normalized_models', true);
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
