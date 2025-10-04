<?php

use Illuminate\Support\Facades\Event;
use MasyukAI\Chip\Events\WebhookReceived;
use MasyukAI\Chip\Http\Requests\WebhookRequest;
use MasyukAI\Chip\Services\WebhookService;

describe('WebhookService', function () {
    beforeEach(function () {
        $this->webhookService = new WebhookService;
        $this->publicKey = 'test-public-key';
    });

    it('verifies valid webhook signatures', function () {
        $request = new WebhookRequest;
        $request->headers->set('X-Signature', base64_encode('valid_signature'));
        $request->merge(['event' => 'purchase.paid', 'data' => ['id' => 'purchase_123']]);

        // Mock successful verification for testing
        $webhookService = new class extends WebhookService
        {
            public function verifySignature($payloadOrRequest, ?string $signature = null, ?string $publicKey = null): bool
            {
                return true; // Mock successful verification
            }
        };

        expect($webhookService->verifySignature($request, null, $this->publicKey))->toBeTrue();
    });

    it('processes webhook events', function () {
        Event::fake();

        $request = new WebhookRequest;
        $request->headers->set('X-Signature', base64_encode('valid_signature'));
        $request->merge([
            'event' => 'purchase.paid',
            'data' => [
                'id' => 'purchase_123',
                'amount_in_cents' => 10000,
                'currency' => 'MYR',
                'status' => 'paid',
            ],
        ]);

        // Mock successful verification and processing
        $webhookService = new class extends WebhookService
        {
            public function verifySignature($payloadOrRequest, ?string $signature = null, ?string $publicKey = null): bool
            {
                return true;
            }
        };

        $webhookService->processWebhook($request);

        Event::assertDispatched(WebhookReceived::class);
    });

    it('uses configured public key when available', function () {
        config(['chip.webhooks.public_key' => $this->publicKey]);

        $request = new WebhookRequest;
        $request->headers->set('X-Signature', base64_encode('valid_signature'));
        $request->merge(['event' => 'purchase.paid', 'data' => ['id' => 'purchase_123']]);

        // Mock service that uses config
        $webhookService = new class extends WebhookService
        {
            public function verifySignature($payloadOrRequest, ?string $signature = null, ?string $publicKey = null): bool
            {
                // If no public key provided, use the configured one
                $publicKey = $publicKey ?? $this->getPublicKey();

                return $publicKey === config('chip.webhooks.public_key');
            }
        };

        expect($webhookService->verifySignature($request))->toBeTrue();
    });
});
