<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Data;

class ItemData
{
    public function __construct(
        public readonly string $itemName,
        public readonly string $number,
        public readonly string $weight,
        public readonly string $itemValue,
        public readonly ?string $englishName = null,
        public readonly ?string $itemDesc = null,
        public readonly string $itemCurrency = 'MYR',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            itemName: $data['itemName'],
            number: (string) $data['number'],
            weight: (string) $data['weight'],
            itemValue: (string) $data['itemValue'],
            englishName: $data['englishName'] ?? null,
            itemDesc: $data['itemDesc'] ?? null,
            itemCurrency: $data['itemCurrency'] ?? 'MYR',
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'itemName' => $this->itemName,
            'englishName' => $this->englishName,
            'number' => $this->number,
            'weight' => $this->weight,
            'itemValue' => $this->itemValue,
            'itemCurrency' => $this->itemCurrency,
            'itemDesc' => $this->itemDesc,
        ], fn ($value) => $value !== null);
    }
}
