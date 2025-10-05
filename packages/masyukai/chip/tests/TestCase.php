<?php

namespace MasyukAI\Chip\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use MasyukAI\Chip\ChipServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Load package migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            ChipServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup package database configuration
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set up package-specific configuration early
        $app['config']->set('chip.collect.api_key', 'test_api_key_12345');
        $app['config']->set('chip.collect.brand_id', 'test_brand_12345');
        $app['config']->set('chip.collect.secret_key', 'test_secret_key');
        $app['config']->set('chip.collect.environment', 'sandbox');
        $app['config']->set('chip.collect.base_url', 'https://gate.chip-in.asia/api/v1/');
        $app['config']->set('chip.collect.timeout', 30);
        $app['config']->set('chip.collect.retry', ['times' => 3, 'sleep' => 1000]);
        $app['config']->set('chip.send.api_key', 'test_api_key');
        $app['config']->set('chip.send.secret_key', 'test_send_secret');
        $app['config']->set('chip.send.base_url', [
            'sandbox' => 'https://staging-api.chip-in.asia/api',
            'production' => 'https://api.chip-in.asia/api',
        ]);
        $app['config']->set('chip.is_sandbox', true);
        $app['config']->set('chip.webhook.public_key', $this->getTestPublicKey());
        $app['config']->set('chip.webhooks.verify_signature', false); // Disable for testing
        $app['config']->set('chip.database.connection', 'testing');
        $app['config']->set('chip.database.table_prefix', 'chip_');
    }

    protected function getTestPublicKey(): string
    {
        return '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAyV7Z8iFMnkLJSPwEW8P1
GrT0xP3ZdQKk9L1mJY6fRd4QwQ8F7mW9vJx2Q3h8gKl7QvW0x3ZqF8cNx1Q5wNY
YKdF2L9m8rT7vKxH1qFy0Z8vRnKl6Q7x9F3mJ1wEzGz8KjF3vP9Qr4xY1wL6nM8
UvX2z4RqT1wKzF4N3rP7vBQ9L8mF6xY2wQ8vR1nP3zK4x7Q5FwM1Y8KjL9vWzP6
rBQ2x4F7mY1wNzR8vP3qK6x5Q7FwMzY9KjL8vWzP7rBQ3x4F8mY2wOzR9vP4qK7
x6Q8FwNzY0KjL9vWzP8rBQ4x4F9mY3wPzS0vP5qK8x7Q9FwOzY1KjM0vWzP9rB
Q5x4G0mY4wQzS1vP6qK9x8R0FwPzY2KjM1vWzQ0rBQ6x4G1mY5wRzS2vP7qL0x
9R1FwQzY3KjM2vWzQ1rBQ7x4G2mY6wSzS3vP8qL1y0R2FwRzY4KjM3vWzQ2rB
Q8x4G3mY7wTzS4vP9qL2y1R3FwSzY5KjM4vWzQ3rBQ9x4G4mY8wUzS5vQ0qL3
y2R4FwTzY6KjM5vWzQ4rBR0x4G5mY9wVzS6vQ1qL4y3R5FwUzY7KjM6vWzQ5r
BR1x4G6mZ0wWzS7vQ2qL5y4R6FwVzY8KjM7vWzQ6rBR2x4G7mZ1wXzS8vQ3qL
6y5R7FwWzY9KjM8vWzQ7rBR3x4G8mZ2wYzS9vQ4qL7y6R8FwXzZ0KjM9vWzQ8
rBR4x4G9mZ3wZzT0vQ5qL8y7R9FwYzZ1KjN0vWzQ9rBR5x4H0mZ4w0zT1vQ6q
L9y8S0FwZzZ2KjN1vWzR0rBR6x4H1mZ5w1zT2vQ7qM0y9S1FwazZ3KjN2vWz
R1rBR7x4H2mZ6w2zT3vQ8qM1z0S2Fw
-----END PUBLIC KEY-----';
    }
}
