<?php

declare(strict_types=1);

namespace MasyukAI\Cart\PriceTransformers;

use MasyukAI\Cart\Contracts\PriceTransformerInterface;

/**
 * Integer-based price transformer (stores as cents)
 * Example: 19.99 -> 1999 (storage) -> 19.99 (retrieval)
 */
class IntegerPriceTransformer implements PriceTransformerInterface
{
    public function __construct(
        protected int $precision = 2
    ) {}

    public function toStorage(int|float|string $price): int
    {
        if (is_string($price)) {
            // Remove thousands separators (commas) but preserve decimal points
            $price = str_replace(',', '', $price);
        }

        $multiplier = (int) pow(10, $this->precision);
        
        return (int) round((float) $price * $multiplier);
    }

    public function fromStorage(int|float $storageValue): float
    {
        $multiplier = (int) pow(10, $this->precision);
        
        return (float) $storageValue / $multiplier;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }
}
