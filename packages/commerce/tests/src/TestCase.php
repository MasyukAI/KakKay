<?php

declare(strict_types=1);

namespace AIArmada\Commerce\Tests;

use AIArmada\Cart\CartServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Start session for Livewire/Filament tests
        $this->app['session']->start();

        // Share an empty error bag so Blade always receives the expected variable
        $this->app['view']->share('errors', tap(new ViewErrorBag(), static function (ViewErrorBag $bag): void {
            $bag->put('default', new MessageBag());
        }));

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
            \Illuminate\Translation\TranslationServiceProvider::class,
            \Illuminate\Validation\ValidationServiceProvider::class,
            \Livewire\LivewireServiceProvider::class,
            \Filament\Support\SupportServiceProvider::class,
            \Filament\FilamentServiceProvider::class,
            CartServiceProvider::class,
            \AIArmada\Chip\ChipServiceProvider::class,
            \AIArmada\Jnt\JntServiceProvider::class,
            \AIArmada\Docs\DocsServiceProvider::class,
            \AIArmada\Stock\StockServiceProvider::class,
            \AIArmada\Vouchers\VoucherServiceProvider::class,
            \AIArmada\FilamentCart\FilamentCartServiceProvider::class,
            \AIArmada\FilamentChip\FilamentChipServiceProvider::class,
            TestPanelProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Cart' => \AIArmada\Cart\Facades\Cart::class,
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

        // Configure CHIP settings for testing
        $app['config']->set('chip.collect.api_key', 'test_secret_key');
        $app['config']->set('chip.collect.secret_key', 'test_secret_key'); // For backward compatibility with tests
        $app['config']->set('chip.collect.brand_id', 'test_brand_id');
        $app['config']->set('chip.collect.environment', 'sandbox');
        $app['config']->set('chip.send.api_key', 'test_api_key');
        $app['config']->set('chip.send.api_secret', 'test_send_secret');
        $app['config']->set('chip.webhooks.public_key', 'test_public_key');
        $app['config']->set('chip.is_sandbox', true);

        // Configure JNT settings for testing
        $app['config']->set('jnt.environment', 'testing');
        $app['config']->set('jnt.api_account', '640826271705595946'); // J&T official testing account
        $app['config']->set('jnt.private_key', '8e88c8477d4e4939859c560192fcafbc'); // J&T official testing key
        $app['config']->set('jnt.customer_code', 'test_customer_code');
        $app['config']->set('jnt.password', 'test_password');

        // Configure filament-chip settings for testing
        $app['config']->set('filament-chip.navigation_group', 'CHIP Operations');
        $app['config']->set('filament-chip.navigation_badge_color', 'primary');
        $app['config']->set('filament-chip.polling_interval', '45s');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../packages/chip/database/migrations');
    }

    protected function setUpDatabase(): void
    {
        // Cart tables
        Schema::dropIfExists('carts');
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

        // Docs tables
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_histories');
        Schema::dropIfExists('document_templates');

        Schema::create('document_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('view_name');
            $table->string('document_type')->default('invoice');
            $table->boolean('is_default')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('document_number')->unique();
            $table->string('document_type')->default('invoice');
            $table->foreignUuid('document_template_id')->nullable()->constrained('document_templates')->nullOnDelete();
            $table->nullableUuidMorphs('documentable');
            $table->string('status')->default('draft');
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('currency', 3)->default('MYR');
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->json('customer_data')->nullable();
            $table->json('company_data')->nullable();
            $table->json('items')->nullable();
            $table->json('metadata')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });

        Schema::create('document_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_id')->constrained('documents')->cascadeOnDelete();
            $table->string('action');
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // Stock tables
        Schema::dropIfExists('stock_transactions');
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('stockable');
            $table->uuid('user_id')->nullable();
            $table->integer('quantity');
            $table->enum('type', ['in', 'out']);
            $table->string('reason')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('transaction_date')->useCurrent();
            $table->timestamps();
        });

        // Test support table for stock testing
        Schema::dropIfExists('test_products');
        Schema::create('test_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        // Vouchers tables
        Schema::dropIfExists('voucher_usage');
        Schema::dropIfExists('vouchers');

        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type');
            $table->decimal('value', 10, 2);
            $table->string('currency', 3)->default('MYR');
            $table->decimal('min_cart_value', 10, 2)->nullable();
            $table->decimal('max_discount', 10, 2)->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_limit_per_user')->nullable();
            $table->integer('times_used')->default(0);
            $table->datetime('starts_at')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->string('status')->default('active');
            $table->json('applicable_products')->nullable();
            $table->json('excluded_products')->nullable();
            $table->json('applicable_categories')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('voucher_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained()->cascadeOnDelete();
            $table->nullableUuidMorphs('usable');
            $table->uuid('user_id')->nullable();
            $table->decimal('discount_applied', 10, 2);
            $table->timestamps();
        });
    }
}
