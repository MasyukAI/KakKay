<?php

declare(strict_types=1);

namespace MasyukAI\Cart\PriceTransformers;

/**
 * Integer-based price transformer (stores as cents, displays as dollars)
 * Example: 1999 (cents) -> "19.99" -> 1999 (cents)
 */
class IntegerPriceTransformer extends BasePriceTransformer
{
    protected int $multiplier;

    public function __construct(
        string $currency = 'USD',
        string $locale = 'en_US',
        int $precision = 2
    ) {
        parent::__construct($currency, $locale, $precision);
        $this->multiplier = (int) pow(10, $precision);
    }

    public function toDisplay(int|float|string $price): string
    {
        $numericPrice = (float) $price;

        // Detect if the value is already in display format
        // Display format values are typically < 1000 and have decimal places
        // Storage format values are typically >= 100 (for prices > $1.00)
        if ($numericPrice < 1000 && fmod($numericPrice, 1) !== 0.0) {
            // This looks like a display format value (e.g., 19.99)
            // Convert to storage format first, then back to display
            $storageValue = $this->toStorage($numericPrice);
            $decimal = $storageValue / $this->multiplier;
        } else {
            // This looks like a storage format value (e.g., 1999)
            $decimal = (int) $price / $this->multiplier;
        }

        return number_format($decimal, $this->precision, '.', '');
    }

    public function toStorage(int|float|string $price): int
    {
        if (is_string($price)) {
            // Remove thousands separators (commas) but preserve decimal points
            $price = str_replace(',', '', $price);
        }

        return (int) round((float) $price * $this->multiplier);
    }

    public function toNumeric(int|float|string $price): float
    {
        if (is_string($price)) {
            // Remove thousands separators (commas) but preserve decimal points
            $price = str_replace(',', '', $price);
        }

        // Always treat stored values as cents and convert to dollars
        return (float) ((int) $price) / $this->multiplier;
    }
}
