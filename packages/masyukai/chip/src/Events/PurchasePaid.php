<?php

declare(strict_types=1);

namespace MasyukAI\Chip\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MasyukAI\Chip\DataObjects\Purchase;

final class PurchasePaid implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The name of the queue on which to place the broadcasting job.
     */
    public string $broadcastQueue = 'broadcast';

    public function __construct(
        public readonly Purchase $purchase
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("purchase.{$this->purchase->id}"),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'purchase' => [
                'id' => $this->purchase->id,
                'amount_in_cents' => $this->purchase->purchase->total,
                'currency' => $this->purchase->purchase->currency,
                'reference' => $this->purchase->reference,
                'status' => $this->purchase->status,
                'metadata' => $this->purchase->purchase->metadata,
            ],
            'event_type' => 'purchase.paid',
            'timestamp' => now()->toISOString(),
        ];
    }
}
