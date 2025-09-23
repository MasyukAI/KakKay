<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use MasyukAI\Chip\Events\WebhookReceived;
use MasyukAI\Chip\Tests\TestCase;

class FeatureTestCase extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Load package migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Set up test configuration
        config([
            'chip.collect.secret_key' => 'test_secret_key',
            'chip.collect.brand_id' => 'test_brand_id',
            'chip.send.api_key' => 'test_api_key',
            'chip.send.api_secret' => 'test_api_secret',
            'chip.is_sandbox' => true,
            'chip.webhooks.verify_signature' => false, // Disable signature verification for tests
            'chip.logging.channel' => 'single', // Use single channel for tests
            'logging.channels.single.driver' => 'single',
            'logging.channels.single.path' => storage_path('logs/laravel.log'),
        ]);
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

    protected function createTestPurchase(array $overrides = []): array
    {
        return array_merge([
            'id' => 'purchase_'.$this->faker->uuid,
            'amount_in_cents' => $this->faker->numberBetween(100, 100000),
            'currency' => 'MYR',
            'reference' => 'ORDER_'.$this->faker->randomNumber(6),
            'checkout_url' => $this->faker->url,
            'status' => 'created',
            'is_recurring' => false,
            'metadata' => ['test' => true],
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ], $overrides);
    }

    protected function createTestPayment(string $purchaseId, array $overrides = []): array
    {
        return array_merge([
            'id' => 'payment_'.$this->faker->uuid,
            'purchase_id' => $purchaseId,
            'amount_in_cents' => $this->faker->numberBetween(100, 100000),
            'currency' => 'MYR',
            'status' => 'successful',
            'method' => 'fpx',
            'paid_at' => now()->toISOString(),
            'transaction_fee_in_cents' => 50,
            'metadata' => ['gateway' => 'test'],
        ], $overrides);
    }

    protected function createTestSendInstruction(array $overrides = []): array
    {
        return array_merge([
            'id' => 'send_'.$this->faker->uuid,
            'reference' => 'TRANSFER_'.$this->faker->randomNumber(6),
            'amount_in_cents' => $this->faker->numberBetween(1000, 100000),
            'currency' => 'MYR',
            'recipient_bank_account_id' => 'bank_account_'.$this->faker->uuid,
            'description' => $this->faker->sentence,
            'status' => 'pending',
            'metadata' => ['test' => true],
        ], $overrides);
    }

    protected function createTestBankAccount(array $overrides = []): array
    {
        return array_merge([
            'id' => 'bank_account_'.$this->faker->uuid,
            'bank_code' => 'MBBEMYKL',
            'account_number' => $this->faker->bankAccountNumber,
            'account_holder_name' => $this->faker->name,
            'account_type' => 'savings',
            'is_active' => true,
            'is_verified' => false,
        ], $overrides);
    }

    protected function createTestClient(array $overrides = []): array
    {
        return array_merge([
            'id' => 'client_'.$this->faker->uuid,
            'full_name' => $this->faker->name,
            'email' => $this->faker->email,
            'phone' => '+60'.$this->faker->numerify('#########'),
            'address' => [
                'line1' => $this->faker->streetAddress,
                'city' => $this->faker->city,
                'state' => $this->faker->state,
                'country' => 'MY',
            ],
        ], $overrides);
    }

    protected function createWebhookPayload(string $event, array $data, array $overrides = []): array
    {
        return array_merge([
            'event' => $event,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ], $overrides);
    }

    protected function signWebhookPayload(array $payload): string
    {
        // In a real implementation, this would use proper RSA signing
        // For testing, we'll use a mock signature
        return base64_encode('test_signature_'.md5(json_encode($payload)));
    }

    protected function assertDatabaseHasChipRecord(string $table, array $data): void
    {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');
        $this->assertDatabaseHas($tablePrefix.$table, $data);
    }

    protected function assertDatabaseMissingChipRecord(string $table, array $data): void
    {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');
        $this->assertDatabaseMissing($tablePrefix.$table, $data);
    }
}

describe('Package Integration Tests', function () {
    it('loads package service provider correctly', function () {
        expect(app()->bound('chip.collect'))->toBeTrue();
        expect(app()->bound('chip.send'))->toBeTrue();
        expect(app()->bound('chip.webhook'))->toBeTrue();
    });

    it('registers package routes', function () {
        $routes = collect(app('router')->getRoutes())->map(fn ($route) => $route->uri());

        expect($routes->contains('chip/webhook'))->toBeTrue();
    });

    it('publishes package migrations', function () {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');

        expect(Schema::hasTable($tablePrefix.'purchases'))->toBeTrue();
        expect(Schema::hasTable($tablePrefix.'payments'))->toBeTrue();
        expect(Schema::hasTable($tablePrefix.'webhooks'))->toBeTrue();
        expect(Schema::hasTable($tablePrefix.'send_instructions'))->toBeTrue();
        expect(Schema::hasTable($tablePrefix.'bank_accounts'))->toBeTrue();
        expect(Schema::hasTable($tablePrefix.'clients'))->toBeTrue();
    });

    it('configures package correctly from config file', function () {
        expect(config('chip.collect.secret_key'))->toBe('test_secret_key');
        expect(config('chip.send.api_key'))->toBe('test_api_key');
        expect(config('chip.is_sandbox'))->toBeTrue();
    });
});

