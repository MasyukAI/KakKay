<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Traits;

use MasyukAI\Cart\Support\PriceFormatManager;

trait ManagesPricing
{
    /**
     * Format price value based on current settings
     */
    protected function formatPriceValue(int|float|string $value, bool $withCurrency = false): string|int|float
    {
        return PriceFormatManager::formatPrice($value, $withCurrency);
    }

    /**
     * Enable formatting globally
     */
    public static function enableFormatting(): void
    {
        PriceFormatManager::enableFormatting();
    }

    /**
     * Disable formatting globally
     */
    public static function disableFormatting(): void
    {
        PriceFormatManager::disableFormatting();
    }

    /**
     * Set global currency override
     */
    public static function setCurrency(?string $currency = null): void
    {
        PriceFormatManager::setCurrency($currency);
    }

    /**
     * Reset formatting settings
     */
    public static function resetFormatting(): void
    {
        PriceFormatManager::resetFormatting();
    }
}
