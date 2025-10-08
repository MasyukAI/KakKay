<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Data;

class TrackingData
{
    /**
     * @param  array<TrackingDetailData>  $details
     */
    public function __construct(
        public readonly string $billCode,
        public readonly array $details,
        public readonly ?string $txlogisticId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $details = array_map(
            fn (array $detail) => TrackingDetailData::fromArray($detail),
            $data['details'] ?? []
        );

        return new self(
            billCode: $data['billCode'],
            details: $details,
            txlogisticId: $data['txlogisticId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'billCode' => $this->billCode,
            'txlogisticId' => $this->txlogisticId,
            'details' => array_map(fn (TrackingDetailData $detail) => $detail->toArray(), $this->details),
        ];
    }
}
