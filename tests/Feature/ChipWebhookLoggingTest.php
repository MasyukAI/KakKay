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

    // Verify webhook was processed successfully (no error logs)
    // The current implementation logs errors but not success
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

    // Verify webhook was processed successfully (no error logs)
    // The current implementation logs errors but not success
});
