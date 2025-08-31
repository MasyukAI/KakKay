<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MasyukAI\Cart\Cart;

class CartCleared
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Cart $cart
    ) {
        //
    }
}
