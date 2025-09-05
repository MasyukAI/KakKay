<?php

declare(strict_types=1);

namespace MasyukAI\Cart\PriceTransformers;

use MasyukAI\Cart\Contracts\PriceTransformerInterface;
use NumberFormatter;

abstract class BasePriceTransformer implements PriceTransformerInterface
{
    public function __construct(
        protected string $currency = 'USD',
        protected string $locale = 'en_US',
        protected int $precision = 2
    ) {
    }

    public function formatCurrency(int|float|string $price, ?string $currency = null): string
    {
        $formatter = new NumberFormatter($this->locale, NumberFormatter::CURRENCY);
        $numericPrice = $this->toNumeric($price);
        
        return $formatter->formatCurrency($numericPrice, $currency ?? $this->currency);
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    protected function roundToPrecision(float $value): float
    {
        return round($value, $this->precision);
    }
}
