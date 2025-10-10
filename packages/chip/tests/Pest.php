<?php

declare(strict_types=1);

use MasyukAI\Chip\Tests\Feature\FeatureTestCase;
use MasyukAI\Chip\Tests\TestCase;

uses(FeatureTestCase::class)->in(__DIR__.'/Feature');
uses(TestCase::class)->in(__DIR__.'/Unit', __DIR__.'/Http');

function getTestPublicKey(): string
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

if (! function_exists('config')) {
    function config(string $key, $default = null)
    {
        return Illuminate\Support\Facades\Config::get($key, $default);
    }
}

// Custom expectations for CHIP package testing
expect()->extend('toBeChipPurchase', function () {
    return $this->toBeInstanceOf(MasyukAI\Chip\DataObjects\Purchase::class);
});

expect()->extend('toBeChipPayment', function () {
    return $this->toBeInstanceOf(MasyukAI\Chip\DataObjects\Payment::class);
});

expect()->extend('toBeChipSendInstruction', function () {
    return $this->toBeInstanceOf(MasyukAI\Chip\DataObjects\SendInstruction::class);
});

expect()->extend('toBeChipBankAccount', function () {
    return $this->toBeInstanceOf(MasyukAI\Chip\DataObjects\BankAccount::class);
});

expect()->extend('toBeChipCompanyStatement', function () {
    return $this->toBeInstanceOf(MasyukAI\Chip\DataObjects\CompanyStatement::class);
});

expect()->extend('toBeChipSendLimit', function () {
    return $this->toBeInstanceOf(MasyukAI\Chip\DataObjects\SendLimit::class);
});

expect()->extend('toBeChipSendWebhook', function () {
    return $this->toBeInstanceOf(MasyukAI\Chip\DataObjects\SendWebhook::class);
});

expect()->extend('toBeChipClient', function () {
    return $this->toBeInstanceOf(MasyukAI\Chip\DataObjects\Client::class);
});

expect()->extend('toBeChipWebhook', function () {
    return $this->toBeInstanceOf(MasyukAI\Chip\DataObjects\Webhook::class);
});

// Test data factories
function createPurchaseData(array $overrides = []): array
{
    return array_merge([
        'id' => 'purchase_'.uniqid(),
        'amount_in_cents' => 10000,
        'currency' => 'MYR',
        'reference' => 'ORDER_'.rand(1000, 9999),
        'checkout_url' => 'https://gate.chip-in.asia/checkout/test',
        'status' => 'created',
        'is_recurring' => false,
        'metadata' => null,
        'client_id' => null,
        'created_at' => now()->toISOString(),
        'updated_at' => now()->toISOString(),
    ], $overrides);
}

function createPaymentData(string $purchaseId, array $overrides = []): array
{
    return array_merge([
        'id' => 'payment_'.uniqid(),
        'purchase_id' => $purchaseId,
        'amount_in_cents' => 10000,
        'currency' => 'MYR',
        'status' => 'successful',
        'method' => 'fpx',
        'paid_at' => now()->toISOString(),
        'transaction_fee_in_cents' => 50,
        'metadata' => null,
    ], $overrides);
}

function createSendInstructionData(array $overrides = []): array
{
    return array_merge([
        'id' => 'send_'.uniqid(),
        'reference' => 'TRANSFER_'.rand(1000, 9999),
        'amount_in_cents' => 50000,
        'currency' => 'MYR',
        'recipient_bank_account_id' => 'bank_account_'.uniqid(),
        'recipient_details' => null,
        'description' => 'Test transfer',
        'status' => 'pending',
        'metadata' => null,
        'sent_at' => null,
        'completed_at' => null,
        'failure_reason' => null,
    ], $overrides);
}

function createBankAccountData(array $overrides = []): array
{
    return array_merge([
        'id' => 'bank_account_'.uniqid(),
        'bank_code' => 'MBBEMYKL',
        'account_number' => '1234567890123456',
        'account_holder_name' => 'John Doe',
        'account_type' => 'savings',
        'is_active' => true,
        'is_verified' => false,
        'verified_at' => null,
        'verification_details' => null,
        'metadata' => null,
    ], $overrides);
}

function createClientData(array $overrides = []): array
{
    return array_merge([
        'id' => 'client_'.uniqid(),
        'full_name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+60123456789',
        'address' => null,
        'identity_type' => null,
        'identity_number' => null,
        'date_of_birth' => null,
        'nationality' => null,
        'metadata' => null,
    ], $overrides);
}

function createWebhookData(string $event, array $data, array $overrides = []): array
{
    return array_merge([
        'event' => $event,
        'data' => $data,
        'timestamp' => now()->toISOString(),
    ], $overrides);
}

// Test helpers for assertions
function assertPurchaseEquals(array $expected, MasyukAI\Chip\DataObjects\Purchase $actual): void
{
    expect($actual->id)->toBe($expected['id']);
    expect($actual->amountInCents)->toBe($expected['amount_in_cents']);
    expect($actual->currency)->toBe($expected['currency']);
    expect($actual->reference)->toBe($expected['reference'] ?? null);
    expect($actual->status)->toBe($expected['status']);
}

function assertPaymentEquals(array $expected, MasyukAI\Chip\DataObjects\Payment $actual): void
{
    expect($actual->id)->toBe($expected['id']);
    expect($actual->purchaseId)->toBe($expected['purchase_id']);
    expect($actual->amountInCents)->toBe($expected['amount_in_cents']);
    expect($actual->currency)->toBe($expected['currency']);
    expect($actual->status)->toBe($expected['status']);
}

function assertSendInstructionEquals(array $expected, MasyukAI\Chip\DataObjects\SendInstruction $actual): void
{
    expect($actual->id)->toBe($expected['id']);
    expect($actual->amountInCents)->toBe($expected['amount_in_cents']);
    expect($actual->currency)->toBe($expected['currency']);
    expect($actual->recipientBankAccountId)->toBe($expected['recipient_bank_account_id']);
    expect($actual->status)->toBe($expected['status']);
}

function assertBankAccountEquals(array $expected, MasyukAI\Chip\DataObjects\BankAccount $actual): void
{
    expect($actual->id)->toBe($expected['id']);
    expect($actual->bankCode)->toBe($expected['bank_code']);
    expect($actual->accountNumber)->toBe($expected['account_number']);
    expect($actual->accountHolderName)->toBe($expected['account_holder_name']);
    expect($actual->isActive)->toBe($expected['is_active']);
}

function assertClientEquals(array $expected, MasyukAI\Chip\DataObjects\Client $actual): void
{
    expect($actual->id)->toBe($expected['id']);
    expect($actual->fullName)->toBe($expected['full_name']);
    expect($actual->email)->toBe($expected['email'] ?? null);
    expect($actual->phone)->toBe($expected['phone'] ?? null);
}
