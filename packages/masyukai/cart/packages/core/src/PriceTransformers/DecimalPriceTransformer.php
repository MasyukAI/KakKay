<?php

declare(strict_types=1);

namespace MasyukAI\Cart\PriceTransformers;

use MasyukAI\Cart\Contracts\PriceTransformerInterface;

/**
 * Standard decimal price transformer (stores as float)
 * Example: 19.99 -> 19.99 (storage) -> 19.99 (retrieval)
 */
class DecimalPriceTransformer implements \MasyukAI\Cart\Contracts\PriceTransformerInterface
{
    public function __construct(
        protected int $precision = 2
    ) {}

    public function toStorage(int|float|string $price): float
    {
        if (is_string($price)) {
            // Remove thousands separators (commas) but preserve decimal points
            $price = str_replace(',', '', $price);
        }

        return round((float) $price, $this->precision);
    }

    public function fromStorage(int|float $storageValue): float
    {
        return (float) $storageValue;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

}
