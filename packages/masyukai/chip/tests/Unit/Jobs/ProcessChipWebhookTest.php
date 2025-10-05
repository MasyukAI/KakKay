<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use MasyukAI\Chip\Events\WebhookReceived;
use MasyukAI\Chip\Jobs\ProcessChipWebhook;

describe('Webhook Queue Handler', function (): void {
    it('dispatches webhook processing job when queue is enabled', function (): void {
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

    it('uses configured queue name', function (): void {
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

    it('has 3 retry attempts configured', function (): void {
        $job = new ProcessChipWebhook(['id' => 'test-id'], 'test-signature');

        expect($job->tries)->toBe(3);
    });

    it('has 60 second timeout configured', function (): void {
        $job = new ProcessChipWebhook(['id' => 'test-id'], 'test-signature');

        expect($job->timeout)->toBe(60);
    });

    it('dispatches WebhookReceived event after processing', function (): void {
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

    it('respects logging toggle for successful processing', function (): void {
        config()->set('chip.logging.log_webhooks', false);

        Log::partialMock()
            ->shouldNotReceive('channel');

        $payload = [
            'id' => 'wh_test_456',
            'event' => 'purchase.created',
            'created_on' => time(),
        ];

        $job = new ProcessChipWebhook($payload, 'sig');
        $job->handle();

        config()->set('chip.logging.log_webhooks', true);
    });

    it('logs errors when webhook processing throws', function (): void {
        Log::partialMock()
            ->shouldReceive('channel')
            ->once()
            ->with(config('chip.logging.channel', 'stack'))
            ->andReturn(tap(Mockery::mock(), function ($mock): void {
                $mock->shouldReceive('error')
                    ->once()
                    ->withArgs(function (string $message, array $context): bool {
                        return str_contains($message, 'CHIP webhook processing failed')
                            && $context['event'] === 'purchase.paid'
                            && array_key_exists('trace', $context);
                    });
            }));

        Event::listen(WebhookReceived::class, function (): void {
            throw new RuntimeException('listener failure');
        });

        $payload = [
            'id' => 'wh_test_error',
            'event' => 'purchase.paid',
            'event_type' => 'purchase.paid',
        ];

        $job = new ProcessChipWebhook($payload, 'sig');
        expect(fn () => $job->handle())->toThrow(RuntimeException::class, 'listener failure');

        Event::forget(WebhookReceived::class);
    });

    it('logs critical information when job fails permanently', function (): void {
        Log::partialMock()
            ->shouldReceive('channel')
            ->once()
            ->with(config('chip.logging.channel', 'stack'))
            ->andReturn(tap(Mockery::mock(), function ($mock): void {
                $mock->shouldReceive('critical')
                    ->once()
                    ->withArgs(function (string $message, array $context): bool {
                        return str_contains($message, 'CHIP webhook processing failed permanently')
                            && $context['event'] === 'purchase.cancelled'
                            && $context['error'] === 'permanent failure'
                            && $context['payload'] === ['event_type' => 'purchase.cancelled'];
                    });
            }));

        $payload = ['event_type' => 'purchase.cancelled'];
        $job = new ProcessChipWebhook($payload, 'sig');

        $job->failed(new RuntimeException('permanent failure'));
    });

});
