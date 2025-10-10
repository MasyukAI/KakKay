<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MasyukAI\Cart\CartServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /**
     * Track the application instance that already registered event listeners.
     */
    private static ?int $listenerRegistrationId = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->registerEventListeners();
    }

    protected function tearDown(): void
    {
        // Clear all cart instances to prevent memory leaks between tests
        \MasyukAI\Cart\Facades\Cart::clear();

        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [
            \Illuminate\Events\EventServiceProvider::class,
            \Illuminate\Session\SessionServiceProvider::class,
            \Illuminate\View\ViewServiceProvider::class,
            \Illuminate\Hashing\HashServiceProvider::class,
            \Illuminate\Cache\CacheServiceProvider::class,
            \Illuminate\Database\DatabaseServiceProvider::class,
            CartServiceProvider::class,
            \MasyukAI\FilamentCart\FilamentCartServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Cart' => \MasyukAI\Cart\Facades\Cart::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // Setup the test environment
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('app.env', 'testing');
        $app['config']->set('database.default', 'testing');

        // Set USD currency for consistent test formatting
        $app['config']->set('cart.default_currency', 'USD');

        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Configure session
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

        // Configure cache
        $app['config']->set('cache.default', 'array');
        $app['config']->set('cache.stores.array', [
            'driver' => 'array',
            'serialize' => false,
        ]);

        // Configure cart settings for testing
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

    protected function setUpDatabase(): void
    {
        // Drop tables if they exist
        Schema::dropIfExists('cart_snapshot_conditions');
        Schema::dropIfExists('cart_snapshot_items');
        Schema::dropIfExists('cart_snapshots');
        Schema::dropIfExists('conditions');
        Schema::dropIfExists('carts');

        // Create the carts table (from cart package)
        Schema::create('carts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('identifier')->index();
            $table->string('instance')->default('default')->index();
            $table->json('items')->nullable();
            $table->json('conditions')->nullable();
            $table->json('metadata')->nullable();
            $table->bigInteger('version')->default(1)->index();
            $table->timestamps();

            $table->unique(['identifier', 'instance']);
        });

        // Create the conditions table
        Schema::create('conditions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('type');
            $table->string('target');
            $table->string('value');
            $table->string('operator')->nullable();
            $table->boolean('is_charge')->default(false);
            $table->boolean('is_dynamic')->default(false);
            $table->boolean('is_discount')->default(false);
            $table->boolean('is_percentage')->default(false);
            $table->string('parsed_value')->nullable();
            $table->integer('order')->default(0);
            $table->jsonb('attributes')->nullable();
            $table->jsonb('rules')->nullable();
            $table->boolean('is_global')->default(false);
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->index(['target', 'is_active']);
        });

        // Create the cart_snapshots table
        Schema::create('cart_snapshots', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('identifier');
            $table->string('instance')->default('default');
            $table->unsignedInteger('items_count')->default(0);
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->unsignedBigInteger('savings')->default(0);
            $table->string('currency', 3)->default('USD');
            $table->jsonb('items')->nullable();
            $table->jsonb('conditions')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->unique(['identifier', 'instance']);
            $table->index('identifier');
            $table->index('instance');
            $table->index('items_count');
            $table->index('quantity');
            $table->index('subtotal');
            $table->index('total');
            $table->index('savings');
            $table->index('created_at');
            $table->index('updated_at');
        });

        // Create the cart_snapshot_items table
        Schema::create('cart_snapshot_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cart_id')->constrained('cart_snapshots')->onDelete('cascade');
            $table->string('item_id')->index();
            $table->string('name');
            $table->unsignedInteger('price');
            $table->unsignedInteger('quantity');
            $table->jsonb('attributes')->nullable();
            $table->jsonb('conditions')->nullable();
            $table->string('associated_model')->nullable();
            $table->timestamps();

            $table->index(['cart_id', 'item_id']);
            $table->index('name');
            $table->index('price');
            $table->index('quantity');
            $table->index('associated_model');
            $table->index('created_at');
            $table->index('updated_at');
        });

        // Create the cart_snapshot_conditions table
        Schema::create('cart_snapshot_conditions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cart_id')->constrained('cart_snapshots')->onDelete('cascade');
            $table->foreignUuid('cart_item_id')->nullable()->constrained('cart_snapshot_items')->onDelete('cascade');
            $table->string('name');
            $table->string('type');
            $table->string('target');
            $table->string('value');
            $table->string('operator')->nullable();
            $table->boolean('is_charge')->default(false);
            $table->boolean('is_dynamic')->default(false);
            $table->boolean('is_discount')->default(false);
            $table->boolean('is_percentage')->default(false);
            $table->boolean('is_global')->default(false);
            $table->string('parsed_value')->nullable();
            $table->jsonb('rules')->nullable();
            $table->integer('order')->default(0);
            $table->jsonb('attributes')->nullable();
            $table->string('item_id')->nullable()->index();
            $table->timestamps();

            $table->index(['cart_id', 'name']);
            $table->index('name');
            $table->index('type');
            $table->index('target');
            $table->index('order');
            $table->index('is_discount');
            $table->index('is_charge');
            $table->index('is_percentage');
            $table->index('is_dynamic');
            $table->index('is_global');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Register event listeners for cart synchronization
     */
    protected function registerEventListeners(): void
    {
        $currentAppId = spl_object_id($this->app);

        if (self::$listenerRegistrationId === $currentAppId) {
            return;
        }

        self::$listenerRegistrationId = $currentAppId;

        // Global conditions should always register to mimic service provider
        // Listen to specific item events instead of CartUpdated to avoid infinite loops
        \Illuminate\Support\Facades\Event::listen(
            \MasyukAI\Cart\Events\CartCreated::class,
            [\MasyukAI\FilamentCart\Listeners\ApplyGlobalConditions::class, 'handleCartCreated']
        );
        \Illuminate\Support\Facades\Event::listen(
            \MasyukAI\Cart\Events\ItemAdded::class,
            [\MasyukAI\FilamentCart\Listeners\ApplyGlobalConditions::class, 'handleItemChanged']
        );
        \Illuminate\Support\Facades\Event::listen(
            \MasyukAI\Cart\Events\ItemUpdated::class,
            [\MasyukAI\FilamentCart\Listeners\ApplyGlobalConditions::class, 'handleItemChanged']
        );
        \Illuminate\Support\Facades\Event::listen(
            \MasyukAI\Cart\Events\ItemRemoved::class,
            [\MasyukAI\FilamentCart\Listeners\ApplyGlobalConditions::class, 'handleItemChanged']
        );

        // Unified sync listener for all cart events
        \Illuminate\Support\Facades\Event::listen(
            [
                \MasyukAI\Cart\Events\CartCreated::class,
                \MasyukAI\Cart\Events\CartCleared::class,
                \MasyukAI\Cart\Events\ItemAdded::class,
                \MasyukAI\Cart\Events\ItemUpdated::class,
                \MasyukAI\Cart\Events\ItemRemoved::class,
                \MasyukAI\Cart\Events\CartConditionAdded::class,
                \MasyukAI\Cart\Events\CartConditionRemoved::class,
                \MasyukAI\Cart\Events\ItemConditionAdded::class,
                \MasyukAI\Cart\Events\ItemConditionRemoved::class,
            ],
            \MasyukAI\FilamentCart\Listeners\SyncCartOnEvent::class
        );
    }
}
