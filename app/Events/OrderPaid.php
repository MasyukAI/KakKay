<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class OrderPaid
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  array<string, mixed>  $webhookData
     */
    public function __construct(
        public readonly Order $order,
        public readonly Payment $payment,
        public readonly array $webhookData,
        public readonly string $source,
    ) {}

    /**
     * Get the event name for logging purposes.
     */
    public function getEventName(): string
    {
        return 'OrderPaid';
    }
}
