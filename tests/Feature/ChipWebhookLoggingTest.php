<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

use function Pest\Laravel\withoutExceptionHandling;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('chip.webhooks.verify_signature', false);
    config()->set('chip.webhooks.company_public_key', 'unit-test-public-key');
    config()->set('chip.webhooks.webhook_keys', [
        'demo-webhook' => 'webhook-test-public-key',
    ]);

    Log::spy();
});

it('logs raw payload for success callback mode', function (): void {
    withoutExceptionHandling();

    $payload = [
        'event' => 'purchase.paid',
        'data' => [
            'id' => 'purchase-success',
            'reference' => 'cart-success',
        ],
    ];

    $response = $this->postJson(route('webhooks.chip'), $payload);

    $response->assertOk();

    Log::shouldHaveReceived('info')
        ->withArgs(fn (string $message, array $context): bool => $message === 'CHIP webhook signature verified'
            && ($context['mode'] ?? null) === 'success_callback')
        ->once();

    Log::shouldHaveReceived('debug')
        ->withArgs(fn (string $message, array $context): bool => $message === 'CHIP webhook raw payload captured'
            && ($context['mode'] ?? null) === 'success_callback'
            && ($context['raw_length'] ?? 0) > 0
            && str_contains($context['raw_preview'] ?? '', 'purchase-success'))
        ->once();
});

it('logs raw payload for webhook mode', function (): void {
    withoutExceptionHandling();

    $payload = [
        'event' => null,
        'data' => [
            'id' => 'purchase-webhook',
            'reference' => 'cart-webhook',
        ],
    ];

    $response = $this->postJson(route('webhooks.chip', ['webhook' => 'demo-webhook']), $payload);

    $response->assertOk();

    Log::shouldHaveReceived('info')
        ->withArgs(fn (string $message, array $context): bool => $message === 'CHIP webhook signature verified'
            && ($context['mode'] ?? null) === 'webhook'
            && ($context['webhook_id'] ?? null) === 'demo-webhook')
        ->once();

    Log::shouldHaveReceived('debug')
        ->withArgs(fn (string $message, array $context): bool => $message === 'CHIP webhook raw payload captured'
            && ($context['mode'] ?? null) === 'webhook'
            && ($context['webhook_id'] ?? null) === 'demo-webhook'
            && str_contains($context['raw_preview'] ?? '', 'purchase-webhook'))
        ->once();
});
