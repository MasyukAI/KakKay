<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Services;

use MasyukAI\Jnt\Builders\OrderBuilder;
use MasyukAI\Jnt\Data\AddressData;
use MasyukAI\Jnt\Data\ItemData;
use MasyukAI\Jnt\Data\OrderData;
use MasyukAI\Jnt\Data\PackageInfoData;
use MasyukAI\Jnt\Data\TrackingData;
use MasyukAI\Jnt\Exceptions\JntException;
use MasyukAI\Jnt\Http\JntClient;

class JntExpressService
{
    protected ?JntClient $client = null;

    public function __construct(
        protected readonly string $customerCode,
        protected readonly string $password,
        protected readonly array $config,
    ) {
        // Client is now lazy-loaded on first use
    }

    /**
     * Option 1: Builder Pattern (for complex orders)
     */
    public function createOrderBuilder(): OrderBuilder
    {
        return new OrderBuilder($this->customerCode, $this->password);
    }

    /**
     * Option 2: Direct method with data objects (type-safe)
     *
     * @param  array<ItemData>  $items
     */
    public function createOrder(
        AddressData $sender,
        AddressData $receiver,
        array $items,
        PackageInfoData $packageInfo,
        ?string $txlogisticId = null,
        array $additionalData = [],
    ): OrderData {
        $orderData = [
            'txlogisticId' => $txlogisticId ?? 'TXN-'.time(),
            'actionType' => 'add',
            'serviceType' => '1',
            'orderType' => '1',
            'customerCode' => $this->customerCode,
            'password' => $this->password,
            'sender' => $sender->toArray(),
            'receiver' => $receiver->toArray(),
            'items' => array_map(fn (ItemData $item) => $item->toArray(), $items),
            'packageInfo' => $packageInfo->toArray(),
            ...$additionalData,
        ];

        return $this->createOrderFromArray($orderData);
    }

    /**
     * Option 3: Array passthrough (quick prototyping, less type safety)
     */
    public function createOrderFromArray(array $orderData): OrderData
    {
        $response = $this->getClient()->post('/api/order/addOrder', $orderData);

        return OrderData::fromArray($response['data']);
    }

    public function queryOrder(string $txlogisticId): array
    {
        $response = $this->getClient()->post('/api/order/getOrders', [
            'customerCode' => $this->customerCode,
            'password' => $this->password,
            'txlogisticId' => $txlogisticId,
        ]);

        return $response['data'];
    }

    public function cancelOrder(string $txlogisticId, string $reason, ?string $billCode = null): array
    {
        $payload = [
            'customerCode' => $this->customerCode,
            'password' => $this->password,
            'txlogisticId' => $txlogisticId,
            'reason' => $reason,
        ];

        if ($billCode !== null) {
            $payload['billCode'] = $billCode;
        }

        $response = $this->getClient()->post('/api/order/cancelOrder', $payload);

        return $response['data'];
    }

    public function printOrder(string $txlogisticId, ?string $billCode = null, ?string $templateName = null): array
    {
        $payload = [
            'customerCode' => $this->customerCode,
            'password' => $this->password,
            'txlogisticId' => $txlogisticId,
        ];

        if ($billCode !== null) {
            $payload['billCode'] = $billCode;
        }

        if ($templateName !== null) {
            $payload['templateName'] = $templateName;
        }

        $response = $this->getClient()->post('/api/order/printOrder', $payload);

        return $response['data'];
    }

    public function trackParcel(?string $txlogisticId = null, ?string $billCode = null): TrackingData
    {
        if ($txlogisticId === null && $billCode === null) {
            throw JntException::invalidConfiguration('Either txlogisticId or billCode is required');
        }

        $payload = [
            'customerCode' => $this->customerCode,
            'password' => $this->password,
        ];

        if ($txlogisticId !== null) {
            $payload['txlogisticId'] = $txlogisticId;
        }

        if ($billCode !== null) {
            $payload['billCode'] = $billCode;
        }

        $response = $this->getClient()->post('/api/logistics/trace', $payload);

        return TrackingData::fromArray($response['data']);
    }

    public function verifyWebhookSignature(string $bizContent, string $digest): bool
    {
        if (! ($this->config['webhook']['verify_signature'] ?? true)) {
            return true;
        }

        return $this->getClient()->verifyWebhookSignature($bizContent, $digest);
    }

    /**
     * @return array<TrackingData>
     */
    public function parseWebhookPayload(array $webhookData): array
    {
        if (! isset($webhookData['bizContent'])) {
            throw JntException::invalidConfiguration('Missing bizContent in webhook payload');
        }

        $bizContent = is_string($webhookData['bizContent'])
            ? json_decode($webhookData['bizContent'], true)
            : $webhookData['bizContent'];

        if (! is_array($bizContent)) {
            throw JntException::invalidConfiguration('Invalid bizContent format');
        }

        return array_map(
            fn (array $item) => TrackingData::fromArray($item),
            $bizContent
        );
    }

    /**
     * Lazy load the HTTP client only when needed
     */
    protected function getClient(): JntClient
    {
        if ($this->client === null) {
            $baseUrl = $this->getBaseUrl();
            $apiAccount = $this->config['api_account'] ?? throw JntException::missingCredentials('api_account');
            $privateKey = $this->config['private_key'] ?? throw JntException::missingCredentials('private_key');

            $this->client = new JntClient($baseUrl, $apiAccount, $privateKey, $this->config);
        }

        return $this->client;
    }

    protected function getBaseUrl(): string
    {
        $environment = $this->config['environment'] ?? 'testing';
        $baseUrls = $this->config['base_urls'] ?? [];

        return $baseUrls[$environment] ?? throw JntException::invalidConfiguration("Invalid environment: {$environment}");
    }
}
