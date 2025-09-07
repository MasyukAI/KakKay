<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Services;

use MasyukAI\Cart\Contracts\PriceTransformerInterface;
use MasyukAI\Cart\PriceTransformers\DecimalPriceTransformer;

class PriceFormatterService
{
    protected PriceTransformerInterface $transformer;

    public function __construct(?PriceTransformerInterface $transformer = null)
    {
        $this->transformer = $transformer ?? new DecimalPriceTransformer;
    }

    public function setTransformer(PriceTransformerInterface $transformer): self
    {
        $this->transformer = $transformer;

        return $this;
    }

    public function format(int|float|string $price): string
    {
        // For values from storage, use toDisplay directly
        return $this->transformer->toDisplay($price);
    }

    public function formatInput(int|float|string $price): string
    {
        // For input values, normalize then display
        $normalized = $this->transformer->toStorage($price);

        return $this->transformer->toDisplay($normalized);
    }

    public function formatCurrency(int|float|string $price, ?string $currency = null): string
    {
        return $this->transformer->formatCurrency($price, $currency);
    }

    public function normalize(int|float|string $price): int|float
    {
        return $this->transformer->toStorage($price);
    }

    public function calculate(int|float|string $price): float
    {
        return $this->transformer->toNumeric($price);
    }

    public function setCurrency(string $currency): self
    {
        $this->transformer->setCurrency($currency);

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->transformer->getCurrency();
    }
}
