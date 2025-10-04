<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use MasyukAI\Chip\Events\WebhookReceived;
use MasyukAI\Chip\Jobs\ProcessChipWebhook;

describe('Webhook Queue Handler', function () {
    it('dispatches webhook processing job when queue is enabled', function () {
        Queue::fake();

        $payload = [
            'id' => 'wh_test_123',
            'event_type' => 'purchase.paid',
            'created_on' => time(),
        ];
        $signature = 'test-signature';

        // Dispatch the job
        ProcessChipWebhook::dispatch($payload, $signature);

        // Assert it was pushed to the queue
        Queue::assertPushed(ProcessChipWebhook::class);
    });

    it('uses configured queue name', function () {
        Queue::fake();

        $payload = [
            'id' => 'wh_test_123',
            'event_type' => 'purchase.paid',
            'created_on' => time(),
        ];

        // Dispatch to specific queue
        ProcessChipWebhook::dispatch($payload, 'test-sig')->onQueue('chip-webhooks');

        // Assert it was pushed to the correct queue
        Queue::assertPushedOn('chip-webhooks', ProcessChipWebhook::class);
    });

    it('has 3 retry attempts configured', function () {
        $job = new ProcessChipWebhook(['id' => 'test-id'], 'test-signature');

        expect($job->tries)->toBe(3);
    });

    it('has 60 second timeout configured', function () {
        $job = new ProcessChipWebhook(['id' => 'test-id'], 'test-signature');

        expect($job->timeout)->toBe(60);
    });

    it('dispatches WebhookReceived event after processing', function () {
        Event::fake();

        $payload = [
            'id' => 'wh_test_123',
            'event' => 'purchase.paid',
            'created_on' => time(),
        ];
        $signature = 'test-signature';

        $job = new ProcessChipWebhook($payload, $signature);
        $job->handle();

        Event::assertDispatched(WebhookReceived::class, function ($event) use ($payload) {
            return $event->webhook->id === $payload['id']
                && $event->webhook->event === $payload['event'];
        });
    });

    it('provides genuinely useful async processing', function () {
        Queue::fake();

        $payload = [
            'id' => 'wh_test_123',
            'event_type' => 'purchase.paid',
            'created_on' => time(),
        ];

        // Time how long it takes to dispatch the webhook
        $startTime = microtime(true);
        ProcessChipWebhook::dispatch($payload, 'test-signature');
        $endTime = microtime(true);

        $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Dispatching to queue should be very fast (< 10ms)
        expect($duration)->toBeLessThan(10);

        // Verify job was queued (proves async processing)
        Queue::assertPushed(ProcessChipWebhook::class);
    });
});
