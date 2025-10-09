<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Data;

class OrderData
{
    /**
     * @param  array<string>|null  $additionalTrackingNumbers
     */
    public function __construct(
        public readonly string $orderId,
        public readonly ?string $trackingNumber = null,
        public readonly ?string $sortingCode = null,
        public readonly ?string $thirdSortingCode = null,
        public readonly ?array $additionalTrackingNumbers = null,
        public readonly ?string $chargeableWeight = null,
    ) {}

    /**
     * Create from API response array
     */
    public static function fromApiArray(array $data): self
    {
        return new self(
            orderId: $data['txlogisticId'],
            trackingNumber: $data['billCode'] ?? null,
            sortingCode: $data['sortingCode'] ?? null,
            thirdSortingCode: $data['thirdSortingCode'] ?? null,
            additionalTrackingNumbers: $data['multipleVoteBillCodes'] ?? null,
            chargeableWeight: $data['packageChargeWeight'] ?? null,
        );
    }

    /**
     * @deprecated Use fromApiArray() instead
     */
    public static function fromArray(array $data): self
    {
        return self::fromApiArray($data);
    }

    /**
     * Convert to API request array
     */
    public function toApiArray(): array
    {
        return array_filter([
            'txlogisticId' => $this->orderId,
            'billCode' => $this->trackingNumber,
            'sortingCode' => $this->sortingCode,
            'thirdSortingCode' => $this->thirdSortingCode,
            'multipleVoteBillCodes' => $this->additionalTrackingNumbers,
            'packageChargeWeight' => $this->chargeableWeight,
        ], fn ($value) => $value !== null);
    }

    /**
     * @deprecated Use toApiArray() instead
     */
    public function toArray(): array
    {
        return $this->toApiArray();
    }
}
