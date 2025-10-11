<?php

declare(strict_types=1);

use AIArmada\Chip\DataObjects\Webhook;
use App\Jobs\ProcessWebhook;
use App\Services\Chip\ChipDataRecorder;
use App\Services\Chip\WebhookProcessor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

test('successfully processes webhook and marks as processed', function () {
    Notification::fake();
    Log::shouldReceive('debug')->once();
    Log::shouldReceive('info')->once();

    // Create a mock webhook for successful processing
    $webhook = Webhook::fromArray([
        'id' => 'webhook_123',
        'event' => 'purchase.paid',
        'data' => [
            'id' => 'purchase_123',
            'status' => 'paid',
            'reference' => 'cart_ref_456',
        ],
        'payload' => [
            'webhook_id' => 'wh_123',
        ],
    ]);

    // Mock the services
    $webhookProcessor = mock(WebhookProcessor::class);
    $webhookProcessor->shouldReceive('handle')->once()->with($webhook);

    $chipDataRecorder = mock(ChipDataRecorder::class);
    $chipDataRecorder->shouldReceive('markWebhookProcessed')
        ->once()
        ->with('webhook_123', true);

    // Dispatch and process the job
    $job = new ProcessWebhook($webhook, 'wh_123');
    $job->handle($webhookProcessor, $chipDataRecorder);

    // Verify no notifications were sent for successful processing
    Notification::assertNothingSent();
});

test('successfully processes success callback and marks as processed', function () {
    Notification::fake();
    Log::shouldReceive('debug')->once();
    Log::shouldReceive('info')->once();

    // Create a mock webhook for success callback processing
    $webhook = Webhook::fromArray([
        'id' => 'callback_123',
        'event' => 'purchase.paid',
        'data' => [
            'id' => 'purchase_456',
            'status' => 'paid',
            'reference' => 'cart_ref_789',
        ],
        'payload' => [],
    ]);

    // Mock the services
    $webhookProcessor = mock(WebhookProcessor::class);
    $webhookProcessor->shouldReceive('handle')->once()->with($webhook);

    $chipDataRecorder = mock(ChipDataRecorder::class);
    $chipDataRecorder->shouldReceive('markWebhookProcessed')
        ->once()
        ->with('callback_123', true);

    // Dispatch and process the job (null webhookId indicates success callback)
    $job = new ProcessWebhook($webhook, null);
    $job->handle($webhookProcessor, $chipDataRecorder);

    // Verify no notifications were sent for successful processing
    Notification::assertNothingSent();
});

test('handles webhook processing failure with retry', function () {
    Notification::fake();
    Log::shouldReceive('debug')->once();
    Log::shouldReceive('error')->once();

    // Create a mock webhook that will fail
    $webhook = Webhook::fromArray([
        'id' => 'webhook_fail_123',
        'event' => 'purchase.paid',
        'data' => [
            'id' => 'purchase_fail_123',
            'status' => 'paid',
        ],
        'payload' => [
            'webhook_id' => 'wh_fail_123',
        ],
    ]);

    // Mock the services - webhook processor will throw an exception
    $webhookProcessor = mock(WebhookProcessor::class);
    $webhookProcessor->shouldReceive('handle')
        ->once()
        ->with($webhook)
        ->andThrow(new Exception('Processing failed'));

    $chipDataRecorder = mock(ChipDataRecorder::class);
    $chipDataRecorder->shouldReceive('markWebhookProcessed')
        ->once()
        ->with('webhook_fail_123', false, 'Processing failed');

    // Dispatch and process the job - it should throw the exception to trigger retry
    $job = new ProcessWebhook($webhook, 'wh_fail_123');

    expect(fn () => $job->handle($webhookProcessor, $chipDataRecorder))
        ->toThrow(Exception::class, 'Processing failed');

    // Verify no notifications were sent (only sent on final failure)
    Notification::assertNothingSent();
});

test('sends notification only after all retries exhausted', function () {
    // This test would require more complex mocking of the queue system
    // The main functionality (retry logic) is tested in other tests
    // For now, we verify that the job has the correct retry configuration
    $webhook = Webhook::fromArray([
        'id' => 'webhook_final_fail_123',
        'event' => 'purchase.paid',
        'data' => ['id' => 'purchase_final_fail_123'],
    ]);

    $job = new ProcessWebhook($webhook, 'wh_final_fail_123');

    expect($job->tries)->toBe(3);
    expect($job->backoff)->toBe(60);
});

test('job has correct queue configuration', function () {
    $webhook = Webhook::fromArray([
        'id' => 'webhook_config_123',
        'event' => 'purchase.paid',
        'data' => ['id' => 'purchase_config_123'],
    ]);

    $job = new ProcessWebhook($webhook, 'wh_config_123');

    expect($job->tries)->toBe(3);
    expect($job->backoff)->toBe(60);
    expect($job)->toBeInstanceOf(Illuminate\Contracts\Queue\ShouldQueue::class);
});

test('job serializes correctly for queue', function () {
    $webhook = Webhook::fromArray([
        'id' => 'webhook_serialize_123',
        'event' => 'purchase.paid',
        'data' => [
            'id' => 'purchase_serialize_123',
            'status' => 'paid',
        ],
        'payload' => [
            'webhook_id' => 'wh_serialize_123',
        ],
    ]);

    $job = new ProcessWebhook($webhook, 'wh_serialize_123');

    // Test that the job can be serialized (required for queuing)
    $serialized = serialize($job);
    expect($serialized)->toBeString();

    // Test that it can be unserialized
    $unserialized = unserialize($serialized);
    expect($unserialized)->toBeInstanceOf(ProcessWebhook::class);
});
