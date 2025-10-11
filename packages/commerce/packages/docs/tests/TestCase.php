<?php

declare(strict_types=1);

namespace MasyukAI\Docs\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use MasyukAI\Docs\DocsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName): string => 'MasyukAI\\Invoice\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        $migration = include __DIR__.'/../database/migrations/2025_01_01_000002_create_docs_tables.php';
        $migration->up();
    }

    protected function getPackageProviders($app): array
    {
        return [
            DocsServiceProvider::class,
        ];
    }
}
