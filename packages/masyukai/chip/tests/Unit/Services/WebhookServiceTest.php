<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use MasyukAI\Chip\Events\PurchaseCreated;
use MasyukAI\Chip\Events\PurchasePaid;
use MasyukAI\Chip\Events\WebhookReceived;
use MasyukAI\Chip\Exceptions\WebhookVerificationException;
use MasyukAI\Chip\Http\Requests\WebhookRequest;
use MasyukAI\Chip\Services\ChipCollectService;
use MasyukAI\Chip\Services\WebhookService;

describe('WebhookService', function (): void {
    beforeEach(function (): void {
        $this->webhookService = new WebhookService;

        $key = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ]);

        openssl_pkey_export($key, $privateKey);
        $details = openssl_pkey_get_details($key);

        $this->privateKey = $privateKey;
        $this->publicKey = $details['key'];

        config()->set('chip.webhooks.verify_signature', true);
        config()->set('cache.default', 'array');
        Cache::store('array')->clear();
    });

    afterEach(function (): void {
        \Mockery::close();
    });

    it('verifies valid webhook signatures', function (): void {
        $payload = json_encode([
            'event' => 'purchase.paid',
            'data' => ['id' => 'purchase_123'],
        ], JSON_THROW_ON_ERROR);

        openssl_sign($payload, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);

        expect($this->webhookService->verifySignature($payload, base64_encode($signature), $this->publicKey))
            ->toBeTrue();
    });

    it('skips signature verification when disabled', function (): void {
        config()->set('chip.webhooks.verify_signature', false);

        expect($this->webhookService->verifySignature('payload'))->toBeTrue();
    });

    it('throws when signature header is missing', function (): void {
        $request = new WebhookRequest;
        $request->replace(['event' => 'purchase.created', 'data' => []]);

        $service = new class extends WebhookService
        {
            public function getPublicKey(?string $webhookId = null): string
            {
                return 'dummy-public-key';
            }
        };

        expect(fn () => $service->verifySignature($request))
            ->toThrow(WebhookVerificationException::class, 'Missing signature header');
    });

    it('throws when signature is not valid base64', function (): void {
        $service = new class extends WebhookService
        {
            public function getPublicKey(?string $webhookId = null): string
            {
                return $this->pem ??= openssl_pkey_get_details(openssl_pkey_new([
                    'private_key_bits' => 1024,
                    'private_key_type' => OPENSSL_KEYTYPE_RSA,
                ]))['key'];
            }

            private ?string $pem = null;
        };

        expect(fn () => $service->verifySignature('payload', '***invalid***'))
            ->toThrow(WebhookVerificationException::class, 'Signature is not valid base64');
    });

    it('processes webhook events and dispatches mapped events', function (): void {
        Event::fake([
            WebhookReceived::class,
            PurchaseCreated::class,
            PurchasePaid::class,
        ]);

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
        Event::assertDispatched(PurchasePaid::class);

        $request->merge(['event' => 'purchase.created']);
        $webhookService->processWebhook($request);

        Event::assertDispatched(PurchaseCreated::class);
    });

    it('uses configured public key when available', function (): void {
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

    it('throws when webhook signature verification fails during processing', function (): void {
        $request = new WebhookRequest;
        $request->headers->set('X-Signature', 'invalid');
        $request->merge(['event' => 'purchase.paid', 'data' => []]);

        $service = new class extends WebhookService
        {
            public function verifySignature($payloadOrRequest, ?string $signature = null, ?string $publicKey = null): bool
            {
                return false;
            }
        };

        expect(fn () => $service->processWebhook($request))
            ->toThrow(WebhookVerificationException::class, 'Invalid webhook signature');
    });

    it('parses payloads and rejects invalid json', function (): void {
        $payload = json_encode(['foo' => 'bar'], JSON_THROW_ON_ERROR);

        expect($this->webhookService->parsePayload($payload))->toEqual((object) ['foo' => 'bar']);

        expect(fn () => $this->webhookService->parsePayload('{invalid json'))
            ->toThrow(WebhookVerificationException::class, 'Invalid JSON payload');
    });

    it('retrieves and caches public keys from the collect service', function (): void {
        $originalCollect = app(ChipCollectService::class);
        $collectService = \Mockery::mock(ChipCollectService::class);
        $collectService->shouldReceive('getPublicKey')->once()->andReturn('cached-key');

        app()->instance(ChipCollectService::class, $collectService);

        try {
            expect($this->webhookService->getPublicKey())->toBe('cached-key');
            expect($this->webhookService->getPublicKey())->toBe('cached-key');
        } finally {
            app()->instance(ChipCollectService::class, $originalCollect);
        }
    });

    it('retrieves webhook specific public keys when webhook id provided', function (): void {
        $originalCollect = app(ChipCollectService::class);
        $collectService = \Mockery::mock(ChipCollectService::class);
        $collectService->shouldReceive('getWebhook')
            ->once()
            ->with('wh_123')
            ->andReturn(['public_key' => 'webhook-key']);

        app()->instance(ChipCollectService::class, $collectService);

        try {
            expect($this->webhookService->getPublicKey('wh_123'))->toBe('webhook-key');
        } finally {
            app()->instance(ChipCollectService::class, $originalCollect);
        }
    });

    it('falls back to configured public key when api calls fail', function (): void {
        config(['chip.webhooks.public_key' => 'fallback-key']);

        $originalCollect = app(ChipCollectService::class);
        $collectService = \Mockery::mock(ChipCollectService::class);
        $collectService->shouldReceive('getPublicKey')->andThrow(new \RuntimeException('network error'));

        app()->instance(ChipCollectService::class, $collectService);

        try {
            expect($this->webhookService->getPublicKey())->toBe('fallback-key');
        } finally {
            app()->instance(ChipCollectService::class, $originalCollect);
        }
    });

    it('checks allowed events using configuration', function (): void {
        config(['chip.webhooks.allowed_events' => ['purchase.paid']]);

        expect($this->webhookService->isEventAllowed('purchase.paid'))->toBeTrue();
        expect($this->webhookService->isEventAllowed('purchase.created'))->toBeFalse();

        config(['chip.webhooks.allowed_events' => ['*']]);

        expect($this->webhookService->isEventAllowed('purchase.failed'))->toBeTrue();
    });

    it('exposes webhook configuration array', function (): void {
        expect($this->webhookService->getWebhookConfig())
            ->toBe(config('chip.webhooks'));
    });

    it('indicates webhooks should always be processed', function (): void {
        expect($this->webhookService->shouldProcessWebhook('any-event'))->toBeTrue();
    });
});
