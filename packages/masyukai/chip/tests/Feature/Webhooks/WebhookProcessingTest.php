<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use MasyukAI\Chip\Events\WebhookReceived;

beforeEach(function () {
    Event::fake([WebhookReceived::class]);
});

describe('Webhook processing', function () {
    it('dispatches purchase.created events', function () {
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

    it('dispatches purchase.paid events', function () {
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

    it('rejects requests without signatures when verification is enabled', function () {
        config()->set('chip.webhooks.verify_signature', true);

        $this->postJson('/chip/webhook', [
            'event' => 'purchase.created',
            'data' => ['id' => 'purchase_123'],
        ])->assertForbidden();

        config()->set('chip.webhooks.verify_signature', false);
    });

    it('validates required payload fields', function () {
        $this->postJson('/chip/webhook', [])->assertUnprocessable();
    });
});
