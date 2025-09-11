<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Traits;

use MasyukAI\Cart\Support\CartMoney;

trait ManagesPricing
{
    /**
     * Create CartMoney from amount (dollars/major units)
     */
    protected function createMoney(int|float|string $amount, ?string $currency = null): CartMoney
    {
        return CartMoney::fromAmount($amount, $currency);
    }

    /**
     * Create CartMoney from cents (minor units)
     */
    protected function createMoneyFromCents(int $cents, ?string $currency = null): CartMoney
    {
        return CartMoney::fromCents($cents, $currency);
    }

    /**
     * Create CartMoney from storage value (auto-detects format)
     */
    protected function createMoneyFromStorage(int|float $value): CartMoney
    {
        return CartMoney::fromStorage($value);
    }

    /**
     * Convert amount to storage format
     */
    protected function toStorage(int|float|string $amount): int|float
    {
        return CartMoney::toStorage($amount);
    }

    /**
     * Format money for display
     */
    protected function formatMoney(CartMoney $money): string
    {
        return $money->format();
    }

    /**
     * Format money without currency symbol
     */
    protected function formatMoneySimple(CartMoney $money): string
    {
        return $money->formatSimple();
    }

    /**
     * Get default currency
     */
    protected function getDefaultCurrency(): string
    {
        return config('cart.money.default_currency', 'USD');
    }

    /**
     * Get default precision
     */
    protected function getDefaultPrecision(): int
    {
        return config('cart.money.default_precision', 2);
    }

    /**
     * Get display locale
     */
    protected function getDisplayLocale(): string
    {
        return config('cart.display.locale', 'en_US');
    }
}
