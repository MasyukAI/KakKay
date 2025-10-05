<?php

declare(strict_types=1);

namespace MasyukAI\Chip\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use MasyukAI\Chip\DataObjects\Webhook;
use MasyukAI\Chip\Events\WebhookReceived;

class ProcessChipWebhook implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 3;

    public int $timeout = 60;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly array $payload,
        public readonly string $signature,
    ) {}

    public function handle(): void
    {
        try {
            // Create Webhook object from payload
            $webhook = Webhook::fromArray($this->payload);

            // Fire the webhook received event
            event(new WebhookReceived($webhook));

            if (config('chip.logging.log_webhooks', true)) {
                Log::channel(config('chip.logging.channel', 'stack'))
                    ->info('CHIP webhook processed', [
                        'event' => $this->payload['event_type'] ?? 'unknown',
                        'id' => $this->payload['id'] ?? null,
                    ]);
            }
        } catch (\Throwable $e) {
            Log::channel(config('chip.logging.channel', 'stack'))
                ->error('CHIP webhook processing failed', [
                    'error' => $e->getMessage(),
                    'event' => $this->payload['event_type'] ?? 'unknown',
                    'trace' => $e->getTraceAsString(),
                ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel(config('chip.logging.channel', 'stack'))
            ->critical('CHIP webhook processing failed permanently', [
                'error' => $exception->getMessage(),
                'event' => $this->payload['event_type'] ?? 'unknown',
                'payload' => $this->payload,
            ]);
    }
}
