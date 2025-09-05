<?php

declare(strict_types=1);

namespace MasyukAI\Cart\PriceTransformers;

/**
 * Localized price transformer with custom formatting
 * Example: 1999.50 -> "1.999,50" (German) or "1,999.50" (US)
 */
class LocalizedPriceTransformer extends BasePriceTransformer
{
    public function __construct(
        string $currency = 'USD',
        string $locale = 'en_US',
        int $precision = 2,
        protected string $decimalSeparator = '.',
        protected string $thousandsSeparator = ','
    ) {
        parent::__construct($currency, $locale, $precision);
    }

    public function toDisplay(int|float|string $price): string
    {
        return number_format(
            $this->toNumeric($price),
            $this->precision,
            $this->decimalSeparator,
            $this->thousandsSeparator
        );
    }

    public function toStorage(int|float|string $price): float
    {
        // Remove thousands separators and normalize decimal separator
        $normalized = str_replace($this->thousandsSeparator, '', (string) $price);
        $normalized = str_replace($this->decimalSeparator, '.', $normalized);
        
        return $this->roundToPrecision((float) $normalized);
    }

    public function toNumeric(int|float|string $price): float
    {
        return $this->toStorage($price);
    }
}
