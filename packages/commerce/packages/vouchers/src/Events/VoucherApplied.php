<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Vouchers\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Vouchers\Data\VoucherData;

/**
 * Fired when a voucher is successfully applied to a cart.
 */
class VoucherApplied
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  Cart  $cart  The cart that the voucher was applied to
     * @param  VoucherData  $voucher  The voucher that was applied
     */
    public function __construct(
        public readonly Cart $cart,
        public readonly VoucherData $voucher
    ) {}
}
