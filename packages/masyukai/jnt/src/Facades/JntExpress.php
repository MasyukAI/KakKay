<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Facades;

use Illuminate\Support\Facades\Facade;
use MasyukAI\Jnt\Builders\OrderBuilder;
use MasyukAI\Jnt\Data\AddressData;
use MasyukAI\Jnt\Data\OrderData;
use MasyukAI\Jnt\Data\PackageInfoData;
use MasyukAI\Jnt\Data\TrackingData;

/**
 * @method static OrderBuilder createOrderBuilder()
 * @method static OrderData createOrder(AddressData $sender, AddressData $receiver, array $items, PackageInfoData $packageInfo, ?string $txlogisticId = null, array $additionalData = [])
 * @method static OrderData createOrderFromArray(array $orderData)
 * @method static array queryOrder(string $txlogisticId)
 * @method static array cancelOrder(string $txlogisticId, string $reason, ?string $billCode = null)
 * @method static array printOrder(string $txlogisticId, ?string $billCode = null, ?string $templateName = null)
 * @method static TrackingData trackParcel(?string $txlogisticId = null, ?string $billCode = null)
 * @method static bool verifyWebhookSignature(string $bizContent, string $digest)
 * @method static array<TrackingData> parseWebhookPayload(array $webhookData)
 *
 * @see \MasyukAI\Jnt\Services\JntExpressService
 */
class JntExpress extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'jnt-express';
    }
}
