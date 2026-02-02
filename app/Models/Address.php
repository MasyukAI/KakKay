<?php

declare(strict_types=1);

namespace App\Models;

use AIArmada\Customers\Models\Address as BaseAddress;

/**
 * App Address model - extends the package model for demo purposes.
 *
 * This alias allows existing code to reference App\Models\Address
 * while using the full-featured package implementation.
 */
final class Address extends BaseAddress
{
    //
}
