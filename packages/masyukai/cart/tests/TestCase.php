<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use MasyukAI\Cart\CartServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
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

        // Force DecimalPriceTransformer for consistent tests
        $app['config']->set('cart.price_formatting.transformer', \MasyukAI\Cart\PriceTransformers\DecimalPriceTransformer::class);

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
        $app['config']->set('cart.database.table', 'carts_test');
        $app['config']->set('cart.events', true);
        $app['config']->set('cart.strict_validation', true);
    }

    protected function setUpDatabase(): void
    {
        $this->artisan('migrate', [
            '--database' => 'testing',
            '--path' => __DIR__.'/../database/migrations',
        ]);

        // Create test-specific table with new structure including version column
        $this->app['db']->connection('testing')->getSchemaBuilder()->create('carts_test', function ($table) {
            $table->id();
            $table->string('identifier')->index()->comment('auth()->id() for authenticated users, session()->id() for guests');
            $table->string('instance')->default('default')->index()->comment('Cart instance name for multiple carts per identifier');
            $table->longText('items')->nullable()->comment('Serialized cart items');
            $table->longText('conditions')->nullable()->comment('Serialized cart conditions');
            $table->longText('metadata')->nullable()->comment('Serialized cart metadata');
            $table->bigInteger('version')->default(1)->index()->comment('Version number for optimistic locking');
            $table->timestamps();

            $table->unique(['identifier', 'instance']);
        });
    }
}
