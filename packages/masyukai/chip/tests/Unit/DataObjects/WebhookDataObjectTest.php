<?php

declare(strict_types=1);

use MasyukAI\Chip\DataObjects\Purchase;
use MasyukAI\Chip\DataObjects\Webhook;

describe('Webhook data object', function (): void {
    it('creates a webhook from array data', function (): void {
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

    it('extracts purchase from webhook data', function (): void {
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

    it('returns null for non-purchase webhook events', function (): void {
        $webhook = Webhook::fromArray([
            'event' => 'send_instruction.completed',
            'data' => [
                'id' => 'send_123',
                'status' => 'completed',
            ],
        ]);

        expect($webhook->getPurchase())->toBeNull();
    });

    it('represents webhook configuration entries', function (): void {
        $webhook = Webhook::fromArray([
            'id' => 'wh_123',
            'type' => 'webhook',
            'created_on' => strtotime('2024-01-01T12:00:00Z'),
            'updated_on' => strtotime('2024-01-02T12:00:00Z'),
            'title' => 'Payments',
            'all_events' => false,
            'public_key' => 'pk',
            'events' => ['purchase.paid'],
            'callback' => 'https://example.com/webhook',
        ]);

        expect($webhook->handlesEvent('purchase.paid'))->toBeTrue();
        expect($webhook->handlesEvent('purchase.created'))->toBeFalse();
        expect($webhook->getCreatedAt()->toDateString())->toBe('2024-01-01');
        expect($webhook->toArray())->toMatchArray([
            'id' => 'wh_123',
            'callback' => 'https://example.com/webhook',
        ]);
    });
});
