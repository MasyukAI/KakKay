<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Contracts;

interface PriceTransformerInterface
{
    /**
     * Transform price from storage format to display format
     */
    public function toDisplay(int|float|string $price): string;

    /**
     * Transform price from input format to storage format
     */
    public function toStorage(int|float|string $price): int|float;

    /**
     * Get the raw numeric value for calculations
     */
    public function toNumeric(int|float|string $price): float;

    /**
     * Format price with currency symbol and locale
     */
    public function formatCurrency(int|float|string $price, ?string $currency = null): string;

    /**
     * Set the currency for formatting
     */
    public function setCurrency(string $currency): self;

    /**
     * Get the current currency
     */
    public function getCurrency(): string;
}
