<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MasyukAI\Jnt\Data\OrderData;

class OrderCreatedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public readonly OrderData $order) {}

    public function getOrderId(): string
    {
        return $this->order->orderId;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->order->trackingNumber;
    }

    public function hasTrackingNumber(): bool
    {
        return $this->order->trackingNumber !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->order->toApiArray();
    }
}
