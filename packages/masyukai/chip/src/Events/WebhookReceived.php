<?php

declare(strict_types=1);

namespace Masyukai\Chip\Events;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Masyukai\Chip\DataObjects\Webhook;

class WebhookReceived implements ShouldQueue
{
    use Dispatchable, SerializesModels;

    /**
     * The name of the queue on which to place the job.
     */
    public string $queue = 'webhooks';

    public function __construct(
        public readonly Webhook $webhook
    ) {}

    /**
     * Check if this webhook is for a specific event type.
     *
     * @param string $eventType
     * @return bool
     */
    public function isEventType(string $eventType): bool
    {
        return $this->webhook->event === $eventType;
    }
}
