<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Data;

class TrackingData
{
    /**
     * @param  array<TrackingDetailData>  $details
     */
    public function __construct(
        public readonly string $trackingNumber,
        public readonly array $details,
        public readonly ?string $orderId = null,
    ) {}

    /**
     * Create from API response array
     */
    public static function fromApiArray(array $data): self
    {
        $details = array_map(
            fn (array $detail) => TrackingDetailData::fromApiArray($detail),
            $data['details'] ?? []
        );

        return new self(
            trackingNumber: $data['billCode'],
            details: $details,
            orderId: $data['txlogisticId'] ?? null,
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
        return [
            'billCode' => $this->trackingNumber,
            'txlogisticId' => $this->orderId,
            'details' => array_map(fn (TrackingDetailData $detail) => $detail->toApiArray(), $this->details),
        ];
    }

    /**
     * @deprecated Use toApiArray() instead
     */
    public function toArray(): array
    {
        return $this->toApiArray();
    }
}
