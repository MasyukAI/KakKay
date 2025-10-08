<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Data;

class PackageInfoData
{
    public function __construct(
        public readonly string $packageQuantity,
        public readonly string $weight,
        public readonly string $packageValue,
        public readonly string $goodsType,
        public readonly ?string $length = null,
        public readonly ?string $width = null,
        public readonly ?string $height = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            packageQuantity: (string) $data['packageQuantity'],
            weight: (string) $data['weight'],
            packageValue: (string) $data['packageValue'],
            goodsType: $data['goodsType'],
            length: isset($data['length']) ? (string) $data['length'] : null,
            width: isset($data['width']) ? (string) $data['width'] : null,
            height: isset($data['height']) ? (string) $data['height'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'packageQuantity' => $this->packageQuantity,
            'weight' => $this->weight,
            'packageValue' => $this->packageValue,
            'goodsType' => $this->goodsType,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
        ], fn ($value) => $value !== null);
    }
}
