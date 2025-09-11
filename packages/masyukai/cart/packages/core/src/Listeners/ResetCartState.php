<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Listeners;

use MasyukAI\Cart\Support\CartMoney;

class ResetCartState
{
    /**
     * Handle the event.
     */
    public function handle(): void
    {
        // Reset static state in CartMoney
        CartMoney::reset();
    }
}
