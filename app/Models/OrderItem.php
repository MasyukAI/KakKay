<?php

declare(strict_types=1);

namespace App\Models;

use AIArmada\Orders\Models\OrderItem as BaseOrderItem;

/**
 * App OrderItem model - extends the package model for demo purposes.
 *
 * This alias allows existing code to reference App\Models\OrderItem
 * while using the full-featured package implementation.
 */
final class OrderItem extends BaseOrderItem
{
    //
}
