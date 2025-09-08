<?php

declare(strict_types=1);

namespace MasyukAI\Shipping\Facades;

use Illuminate\Support\Facades\Facade;
use MasyukAI\Shipping\Models\Shipment;

/**
 * @method static array getShippingMethods()
 * @method static int calculateCost(array $items, string $method, array $destination = [])
 * @method static array getQuotes(array $items, array $destination = [], ?string $provider = null)
 * @method static array createShipment(Shipment $shipment, ?string $provider = null)
 * @method static array getTrackingInfo(string $trackingNumber, ?string $provider = null)
 * @method static bool canShipTo(array $destination, ?string $provider = null)
 * @method static \MasyukAI\Shipping\Contracts\ShippingProviderInterface driver(?string $driver = null)
 */
class Shipping extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'shipping';
    }
}