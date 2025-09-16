<?php

use Illuminate\Support\Facades\Event;
use MasyukAI\Chip\DataObjects\Purchase;
use MasyukAI\Chip\DataObjects\Webhook;
use MasyukAI\Chip\Events\PurchaseCreated;
use MasyukAI\Chip\Events\PurchasePaid;
use MasyukAI\Chip\Events\WebhookReceived;

beforeEach(function () {
    Event::fake();
});

describe('PurchaseCreated Event', function () {
    it('creates event with purchase data', function () {
        $purchaseData = [
            'id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
            'reference' => 'ORDER_001',
            'status' => 'created',
        ];

        $purchase = Purchase::fromArray($purchaseData);
        $event = new PurchaseCreated($purchase);

        expect($event->purchase)->toBe($purchase);
        expect($event->purchase->id)->toBe('purchase_123');
        expect($event->purchase->status)->toBe('created');
    });

    it('implements ShouldBroadcast interface', function () {
        $purchase = Purchase::fromArray([
            'id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
            'status' => 'created',
        ]);

        $event = new PurchaseCreated($purchase);

        expect($event)->toBeInstanceOf(\Illuminate\Contracts\Broadcasting\ShouldBroadcast::class);
    });

    it('broadcasts on purchase channel', function () {
        $purchase = Purchase::fromArray([
            'id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
            'status' => 'created',
        ]);

        $event = new PurchaseCreated($purchase);
        $channels = $event->broadcastOn();

        expect($channels)->toHaveCount(1);
        expect($channels[0]->name)->toBe('purchase.purchase_123');
    });

    it('broadcasts with correct data', function () {
        $purchase = Purchase::fromArray([
            'id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
            'status' => 'created',
        ]);

        $event = new PurchaseCreated($purchase);
        $broadcastData = $event->broadcastWith();

        expect($broadcastData)->toHaveKey('purchase');
        expect($broadcastData['purchase']['id'])->toBe('purchase_123');
        expect($broadcastData['purchase']['status'])->toBe('created');
    });
});

describe('PurchasePaid Event', function () {
    it('creates event with paid purchase data', function () {
        $purchaseData = [
            'id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
            'reference' => 'ORDER_001',
            'status' => 'paid',
        ];

        $purchase = Purchase::fromArray($purchaseData);
        $event = new PurchasePaid($purchase);

        expect($event->purchase)->toBe($purchase);
        expect($event->purchase->id)->toBe('purchase_123');
        expect($event->purchase->status)->toBe('paid');
    });

    it('implements ShouldBroadcast interface', function () {
        $purchase = Purchase::fromArray([
            'id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
            'status' => 'paid',
        ]);

        $event = new PurchasePaid($purchase);

        expect($event)->toBeInstanceOf(\Illuminate\Contracts\Broadcasting\ShouldBroadcast::class);
    });

    it('broadcasts on purchase channel', function () {
        $purchase = Purchase::fromArray([
            'id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
            'status' => 'paid',
        ]);

        $event = new PurchasePaid($purchase);
        $channels = $event->broadcastOn();

        expect($channels)->toHaveCount(1);
        expect($channels[0]->name)->toBe('purchase.purchase_123');
    });

    it('includes payment timestamp in broadcast data', function () {
        $purchase = Purchase::fromArray([
            'id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
            'status' => 'paid',
        ]);

        $event = new PurchasePaid($purchase);
        $broadcastData = $event->broadcastWith();

        expect($broadcastData)->toHaveKey('purchase');
        expect($broadcastData)->toHaveKey('timestamp');
        expect($broadcastData['purchase']['status'])->toBe('paid');
    });
});

describe('WebhookReceived Event', function () {
    it('creates event with webhook data', function () {
        $webhookData = [
            'event' => 'purchase.paid',
            'data' => [
                'id' => 'purchase_123',
                'amount_in_cents' => 10000,
                'status' => 'paid',
            ],
            'timestamp' => '2024-01-01T12:00:00Z',
        ];

        $webhook = Webhook::fromArray($webhookData);
        $event = new WebhookReceived($webhook);

        expect($event->webhook)->toBe($webhook);
        expect($event->webhook->event)->toBe('purchase.paid');
    });

    it('implements ShouldQueue interface for background processing', function () {
        $webhook = Webhook::fromArray([
            'event' => 'purchase.paid',
            'data' => ['id' => 'purchase_123'],
            'timestamp' => '2024-01-01T12:00:00Z',
        ]);

        $event = new WebhookReceived($webhook);

        expect($event)->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
    });

    it('stores raw webhook payload', function () {
        $webhookData = [
            'event' => 'purchase.paid',
            'data' => [
                'id' => 'purchase_123',
                'amount_in_cents' => 10000,
                'status' => 'paid',
            ],
        ];

        $webhook = Webhook::fromArray($webhookData);
        $event = new WebhookReceived($webhook);

        expect($event->webhook->data)->toBe($webhookData['data']);
        expect($event->webhook->event)->toBe('purchase.paid');
    });

    it('can determine webhook event type', function () {
        $webhook = Webhook::fromArray([
            'event' => 'send_instruction.completed',
            'data' => ['id' => 'send_123'],
        ]);

        $event = new WebhookReceived($webhook);

        expect($event->webhook->event)->toBe('send_instruction.completed');
        expect($event->isEventType('send_instruction.completed'))->toBeTrue();
        expect($event->isEventType('purchase.paid'))->toBeFalse();
    });
});

describe('Event Broadcasting Configuration', function () {
    it('uses correct queue for background events', function () {
        $webhook = Webhook::fromArray([
            'event' => 'test.event',
            'data' => ['id' => 'test_123'],
        ]);

        $event = new WebhookReceived($webhook);

        expect($event->queue)->toBe('webhooks');
    });

    it('sets appropriate broadcast queue for real-time events', function () {
        $purchase = Purchase::fromArray([
            'id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
            'status' => 'paid',
        ]);

        $event = new PurchasePaid($purchase);

        expect($event->broadcastQueue)->toBe('broadcast');
    });
});

describe('Event Data Serialization', function () {
    it('serializes purchase data correctly for broadcasting', function () {
        $purchase = Purchase::fromArray([
            'id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
            'reference' => 'ORDER_001',
            'status' => 'created',
            'metadata' => ['order_id' => '456'],
        ]);

        $event = new PurchaseCreated($purchase);
        $broadcastData = $event->broadcastWith();

        expect($broadcastData['purchase'])->toHaveKeys([
            'id', 'amount_in_cents', 'currency', 'reference', 'status', 'metadata',
        ]);
        expect($broadcastData['purchase']['id'])->toBe('purchase_123');
        expect($broadcastData['purchase']['metadata'])->toBe(['order_id' => '456']);
    });

    it('includes event metadata in broadcast payload', function () {
        $purchase = Purchase::fromArray([
            'id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
            'status' => 'paid',
        ]);

        $event = new PurchasePaid($purchase);
        $broadcastData = $event->broadcastWith();

        expect($broadcastData)->toHaveKey('event_type');
        expect($broadcastData['event_type'])->toBe('purchase.paid');
        expect($broadcastData)->toHaveKey('timestamp');
    });
});
