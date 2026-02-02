<?php

declare(strict_types=1);

namespace App\Models;

use AIArmada\Shipping\Models\Shipment as BaseShipment;

/**
 * App Shipment model - extends the package model for demo purposes.
 *
 * This alias allows existing code to reference App\Models\Shipment
 * while using the full-featured package implementation.
 */
final class Shipment extends BaseShipment
{
    //
}
