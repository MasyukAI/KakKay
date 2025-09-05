<?php

declare(strict_types=1);

namespace MasyukAI\Cart\PriceTransformers;

/**
 * Standard decimal price transformer (stores as float, displays as formatted string)
 * Example: 19.99 -> "19.99" -> 19.99
 */
class DecimalPriceTransformer extends BasePriceTransformer
{
    public function toDisplay(int|float|string $price): string
    {
        return number_format($this->toNumeric($price), $this->precision, '.', '');
    }

    public function toStorage(int|float|string $price): float
    {
        return $this->roundToPrecision($this->toNumeric($price));
    }

    public function toNumeric(int|float|string $price): float
    {
        if (is_string($price)) {
            // Remove thousands separators (commas) but preserve decimal points
            $price = str_replace(',', '', $price);
        }
        
        return (float) $price;
    }
}
