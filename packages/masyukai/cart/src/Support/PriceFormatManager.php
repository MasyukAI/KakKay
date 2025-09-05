<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Support;

use MasyukAI\Cart\Services\PriceFormatterService;
use MasyukAI\Cart\PriceTransformers\DecimalPriceTransformer;

class PriceFormatManager
{
    private static ?PriceFormatterService $formatter = null;
    private static bool $globalFormatOverride = false;
    private static ?string $globalCurrencyOverride = null;
    private static ?string $lastTransformerClass = null;

    /**
     * Get price formatter service
     */
    public static function getFormatter(): PriceFormatterService
    {
        $currentTransformerClass = self::getConfig('cart.price_formatting.transformer', DecimalPriceTransformer::class);
        
        // Recreate formatter if transformer class changed or no formatter exists
        if (self::$formatter === null || self::$lastTransformerClass !== $currentTransformerClass) {
            // Create transformer based on class type
            if ($currentTransformerClass === \MasyukAI\Cart\PriceTransformers\LocalizedPriceTransformer::class) {
                $transformer = new $currentTransformerClass(
                    self::getConfig('cart.price_formatting.currency', 'USD'),
                    self::getConfig('cart.price_formatting.locale', 'en_US'),
                    self::getConfig('cart.price_formatting.precision', 2),
                    self::getConfig('cart.price_formatting.decimal_separator', '.'),
                    self::getConfig('cart.price_formatting.thousands_separator', ',')
                );
            } else {
                $transformer = new $currentTransformerClass(
                    self::getConfig('cart.price_formatting.currency', 'USD'),
                    self::getConfig('cart.price_formatting.locale', 'en_US'),
                    self::getConfig('cart.price_formatting.precision', 2)
                );
            }
            
            self::$formatter = new PriceFormatterService($transformer);
            self::$lastTransformerClass = $currentTransformerClass;
        }

        return self::$formatter;
    }

    /**
     * Check if formatting should be applied
     */
    public static function shouldFormat(): bool
    {
        return self::$globalFormatOverride || self::getConfig('cart.price_formatting.auto_format', false);
    }

    /**
     * Format price value based on current settings
     */
    public static function formatPrice(int|float|string $value, bool $withCurrency = false): string|int|float
    {
        if (!self::shouldFormat()) {
            return self::getFormatter()->normalize($value);
        }

        if ($withCurrency && config('cart.price_formatting.show_currency_symbol', false)) {
            return self::getFormatter()->formatCurrency($value, self::$globalCurrencyOverride);
        }

        return self::getFormatter()->format($value);
    }

    /**
     * Format input price value (for user inputs)
     */
    public static function formatInputPrice(int|float|string $value, bool $withCurrency = false): string|int|float
    {
        if (!self::shouldFormat()) {
            return self::getFormatter()->normalize($value);
        }

        if ($withCurrency && config('cart.price_formatting.show_currency_symbol', false)) {
            return self::getFormatter()->formatCurrency($value, self::$globalCurrencyOverride);
        }

        return self::getFormatter()->formatInput($value);
    }

    /**
     * Enable formatting globally
     */
    public static function enableFormatting(): void
    {
        self::$globalFormatOverride = true;
    }

    /**
     * Disable formatting globally
     */
    public static function disableFormatting(): void
    {
        self::$globalFormatOverride = false;
    }

    /**
     * Set global currency override
     */
    public static function setCurrency(?string $currency = null): void
    {
        self::$globalCurrencyOverride = $currency ?? config('cart.price_formatting.currency', 'USD');
        self::getFormatter()->setCurrency(self::$globalCurrencyOverride);
        self::$globalFormatOverride = true;
    }

    /**
     * Reset formatting settings
     */
    public static function resetFormatting(): void
    {
        self::$globalFormatOverride = false;
        self::$globalCurrencyOverride = null;
        self::$formatter = null;
        self::$lastTransformerClass = null;
    }

    /**
     * Safely get config value with fallback
     */
    private static function getConfig(string $key, mixed $default = null): mixed
    {
        try {
            return config($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}
