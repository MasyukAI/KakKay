<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use MasyukAI\Chip\Events\WebhookReceived;
use MasyukAI\Chip\Services\WebhookService;

beforeEach(function (): void {
    Event::fake([WebhookReceived::class]);
    config()->set('chip.webhooks.verify_signature', false);
    config()->set('logging.channels.chip_test', [
        'driver' => 'monolog',
        'handler' => \Monolog\Handler\NullHandler::class,
    ]);
    config()->set('chip.logging.channel', 'chip_test');
});

describe('Webhook processing', function (): void {
    it('dispatches purchase.created events', function (): void {
        $payload = $this->createWebhookPayload('purchase.created', [
            'id' => 'purchase_123',
            'status' => 'created',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
        ]);

        $this->postJson('/chip/webhook', $payload, [
            'X-Signature' => 'test-signature',
        ])->assertSuccessful();

        Event::assertDispatched(WebhookReceived::class, function (WebhookReceived $event) {
            return $event->webhook->event === 'purchase.created';
        });
    });

    it('dispatches purchase.paid events', function (): void {
        $payload = $this->createWebhookPayload('purchase.paid', [
            'id' => 'purchase_456',
            'status' => 'paid',
            'amount_in_cents' => 15000,
            'currency' => 'MYR',
        ]);

        $this->postJson('/chip/webhook', $payload, [
            'X-Signature' => 'test-signature',
        ])->assertSuccessful();

        Event::assertDispatched(WebhookReceived::class, function (WebhookReceived $event) {
            return $event->webhook->event === 'purchase.paid';
        });
    });

    it('rejects requests without signatures when verification is enabled', function (): void {
        config()->set('chip.webhooks.verify_signature', true);

        $this->postJson('/chip/webhook', [
            'event' => 'purchase.created',
            'data' => ['id' => 'purchase_123'],
        ])->assertForbidden();

        config()->set('chip.webhooks.verify_signature', false);
    });

    it('validates required payload fields', function (): void {
        $this->postJson('/chip/webhook', [])->assertUnprocessable();
    });
});

describe('Webhook controller interactions', function (): void {
    it('returns ok when webhook service processes request', function (): void {
        $service = \Mockery::mock(WebhookService::class);
        $service->shouldReceive('processWebhook')->once()->andReturnTrue();

        $original = app(WebhookService::class);
        app()->instance(WebhookService::class, $service);

        try {
            $this->postJson('/chip/webhook', [
                'event' => 'purchase.created',
                'data' => ['id' => 'purchase_789'],
            ], [
                'X-Signature' => 'test-signature',
            ])->assertOk()->assertSee('OK');
        } finally {
            app()->instance(WebhookService::class, $original);
        }
    });

    it('logs and returns error when webhook processing fails', function (): void {
        Log::spy();
        Log::shouldReceive('channel')->andReturnSelf();

        $service = \Mockery::mock(WebhookService::class);
        $service->shouldReceive('processWebhook')
            ->once()
            ->andThrow(new \RuntimeException('unexpected failure'));

        $original = app(WebhookService::class);
        app()->instance(WebhookService::class, $service);

        try {
            $this->postJson('/chip/webhook', [
                'event' => 'purchase.created',
                'data' => ['id' => 'purchase_789'],
            ])->assertStatus(400)->assertSee('Error processing webhook');
        } finally {
            app()->instance(WebhookService::class, $original);
        }

        Log::shouldHaveLogged('error', function ($message, $context): bool {
            return str_contains($message, 'CHIP Webhook processing failed')
                && $context['error'] === 'unexpected failure';
        });
    });

    it('rejects success callbacks without signature or payload', function (): void {
        $this->postJson('/chip/webhooks/success', [])
            ->assertStatus(400)
            ->assertSee('Missing signature or payload');
    });

    it('returns unauthorized when success callback signature is invalid', function (): void {
        $payload = ['event_type' => 'purchase.paid'];

        $service = \Mockery::mock(WebhookService::class);
        $service->shouldReceive('getPublicKey')->once()->andReturn('public-key');
        $service->shouldReceive('verifySignature')
            ->once()
            ->withArgs(function (string $rawPayload, string $signature, string $publicKey) use ($payload): bool {
                expect(json_decode($rawPayload, true))->toMatchArray($payload);

                return $signature === 'invalid-signature' && $publicKey === 'public-key';
            })
            ->andReturnFalse();

        $original = app(WebhookService::class);
        app()->instance(WebhookService::class, $service);

        try {
            $this->postJson('/chip/webhooks/success', $payload, [
                'X-Signature' => 'invalid-signature',
            ])->assertStatus(401)->assertSee('Invalid signature');
        } finally {
            app()->instance(WebhookService::class, $original);
        }
    });

    it('processes success callbacks and dispatches events', function (): void {
        Event::fake([WebhookReceived::class]);
        Log::spy();
        Log::shouldReceive('channel')->andReturnSelf();

        $payload = [
            'event_type' => 'purchase.paid',
            'data' => ['id' => 'purchase_123'],
        ];

        $service = \Mockery::mock(WebhookService::class);
        $service->shouldReceive('getPublicKey')->once()->andReturn('public-key');
        $service->shouldReceive('verifySignature')
            ->once()
            ->withArgs(function (string $rawPayload, string $signature, string $publicKey) use ($payload): bool {
                expect(json_decode($rawPayload, true))->toMatchArray($payload);

                return $signature === 'valid-signature' && $publicKey === 'public-key';
            })
            ->andReturnTrue();
        $service->shouldReceive('parsePayload')
            ->once()
            ->with(Mockery::type('string'))
            ->andReturn((object) $payload);

        $original = app(WebhookService::class);
        app()->instance(WebhookService::class, $service);

        try {
            $this->postJson('/chip/webhooks/success', $payload, [
                'X-Signature' => 'valid-signature',
            ])->assertOk()->assertSee('OK');
        } finally {
            app()->instance(WebhookService::class, $original);
        }

        Log::shouldHaveLogged('info', function ($message, $context): bool {
            return str_contains($message, 'CHIP Success callback received')
                && $context['event_type'] === 'purchase.paid';
        });

        Event::assertDispatched(WebhookReceived::class, function (WebhookReceived $event): bool {
            return $event->webhook->event === 'purchase.success';
        });
    });

    it('logs errors when success callbacks throw exceptions', function (): void {
        Log::spy();
        Log::shouldReceive('channel')->andReturnSelf();

        $payload = ['event_type' => 'purchase.paid'];

        $service = \Mockery::mock(WebhookService::class);
        $service->shouldReceive('getPublicKey')->once()->andReturn('public-key');
        $service->shouldReceive('verifySignature')->once()->andThrow(new \RuntimeException('signature failure'));

        $original = app(WebhookService::class);
        app()->instance(WebhookService::class, $service);

        try {
            $this->postJson('/chip/webhooks/success', $payload, [
                'X-Signature' => 'valid',
            ])->assertStatus(400)->assertSee('Error processing callback');
        } finally {
            app()->instance(WebhookService::class, $original);
        }

        Log::shouldHaveLogged('error', function ($message, $context): bool {
            return str_contains($message, 'CHIP Success callback processing failed')
                && $context['error'] === 'signature failure'
                && array_key_exists('trace', $context);
        });
    });
});
