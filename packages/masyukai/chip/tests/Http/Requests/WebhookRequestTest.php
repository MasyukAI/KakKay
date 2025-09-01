<?php

use Illuminate\Http\Request;
use Masyukai\Chip\Http\Requests\WebhookRequest;
use Masyukai\Chip\Exceptions\WebhookVerificationException;

describe('WebhookRequest Validation', function () {
    it('validates required event field', function () {
        $request = new WebhookRequest();
        $request->replace(['data' => ['id' => 'test_123']]);

        $validator = validator($request->all(), $request->rules());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('event'))->toBeTrue();
    });

    it('validates required data field', function () {
        $request = new WebhookRequest();
        $request->replace(['event' => 'purchase.paid']);

        $validator = validator($request->all(), $request->rules());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('data'))->toBeTrue();
    });

    it('validates event field is string', function () {
        $request = new WebhookRequest();
        $request->replace([
            'event' => 123,
            'data' => ['id' => 'test_123']
        ]);

        $validator = validator($request->all(), $request->rules());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('event'))->toBeTrue();
    });

    it('validates data field is array', function () {
        $request = new WebhookRequest();
        $request->replace([
            'event' => 'purchase.paid',
            'data' => 'invalid_data'
        ]);

        $validator = validator($request->all(), $request->rules());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('data'))->toBeTrue();
    });

    it('passes validation with valid data', function () {
        $request = new WebhookRequest();
        $request->replace([
            'event' => 'purchase.paid',
            'data' => [
                'id' => 'purchase_123',
                'amount_in_cents' => 10000,
                'currency' => 'MYR',
                'status' => 'paid'
            ],
            'timestamp' => '2024-01-01T12:00:00Z'
        ]);

        $validator = validator($request->all(), $request->rules());

        expect($validator->passes())->toBeTrue();
    });

    it('allows optional timestamp field', function () {
        $request = new WebhookRequest();
        $request->replace([
            'event' => 'purchase.created',
            'data' => ['id' => 'purchase_123'],
            'timestamp' => '2024-01-01T12:00:00Z'
        ]);

        $validator = validator($request->all(), $request->rules());

        expect($validator->passes())->toBeTrue();
    });

    it('validates timestamp format when provided', function () {
        $request = new WebhookRequest();
        $request->replace([
            'event' => 'purchase.created',
            'data' => ['id' => 'purchase_123'],
            'timestamp' => 'invalid_timestamp'
        ]);

        $validator = validator($request->all(), $request->rules());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('timestamp'))->toBeTrue();
    });
});

describe('WebhookRequest Authorization', function () {
    it('always authorizes webhook requests', function () {
        $request = new WebhookRequest();
        
        expect($request->authorize())->toBeTrue();
    });
});

describe('WebhookRequest Custom Methods', function () {
    it('extracts webhook signature from headers', function () {
        $request = new WebhookRequest();
        $request->headers->set('X-Signature', 'test_signature_123');

        expect($request->getSignature())->toBe('test_signature_123');
    });

    it('returns null when signature header is missing', function () {
        $request = new WebhookRequest();

        expect($request->getSignature())->toBeNull();
    });

    it('gets webhook event type', function () {
        $request = new WebhookRequest();
        $request->replace(['event' => 'purchase.paid', 'data' => []]);

        expect($request->getEvent())->toBe('purchase.paid');
    });

    it('gets webhook data payload', function () {
        $data = [
            'id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'status' => 'paid'
        ];

        $request = new WebhookRequest();
        $request->replace(['event' => 'purchase.paid', 'data' => $data]);

        expect($request->getData())->toBe($data);
    });

    it('checks if webhook is for specific event type', function () {
        $request = new WebhookRequest();
        $request->replace(['event' => 'purchase.paid', 'data' => []]);

        expect($request->isEvent('purchase.paid'))->toBeTrue();
        expect($request->isEvent('purchase.created'))->toBeFalse();
    });

    it('checks if webhook is purchase related', function () {
        $purchaseRequest = new WebhookRequest();
        $purchaseRequest->replace(['event' => 'purchase.paid', 'data' => []]);

        $sendRequest = new WebhookRequest();
        $sendRequest->replace(['event' => 'send_instruction.completed', 'data' => []]);

        expect($purchaseRequest->isPurchaseEvent())->toBeTrue();
        expect($sendRequest->isPurchaseEvent())->toBeFalse();
    });

    it('extracts purchase ID from purchase events', function () {
        $request = new WebhookRequest();
        $request->replace([
            'event' => 'purchase.paid',
            'data' => ['id' => 'purchase_123', 'status' => 'paid']
        ]);

        expect($request->getPurchaseId())->toBe('purchase_123');
    });

    it('returns null for purchase ID on non-purchase events', function () {
        $request = new WebhookRequest();
        $request->replace([
            'event' => 'send_instruction.completed',
            'data' => ['id' => 'send_123']
        ]);

        expect($request->getPurchaseId())->toBeNull();
    });
});
