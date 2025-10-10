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
 * J&T Express Facade
 *
 * Provides a convenient static interface to the J&T Express API service.
 * All methods return typed data objects for type safety and IDE autocompletion.
 *
 * @method static OrderBuilder createOrderBuilder() Create a new order builder instance for fluent order creation
 * @method static OrderData createOrder(AddressData $sender, AddressData $receiver, array<array<string, mixed>> $items, PackageInfoData $packageInfo, ?string $orderId = null, array<string, mixed> $additionalData = []) Create a new order with all required information
 * @method static OrderData createOrderFromArray(array<string, mixed> $orderData) Create an order from a raw array payload
 * @method static array<string, mixed> queryOrder(string $orderId) Query order details by order ID
 * @method static array<string, mixed> cancelOrder(string $orderId, string $reason, ?string $trackingNumber = null) Cancel an existing order with reason
 * @method static array<string, mixed> printOrder(string $orderId, ?string $trackingNumber = null, ?string $templateName = null) Generate waybill/shipping label for an order
 * @method static TrackingData trackParcel(?string $orderId = null, ?string $trackingNumber = null) Track parcel status by order ID or tracking number
 * @method static bool verifyWebhookSignature(string $bizContent, string $digest) Verify webhook signature from J&T Express
 * @method static array<TrackingData> parseWebhookPayload(array<string, mixed> $webhookData) Parse webhook payload into TrackingData objects
 *
 * @throws \MasyukAI\Jnt\Exceptions\JntValidationException When input validation fails (missing required fields, invalid formats, out of range values)
 * @throws \MasyukAI\Jnt\Exceptions\JntApiException When J&T Express API returns an error (authentication failure, order not found, cancellation failure, etc.)
 * @throws \MasyukAI\Jnt\Exceptions\JntNetworkException When network communication fails (timeout, connection error, DNS failure, SSL error)
 * @throws \MasyukAI\Jnt\Exceptions\JntConfigurationException When package configuration is invalid or incomplete (missing API key, invalid environment, etc.)
 *
 * @see \MasyukAI\Jnt\Services\JntExpressService
 * @see OrderBuilder For fluent order creation
 * @see OrderData For order response structure
 * @see TrackingData For tracking response structure
 *
 * @example
 * // Create an order
 * $order = JntExpress::createOrder($sender, $receiver, $items, $packageInfo, 'ORDER123');
 * @example
 * // Track a parcel
 * $tracking = JntExpress::trackParcel(orderId: 'ORDER123');
 * @example
 * // Cancel an order
 * $result = JntExpress::cancelOrder('ORDER123', 'Customer requested cancellation');
 * @example
 * // Generate waybill
 * $waybill = JntExpress::printOrder('ORDER123');
 */
class JntExpress extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'jnt-express';
    }
}
