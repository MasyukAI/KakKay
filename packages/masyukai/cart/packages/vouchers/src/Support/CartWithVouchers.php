<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Vouchers\Support;

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Vouchers\Traits\HasVouchers;

/**
 * CartWithVouchers extends the base Cart class to provide voucher functionality.
 *
 * This class is provided for convenience but is NOT automatically bound.
 * Applications should manually bind this in their AppServiceProvider if desired.
 *
 * Example:
 * ```php
 * // In AppServiceProvider::register()
 * $this->app->bind(\MasyukAI\Cart\Cart::class, \MasyukAI\Cart\Vouchers\Support\CartWithVouchers::class);
 * ```
 */
class CartWithVouchers extends Cart
{
    use HasVouchers;
}
