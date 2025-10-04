<?php

declare(strict_types=1);

use MasyukAI\Chip\DataObjects\Purchase;
use MasyukAI\Chip\DataObjects\Webhook;

describe('Webhook data object', function () {
    it('creates a webhook from array data', function () {
        $data = [
            'event' => 'purchase.paid',
            'data' => [
                'id' => 'purchase_123',
                'amount_in_cents' => 10000,
                'currency' => 'MYR',
                'status' => 'paid',
            ],
            'timestamp' => '2024-01-01T16:00:00Z',
        ];

        $webhook = Webhook::fromArray($data);

        expect($webhook->event)->toBe('purchase.paid');
        expect($webhook->data)->toBe([
            'id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
            'status' => 'paid',
        ]);
        expect($webhook->timestamp)->toBe('2024-01-01T16:00:00Z');
    });

    it('extracts purchase from webhook data', function () {
        $webhook = Webhook::fromArray([
            'event' => 'purchase.created',
            'data' => [
                'id' => 'purchase_123',
                'amount_in_cents' => 10000,
                'currency' => 'MYR',
                'status' => 'created',
            ],
        ]);

        $purchase = $webhook->getPurchase();

        expect($purchase)->toBeInstanceOf(Purchase::class);
        expect($purchase->id)->toBe('purchase_123');
        expect($purchase->amountInCents)->toBe(10000);
    });

    it('returns null for non-purchase webhook events', function () {
        $webhook = Webhook::fromArray([
            'event' => 'send_instruction.completed',
            'data' => [
                'id' => 'send_123',
                'status' => 'completed',
            ],
        ]);

        expect($webhook->getPurchase())->toBeNull();
    });
});
