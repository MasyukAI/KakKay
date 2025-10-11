<?php

declare(strict_types=1);

namespace App\Jobs;

use AIArmada\Chip\DataObjects\Webhook;
use App\Notifications\WebhookProcessingFailed;
use App\Services\Chip\ChipDataRecorder;
use App\Services\Chip\WebhookProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

final class ProcessWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60; // 1 minute, then exponential backoff

    public function __construct(
        private readonly Webhook $webhook,
        private readonly ?string $webhookId,
    ) {}

    public function handle(
        WebhookProcessor $webhookProcessor,
        ChipDataRecorder $chipDataRecorder,
    ): void {
        $requestType = $this->webhookId !== null ? 'webhook' : 'success_callback';

        Log::debug('Processing CHIP webhook in queue', [
            'type' => $requestType,
            'webhook_id' => $this->webhookId,
            'event_type' => $this->webhook->event ?? $this->webhook->event_type,
            'purchase_id' => $this->webhook->data['id'] ?? null,
            'attempt' => $this->attempts(),
        ]);

        try {
            $webhookProcessor->handle($this->webhook);
            $chipDataRecorder->markWebhookProcessed($this->webhook->id, true);

            Log::info('CHIP webhook processed successfully in queue', [
                'type' => $requestType,
                'webhook_id' => $this->webhookId,
                'event_type' => $this->webhook->event ?? $this->webhook->event_type,
                'purchase_id' => $this->webhook->data['id'] ?? null,
            ]);
        } catch (Throwable $throwable) {
            $chipDataRecorder->markWebhookProcessed($this->webhook->id, false, $throwable->getMessage());

            Log::error('CHIP webhook processing failed in queue', [
                'type' => $requestType,
                'webhook_id' => $this->webhookId,
                'event_type' => $this->webhook->event ?? $this->webhook->event_type,
                'purchase_id' => $this->webhook->data['id'] ?? null,
                'attempt' => $this->attempts(),
                'error' => $throwable->getMessage(),
                'error_class' => get_class($throwable),
            ]);

            // Only send notification on final failure (after all retries)
            if ($this->attempts() >= $this->tries) {
                Notification::route('mail', config('mail.from.address'))
                    ->notify(new WebhookProcessingFailed(
                        $this->webhook->event ?? $this->webhook->event_type ?? 'unknown',
                        $throwable->getMessage(),
                        $this->webhook->data['id'] ?? null,
                        $this->webhook->payload ?? []
                    ));
            }

            throw $throwable; // Re-throw to trigger retry or mark as failed
        }
    }

    public function failed(Throwable $throwable): void
    {
        $requestType = $this->webhookId !== null ? 'webhook' : 'success_callback';

        Log::critical('CHIP webhook processing failed permanently after all retries', [
            'type' => $requestType,
            'webhook_id' => $this->webhookId,
            'event_type' => $this->webhook->event ?? $this->webhook->event_type,
            'purchase_id' => $this->webhook->data['id'] ?? null,
            'attempts' => $this->attempts(),
            'error' => $throwable->getMessage(),
            'error_class' => get_class($throwable),
        ]);
    }
}
