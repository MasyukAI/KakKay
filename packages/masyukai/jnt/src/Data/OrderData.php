<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Data;

class OrderData
{
    /**
     * @param  array<ItemData>  $items
     */
    public function __construct(
        public readonly string $txlogisticId,
        public readonly ?string $billCode = null,
        public readonly ?string $sortingCode = null,
        public readonly ?string $thirdSortingCode = null,
        public readonly ?array $multipleVoteBillCodes = null,
        public readonly ?string $packageChargeWeight = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            txlogisticId: $data['txlogisticId'],
            billCode: $data['billCode'] ?? null,
            sortingCode: $data['sortingCode'] ?? null,
            thirdSortingCode: $data['thirdSortingCode'] ?? null,
            multipleVoteBillCodes: $data['multipleVoteBillCodes'] ?? null,
            packageChargeWeight: $data['packageChargeWeight'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'txlogisticId' => $this->txlogisticId,
            'billCode' => $this->billCode,
            'sortingCode' => $this->sortingCode,
            'thirdSortingCode' => $this->thirdSortingCode,
            'multipleVoteBillCodes' => $this->multipleVoteBillCodes,
            'packageChargeWeight' => $this->packageChargeWeight,
        ], fn ($value) => $value !== null);
    }
}
