<?php

declare(strict_types=1);

namespace App\Models;

use AIArmada\Orders\Models\OrderPayment as BasePayment;

/**
 * App Payment model - extends the package OrderPayment model for demo purposes.
 *
 * This alias allows existing code to reference App\Models\Payment
 * while using the full-featured package implementation.
 */
class Payment extends BasePayment
{
    //
}
