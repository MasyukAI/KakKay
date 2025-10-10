<?php

declare(strict_types=1);

namespace MasyukAI\Stock\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MasyukAI\Stock\StockServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName): string => 'MasyukAI\\Stock\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        // Create test tables
        $this->setUpDatabase();
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            StockServiceProvider::class,
        ];
    }

    protected function setUpDatabase(): void
    {
        // Create users table for testing
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });

        // Create test products table
        Schema::create('test_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        // Run the stock migration
        $migration = include __DIR__.'/../database/migrations/2025_01_01_000001_create_stock_transactions_table.php';
        $migration->up();
    }
}