describe('End-to-End Webhook Processing', function () {
    it('processes purchase.created webhook end-to-end', function () {
        Event::fake();

        $webhookPayload = [
            'event' => 'purchase.created',
            'data' => [
                'id' => 'purchase_123',
                'type' => 'purchase',
                'status' => 'created',
                'amount' => 10000,
                'currency' => 'MYR',
                'client' => ['email' => 'test@example.com'],
                'purchase' => ['total' => 10000, 'currency' => 'MYR', 'products' => []],
                'created_on' => time(),
                'updated_on' => time(),
            ],
            'timestamp' => now()->toISOString(),
        ];

        $signature = base64_encode(hash_hmac('sha256', json_encode($webhookPayload), 'test_secret', true));

        $response = test()->postJson('/chip/webhook', $webhookPayload, [
            'X-Signature' => $signature,
        ]);

        $response->assertStatus(200);
        Event::assertDispatched(WebhookReceived::class);
    });

    it('processes purchase.paid webhook end-to-end', function () {
        Event::fake();

        $webhookPayload = [
            'event' => 'purchase.paid',
            'data' => [
                'id' => 'purchase_456',
                'type' => 'purchase',
                'status' => 'paid',
                'amount' => 15000,
                'currency' => 'MYR',
                'client' => ['email' => 'customer@example.com'],
                'purchase' => ['total' => 15000, 'currency' => 'MYR', 'products' => []],
                'created_on' => time(),
                'updated_on' => time(),
            ],
            'timestamp' => now()->toISOString(),
        ];

        $signature = base64_encode(hash_hmac('sha256', json_encode($webhookPayload), 'test_secret', true));

        $response = test()->postJson('/chip/webhook', $webhookPayload, [
            'X-Signature' => $signature,
        ]);

        $response->assertStatus(200);
        Event::assertDispatched(WebhookReceived::class);
    });

    it('processes webhook with invalid signature when verification is disabled', function () {
        $webhookPayload = [
            'event' => 'purchase.created',
            'data' => [
                'id' => 'purchase_789',
                'type' => 'purchase',
                'status' => 'created',
                'amount' => 5000,
                'currency' => 'MYR',
                'client' => ['email' => 'test@example.com'],
                'purchase' => ['total' => 5000, 'currency' => 'MYR', 'products' => []],
                'created_on' => time(),
                'updated_on' => time(),
            ],
            'timestamp' => now()->toISOString(),
        ];

        $response = test()->postJson('/chip/webhook', $webhookPayload, [
            'X-Signature' => 'invalid_signature',
        ]);

        // When signature verification is disabled, webhook should still process successfully
        $response->assertStatus(200);
    });
});

describe('Database Persistence Integration', function () {
    it('stores webhook data in database', function () {
        $webhookData = [
            'id' => '550e8400-e29b-41d4-a716-446655440000', // UUID
            'title' => 'Test Webhook',
            'events' => json_encode(['purchase.paid']),
            'callback' => 'https://example.com/webhook',
            'all_events' => false,
            'public_key' => 'test_key',
            'created_on' => time(),
            'updated_on' => time(),
            'event_type' => 'purchase.paid',
            'payload' => json_encode(['test' => 'data']),
            'headers' => json_encode(['X-Signature' => 'test']),
            'signature' => 'test_signature',
            'verified' => true,
            'processed' => true,
        ];

        DB::table('chip_webhooks')->insert($webhookData);

        $tablePrefix = config('chip.database.table_prefix', 'chip_');
        expect(DB::table($tablePrefix.'webhooks')->where('event_type', 'purchase.paid')->exists())->toBeTrue();
    });

    it('stores purchase data in database', function () {
        $purchaseData = [
            'id' => '550e8400-e29b-41d4-a716-446655440001', // UUID
            'type' => 'purchase',
            'created_on' => time(),
            'updated_on' => time(),
            'client' => json_encode(['email' => 'test@example.com']),
            'purchase' => json_encode(['total' => 10000, 'currency' => 'MYR']),
            'brand_id' => '550e8400-e29b-41d4-a716-446655440002',
            'company_id' => '550e8400-e29b-41d4-a716-446655440003',
            'issuer_details' => json_encode(['legal_name' => 'Test Company']),
            'transaction_data' => json_encode(['payment_method' => 'card']),
            'status_history' => json_encode([]),
            'status' => 'created',
            'send_receipt' => false,
            'is_test' => true,
            'is_recurring_token' => false,
            'skip_capture' => false,
            'force_recurring' => false,
            'reference_generated' => 'REF123',
            'refund_availability' => 'all',
            'refundable_amount' => 0,
            'platform' => 'api',
            'product' => 'purchases',
            'marked_as_paid' => false,
        ];

        DB::table('chip_purchases')->insert($purchaseData);

        $tablePrefix = config('chip.database.table_prefix', 'chip_');
        expect(DB::table($tablePrefix.'purchases')->where('id', '550e8400-e29b-41d4-a716-446655440001')->exists())->toBeTrue();
    });
});
