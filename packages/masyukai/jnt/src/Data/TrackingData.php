<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Data;

use Deprecated;

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
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromApiArray(array $data): self
    {
        $details = array_map(
            fn (array $detail): TrackingDetailData => TrackingDetailData::fromApiArray($detail),
            $data['details'] ?? []
        );

        return new self(
            trackingNumber: $data['billCode'],
            details: $details,
            orderId: $data['txlogisticId'] ?? null,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    #[Deprecated(message: 'Use fromApiArray() instead')]
    public static function fromArray(array $data): self
    {
        return self::fromApiArray($data);
    }

    /**
     * Convert to API request array
     *
     * @return array<string,mixed>
     */
    public function toApiArray(): array
    {
        return [
            'billCode' => $this->trackingNumber,
            'txlogisticId' => $this->orderId,
            'details' => array_map(fn (TrackingDetailData $detail): array => $detail->toApiArray(), $this->details),
        ];
    }

    #[Deprecated(message: 'Use toApiArray() instead')]
    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return $this->toApiArray();
    }
}
