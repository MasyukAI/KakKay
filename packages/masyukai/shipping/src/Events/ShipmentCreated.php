<?php

declare(strict_types=1);

namespace MasyukAI\Shipping\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MasyukAI\Shipping\Models\Shipment;

class ShipmentCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Shipment $shipment
    ) {}
}