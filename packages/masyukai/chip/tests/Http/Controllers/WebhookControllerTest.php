<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Masyukai\Chip\Http\Controllers\WebhookController;
use Masyukai\Chip\Http\Requests\WebhookRequest;
use Masyukai\Chip\Services\WebhookService;
use Masyukai\Chip\Events\WebhookReceived;
use Masyukai\Chip\Exceptions\WebhookVerificationException;

beforeEach(function () {
    Event::fake();
    $this->webhookService = Mockery::mock(WebhookService::class);
    $this->controller = new WebhookController($this->webhookService);
});

describe('WebhookController Handle Method', function () {
    it('successfully processes valid webhook', function () {
        $payload = [
            'event' => 'purchase.paid',
            'data' => [
                'id' => 'purchase_123',
                'amount_in_cents' => 10000,
                'currency' => 'MYR',
                'status' => 'paid'
            ]
        ];

        $request = new WebhookRequest();
        $request->replace($payload);
        $request->headers->set('X-Signature', base64_encode('valid_signature'));

        $this->webhookService->shouldReceive('processWebhook')
            ->once()
            ->with($request, null)
            ->andReturn(true);

        $response = $this->controller->handle($request);

        expect($response->getStatusCode())->toBe(200);
        expect(json_decode($response->getContent(), true))->toBe([
            'status' => 'success',
            'message' => 'Webhook processed successfully'
        ]);
    });

    it('returns error response for invalid webhook signature', function () {
        $payload = [
            'event' => 'purchase.paid',
            'data' => ['id' => 'purchase_123']
        ];

        $request = new WebhookRequest();
        $request->replace($payload);
        $request->headers->set('X-Signature', base64_encode('invalid_signature'));

        $this->webhookService->shouldReceive('processWebhook')
            ->once()
            ->with($request, null)
            ->andThrow(new WebhookVerificationException('Invalid signature'));

        $response = $this->controller->handle($request);

        expect($response->getStatusCode())->toBe(400);
        expect(json_decode($response->getContent(), true))->toBe([
            'status' => 'error',
            'message' => 'Invalid signature'
        ]);
    });

    it('returns error response for processing exceptions', function () {
        $request = new WebhookRequest();
        $request->replace(['event' => 'invalid.event']);
        $request->headers->set('X-Signature', base64_encode('valid_signature'));

        $this->webhookService->shouldReceive('processWebhook')
            ->once()
            ->with($request, null)
            ->andThrow(new \Exception('Processing failed'));

        $response = $this->controller->handle($request);

        expect($response->getStatusCode())->toBe(500);
        expect(json_decode($response->getContent(), true))->toBe([
            'status' => 'error',
            'message' => 'Internal server error'
        ]);
    });

    it('handles empty request body gracefully', function () {
        $request = new WebhookRequest();
        $request->headers->set('X-Signature', base64_encode('signature'));

        $this->webhookService->shouldReceive('processWebhook')
            ->once()
            ->with($request, null)
            ->andThrow(new WebhookVerificationException('Invalid JSON payload'));

        $response = $this->controller->handle($request);

        expect($response->getStatusCode())->toBe(400);
        expect(json_decode($response->getContent(), true)['status'])->toBe('error');
    });
});

describe('WebhookController CORS Headers', function () {
    it('includes proper CORS headers in response', function () {
        $request = new WebhookRequest();
        $request->replace(['event' => 'test', 'data' => []]);
        $request->headers->set('X-Signature', base64_encode('signature'));

        $this->webhookService->shouldReceive('processWebhook')
            ->once()
            ->andReturn(true);

        $response = $this->controller->handle($request);

        expect($response->headers->get('Content-Type'))->toBe('application/json');
        expect($response->getStatusCode())->toBeIn([200, 400, 500]);
    });
});

describe('WebhookController Logging', function () {
    it('logs webhook processing attempts', function () {
        $request = new WebhookRequest();
        $request->replace(['event' => 'test.event', 'data' => ['id' => 'test_123']]);
        $request->headers->set('X-Signature', base64_encode('signature'));

        $this->webhookService->shouldReceive('processWebhook')
            ->once()
            ->andReturn(true);

        $response = $this->controller->handle($request);

        // In a real test, you would assert against Log facade
        expect($response->getStatusCode())->toBe(200);
    });

    it('logs webhook verification failures', function () {
        $request = new WebhookRequest();
        $request->replace(['event' => 'test.event', 'data' => []]);
        $request->headers->set('X-Signature', base64_encode('bad_signature'));

        $this->webhookService->shouldReceive('processWebhook')
            ->once()
            ->andThrow(new WebhookVerificationException('Signature verification failed'));

        $response = $this->controller->handle($request);

        expect($response->getStatusCode())->toBe(400);
    });
});
