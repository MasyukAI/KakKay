<?php

declare(strict_types=1);

namespace Masyukai\Chip\DataObjects;

class CurrencyConversion
{
    public function __construct(
        public readonly string $original_currency,
        public readonly int $original_amount,
        public readonly float $exchange_rate,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            original_currency: $data['original_currency'],
            original_amount: $data['original_amount'],
            exchange_rate: $data['exchange_rate'],
        );
    }

    public function getOriginalAmountInCurrency(): float
    {
        return $this->original_amount / 100;
    }

    public function toArray(): array
    {
        return [
            'original_currency' => $this->original_currency,
            'original_amount' => $this->original_amount,
            'exchange_rate' => $this->exchange_rate,
        ];
    }
}
