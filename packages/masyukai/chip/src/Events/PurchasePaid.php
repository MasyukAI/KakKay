<?php

declare(strict_types=1);

namespace Masyukai\Chip\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Masyukai\Chip\DataObjects\Purchase;

class PurchasePaid implements ShouldBroadcast
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
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
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
                'amount_in_cents' => $this->purchase->amount,
                'currency' => $this->purchase->currency,
                'reference' => $this->purchase->reference,
                'status' => $this->purchase->status,
                'metadata' => $this->purchase->metadata,
            ],
            'event_type' => 'purchase.paid',
            'timestamp' => now()->toISOString(),
        ];
    }
}
