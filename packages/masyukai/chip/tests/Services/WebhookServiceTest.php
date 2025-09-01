<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Masyukai\Chip\Events\PurchaseCreated;
use Masyukai\Chip\Events\PurchasePaid;
use Masyukai\Chip\Events\WebhookReceived;
use Masyukai\Chip\Http\Requests\WebhookRequest;
use Masyukai\Chip\Services\WebhookService;
use Masyukai\Chip\Exceptions\WebhookVerificationException;

beforeEach(function () {
    Event::fake();
    $this->webhookService = new WebhookService();
    
    // Sample RSA public key for testing
    $this->publicKey = "-----BEGIN PUBLIC KEY-----
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
-----END PUBLIC KEY-----";
});

describe('WebhookService Signature Verification', function () {
    it('verifies valid webhook signatures', function () {
        $payload = json_encode(['event' => 'purchase.paid', 'data' => ['id' => 'purchase_123']]);
        $signature = base64_encode('valid_signature_bytes');
        
        $request = Request::create('/webhook', 'POST', [], [], [], [], $payload);
        $request->headers->set('X-Signature', $signature);
        
        // Mock the RSA verification
        $this->webhookService = new class extends WebhookService {
            protected function verifyRsaSignature(string $payload, string $signature, string $publicKey): bool
            {
                return true; // Mock successful verification
            }
        };
        
        $result = $this->webhookService->verifySignature($request, $this->publicKey);
        
        expect($result)->toBeTrue();
    });

    it('rejects invalid webhook signatures', function () {
        $payload = json_encode(['event' => 'purchase.paid', 'data' => ['id' => 'purchase_123']]);
        $signature = base64_encode('invalid_signature_bytes');
        
        $request = Request::create('/webhook', 'POST', [], [], [], [], $payload);
        $request->headers->set('X-Signature', $signature);
        
        // Mock the RSA verification to fail
        $this->webhookService = new class extends WebhookService {
            protected function verifyRsaSignature(string $payload, string $signature, string $publicKey): bool
            {
                return false; // Mock failed verification
            }
        };
        
        expect(fn() => $this->webhookService->verifySignature($request, $this->publicKey))
            ->toThrow(WebhookVerificationException::class);
    });

    it('throws exception when signature header is missing', function () {
        $payload = json_encode(['event' => 'purchase.paid', 'data' => ['id' => 'purchase_123']]);
        
        $request = Request::create('/webhook', 'POST', [], [], [], [], $payload);
        // No X-Signature header
        
        expect(fn() => $this->webhookService->verifySignature($request, $this->publicKey))
            ->toThrow(WebhookVerificationException::class, 'Missing signature header');
    });
});

describe('WebhookService Event Processing', function () {
    it('processes purchase.created events', function () {
        $payload = [
            'event' => 'purchase.created',
            'data' => [
                'id' => 'purchase_123',
                'amount_in_cents' => 10000,
                'currency' => 'MYR',
                'status' => 'created',
                'reference' => 'ORDER_001'
            ]
        ];
        
        $request = Request::create('/webhook', 'POST', [], [], [], [], json_encode($payload));
        $request->headers->set('X-Signature', base64_encode('valid_signature'));
        
        // Mock successful verification
        $this->webhookService = new class extends WebhookService {
            protected function verifyRsaSignature(string $payload, string $signature, string $publicKey): bool
            {
                return true;
            }
        };
        
        $this->webhookService->processWebhook($request, $this->publicKey);
        
        Event::assertDispatched(PurchaseCreated::class, function ($event) {
            return $event->purchase->id === 'purchase_123' &&
                   $event->purchase->amountInCents === 10000;
        });
        
        Event::assertDispatched(WebhookReceived::class);
    });

    it('processes purchase.paid events', function () {
        $payload = [
            'event' => 'purchase.paid',
            'data' => [
                'id' => 'purchase_123',
                'amount_in_cents' => 10000,
                'currency' => 'MYR',
                'status' => 'paid',
                'reference' => 'ORDER_001'
            ]
        ];
        
        $request = Request::create('/webhook', 'POST', [], [], [], [], json_encode($payload));
        $request->headers->set('X-Signature', base64_encode('valid_signature'));
        
        // Mock successful verification
        $this->webhookService = new class extends WebhookService {
            protected function verifyRsaSignature(string $payload, string $signature, string $publicKey): bool
            {
                return true;
            }
        };
        
        $this->webhookService->processWebhook($request, $this->publicKey);
        
        Event::assertDispatched(PurchasePaid::class, function ($event) {
            return $event->purchase->id === 'purchase_123' &&
                   $event->purchase->status === 'paid';
        });
        
        Event::assertDispatched(WebhookReceived::class);
    });

    it('handles unknown webhook events gracefully', function () {
        $payload = [
            'event' => 'unknown.event',
            'data' => ['id' => 'test_123']
        ];
        
        $request = Request::create('/webhook', 'POST', [], [], [], [], json_encode($payload));
        $request->headers->set('X-Signature', base64_encode('valid_signature'));
        
        // Mock successful verification
        $this->webhookService = new class extends WebhookService {
            protected function verifyRsaSignature(string $payload, string $signature, string $publicKey): bool
            {
                return true;
            }
        };
        
        $this->webhookService->processWebhook($request, $this->publicKey);
        
        Event::assertDispatched(WebhookReceived::class);
        Event::assertNotDispatched(PurchaseCreated::class);
        Event::assertNotDispatched(PurchasePaid::class);
    });

    it('throws exception for invalid JSON payload', function () {
        $request = Request::create('/webhook', 'POST', [], [], [], [], 'invalid json');
        $request->headers->set('X-Signature', base64_encode('valid_signature'));
        
        // Mock successful verification
        $this->webhookService = new class extends WebhookService {
            protected function verifyRsaSignature(string $payload, string $signature, string $publicKey): bool
            {
                return true;
            }
        };
        
        expect(fn() => $this->webhookService->processWebhook($request, $this->publicKey))
            ->toThrow(WebhookVerificationException::class, 'Invalid JSON payload');
    });
});

describe('WebhookService Configuration', function () {
    it('uses configured public key when none provided', function () {
        config(['chip.webhook.public_key' => $this->publicKey]);
        
        $payload = json_encode(['event' => 'test', 'data' => []]);
        $request = Request::create('/webhook', 'POST', [], [], [], [], $payload);
        $request->headers->set('X-Signature', base64_encode('valid_signature'));
        
        // Mock successful verification
        $this->webhookService = new class extends WebhookService {
            protected function verifyRsaSignature(string $payload, string $signature, string $publicKey): bool
            {
                return $publicKey === config('chip.webhook.public_key');
            }
        };
        
        $result = $this->webhookService->verifySignature($request);
        
        expect($result)->toBeTrue();
    });

    it('throws exception when no public key is available', function () {
        config(['chip.webhook.public_key' => null]);
        
        $payload = json_encode(['event' => 'test', 'data' => []]);
        $request = Request::create('/webhook', 'POST', [], [], [], [], $payload);
        $request->headers->set('X-Signature', base64_encode('valid_signature'));
        
        expect(fn() => $this->webhookService->verifySignature($request))
            ->toThrow(WebhookVerificationException::class, 'No public key configured');
    });
});
