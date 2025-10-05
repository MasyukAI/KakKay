<?php

use MasyukAI\Chip\Http\Requests\WebhookRequest;
use MasyukAI\Chip\Services\WebhookService;

describe('WebhookRequest Validation', function (): void {
    it('validates required event field', function (): void {
        $request = new WebhookRequest;
        $request->replace(['data' => ['id' => 'test_123']]);

        $validator = validator($request->all(), $request->rules());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('event'))->toBeTrue();
    });

    it('validates required data field', function (): void {
        $request = new WebhookRequest;
        $request->replace(['event' => 'purchase.paid']);

        $validator = validator($request->all(), $request->rules());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('data'))->toBeTrue();
    });

    it('validates event field is string', function (): void {
        $request = new WebhookRequest;
        $request->replace([
            'event' => 123,
            'data' => ['id' => 'test_123'],
        ]);

        $validator = validator($request->all(), $request->rules());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('event'))->toBeTrue();
    });

    it('validates data field is array', function (): void {
        $request = new WebhookRequest;
        $request->replace([
            'event' => 'purchase.paid',
            'data' => 'invalid_data',
        ]);

        $validator = validator($request->all(), $request->rules());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('data'))->toBeTrue();
    });

    it('passes validation with valid data', function (): void {
        $request = new WebhookRequest;
        $request->replace([
            'event' => 'purchase.paid',
            'data' => [
                'id' => 'purchase_123',
                'amount_in_cents' => 10000,
                'currency' => 'MYR',
                'status' => 'paid',
            ],
            'timestamp' => '2024-01-01T12:00:00Z',
        ]);

        $validator = validator($request->all(), $request->rules());

        expect($validator->fails())->toBeFalse();
    });

    it('allows optional timestamp field', function (): void {
        $request = new WebhookRequest;
        $request->replace([
            'event' => 'purchase.created',
            'data' => ['id' => 'purchase_123'],
            'timestamp' => '2024-01-01T12:00:00Z',
        ]);

        $validator = validator($request->all(), $request->rules());

        expect($validator->fails())->toBeFalse();
    });

    it('validates timestamp format when provided', function (): void {
        $request = new WebhookRequest;
        $request->replace([
            'event' => 'purchase.created',
            'data' => ['id' => 'purchase_123'],
            'timestamp' => 'invalid_timestamp',
        ]);

        $validator = validator($request->all(), $request->rules());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('timestamp'))->toBeTrue();
    });
});

describe('WebhookRequest Authorization', function (): void {
    beforeEach(function (): void {
        config()->set('chip.webhooks.verify_signature', true);
    });

    it('authorizes when signature verification is disabled', function (): void {
        config()->set('chip.webhooks.verify_signature', false);

        $request = new WebhookRequest;

        expect($request->authorize())->toBeTrue();
    });

    it('rejects when signature header is missing', function (): void {
        $request = WebhookRequest::create('/', 'POST', [], [], [], [], json_encode(['event' => 'purchase.paid']));

        expect($request->authorize())->toBeFalse();
    });

    it('verifies the signature using the webhook service', function (): void {
        $payload = json_encode(['event' => 'purchase.paid', 'data' => ['id' => 'purchase_123']]);

        $service = \Mockery::mock(WebhookService::class);
        $service->shouldReceive('getPublicKey')
            ->once()
            ->with('wh_123')
            ->andReturn('public-key');
        $service->shouldReceive('verifySignature')
            ->once()
            ->with($payload, 'signature-123', 'public-key')
            ->andReturnTrue();

        app()->instance(WebhookService::class, $service);

        $request = WebhookRequest::create('/', 'POST', [], [], [], [], $payload);
        $request->headers->set('X-Signature', 'signature-123');
        $request->setRouteResolver(fn () => new class
        {
            public function parameter(string $key, $default = null)
            {
                return $key === 'webhook_id' ? 'wh_123' : $default;
            }
        });

        expect($request->authorize())->toBeTrue();
    });

    it('fails authorization when webhook service throws', function (): void {
        $service = \Mockery::mock(WebhookService::class);
        $service->shouldReceive('getPublicKey')->andThrow(new Exception('Service unavailable'));

        app()->instance(WebhookService::class, $service);

        $request = WebhookRequest::create('/', 'POST', [], [], [], [], json_encode(['event' => 'purchase.paid']));
        $request->headers->set('X-Signature', 'signature-123');

        expect($request->authorize())->toBeFalse();
    });
});

describe('WebhookRequest Custom Methods', function (): void {
    it('extracts webhook signature from headers', function (): void {
        $request = new WebhookRequest;
        $request->headers->set('X-Signature', 'test_signature_123');

        expect($request->getSignature())->toBe('test_signature_123');
    });

    it('returns null when signature header is missing', function (): void {
        $request = new WebhookRequest;

        expect($request->getSignature())->toBeNull();
    });

    it('gets webhook event type', function (): void {
        $request = new WebhookRequest;
        $request->replace(['event' => 'purchase.paid', 'data' => []]);

        expect($request->getEvent())->toBe('purchase.paid');
    });

    it('gets webhook data payload', function (): void {
        $data = [
            'id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'status' => 'paid',
        ];

        $request = new WebhookRequest;
        $request->replace(['event' => 'purchase.paid', 'data' => $data]);

        expect($request->getData())->toBe($data);
    });

    it('checks if webhook is for specific event type', function (): void {
        $request = new WebhookRequest;
        $request->replace(['event' => 'purchase.paid', 'data' => []]);

        expect($request->isEvent('purchase.paid'))->toBeTrue();
        expect($request->isEvent('purchase.created'))->toBeFalse();
    });

    it('checks if webhook is purchase related', function (): void {
        $purchaseRequest = new WebhookRequest;
        $purchaseRequest->replace(['event' => 'purchase.paid', 'data' => []]);

        $sendRequest = new WebhookRequest;
        $sendRequest->replace(['event' => 'send_instruction.completed', 'data' => []]);

        expect($purchaseRequest->isPurchaseEvent())->toBeTrue();
        expect($sendRequest->isPurchaseEvent())->toBeFalse();
    });

    it('extracts purchase ID from purchase events', function (): void {
        $request = new WebhookRequest;
        $request->replace([
            'event' => 'purchase.paid',
            'data' => ['id' => 'purchase_123', 'status' => 'paid'],
        ]);

        expect($request->getPurchaseId())->toBe('purchase_123');
    });

    it('returns null for purchase ID on non-purchase events', function (): void {
        $request = new WebhookRequest;
        $request->replace([
            'event' => 'send_instruction.completed',
            'data' => ['id' => 'send_123'],
        ]);

        expect($request->getPurchaseId())->toBeNull();
    });

    it('returns request headers as array', function (): void {
        $request = new WebhookRequest;
        $request->headers->set('X-Test', 'value');

        expect($request->getHeaders())->toHaveKey('x-test');
        expect($request->getHeaders()['x-test'])->toBe(['value']);
    });

    it('parses webhook payload once and caches the object', function (): void {
        $payload = json_encode(['event_type' => 'purchase.paid']);

        $service = \Mockery::mock(WebhookService::class);
        $service->shouldReceive('parsePayload')
            ->once()
            ->with($payload)
            ->andReturn((object) ['event_type' => 'purchase.paid']);

        app()->instance(WebhookService::class, $service);

        $request = WebhookRequest::create('/', 'POST', [], [], [], [], $payload);

        $first = $request->getWebhookPayload();
        $second = $request->getWebhookPayload();

        expect($first)->toBe($second);
        expect($request->getEventType())->toBe('purchase.paid');
    });
});
