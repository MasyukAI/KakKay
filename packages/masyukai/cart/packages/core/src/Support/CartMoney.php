<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Support;

use Akaunting\Money\Currency;
use Akaunting\Money\Money;
use InvalidArgumentException;
use JsonSerializable;
use Stringable;

/**
 * Modern Money wrapper for cart operations
 * 
 * A clean, modern API for handling money in cart operations.
 * Uses Laravel Money internally with cents-based storage by default.
 */
class CartMoney implements JsonSerializable, Stringable
{
    private Money $money;
    private static bool $formattingEnabled = false;
    private static ?string $globalCurrencyOverride = null;

    private function __construct(Money $money)
    {
        $this->money = $money;
    }

    /**
     * Create from amount in major units (dollars)
     */
    public static function fromAmount(int|float|string $amount, ?string $currency = null): self
    {
        $currency = $currency ?? self::$globalCurrencyOverride ?? config('cart.money.default_currency', 'USD');
        
        // Convert major units to minor units (dollars to cents)
        $precision = config('cart.money.default_precision', 2);
        $minorUnits = (float) $amount * pow(10, $precision);
        
        return new self(new Money($minorUnits, new Currency($currency)));
    }

    /**
     * Create from minor units (cents)
     */
    public static function fromCents(int $cents, ?string $currency = null): self
    {
        $currency = $currency ?? self::$globalCurrencyOverride ?? config('cart.money.default_currency', 'USD');
        
        return new self(new Money($cents, new Currency($currency)));
    }

    /**
     * Create from storage value (auto-detects format based on config)
     */
    public static function fromStorage(int|float $value, ?string $currency = null): self
    {
        $transformerType = config('cart.transformers.default', 'integer');
        
        if ($transformerType === 'integer') {
            // Storage value is in cents, convert to major units
            return self::fromCents((int) $value, $currency);
        } else {
            // Storage value is already in major units 
            return self::fromAmount($value, $currency);
        }
    }

    /**
     * Get amount in major units (dollars)
     */
    public function getAmount(): float
    {
        // Convert from minor units back to major units
        $precision = config('cart.money.default_precision', 2);
        return (float) $this->money->getAmount() / pow(10, $precision);
    }

    /**
     * Get amount in minor units (cents)
     */
    public function getCents(): int
    {
        return (int) $this->money->getAmount();
    }

    /**
     * Convert amount to storage format
     */
    public static function toStorage(int|float|string $amount): int|float
    {
        $transformerType = config('cart.transformers.default', 'integer');
        
        if ($transformerType === 'integer') {
            // Convert to cents for storage
            $precision = config('cart.money.default_precision', 2);
            return (int) round((float) $amount * pow(10, $precision));
        } else {
            // Store as-is (decimal)
            return (float) $amount;
        }
    }

    /**
     * Get currency
     */
    public function getCurrency(): string
    {
        return $this->money->getCurrency()->getCurrency();
    }

    /**
     * Add another CartMoney
     */
    public function add(self $other): self
    {
        return new self($this->money->add($other->money));
    }

    /**
     * Subtract another CartMoney
     */
    public function subtract(self $other): self
    {
        return new self($this->money->subtract($other->money));
    }

    /**
     * Multiply by a factor
     */
    public function multiply(int|float $multiplier): self
    {
        return new self($this->money->multiply($multiplier));
    }

    /**
     * Divide by a divisor
     */
    public function divide(int|float $divisor): self
    {
        return new self($this->money->divide($divisor));
    }

    /**
     * Check if equal to another CartMoney
     */
    public function equals(self $other): bool
    {
        return $this->money->equals($other->money);
    }

    /**
     * Check if greater than another CartMoney
     */
    public function greaterThan(self $other): bool
    {
        return $this->money->greaterThan($other->money);
    }

    /**
     * Check if less than another CartMoney
     */
    public function lessThan(self $other): bool
    {
        return $this->money->lessThan($other->money);
    }

    /**
     * Check if amount is zero
     */
    public function isZero(): bool
    {
        return $this->money->isZero();
    }

    /**
     * Check if amount is negative
     */
    public function isNegative(): bool
    {
        return $this->money->isNegative();
    }

    /**
     * Get precision (decimal places)
     */
    public function getPrecision(): int
    {
        return $this->money->getCurrency()->getPrecision();
    }

    /**
     * Format the money for display
     */
    public function format(?string $locale = null): string
    {
        if (!self::shouldFormat()) {
            return (string) $this->getAmount();
        }

        $locale = $locale ?? config('cart.display.locale', 'en_US');
        $showSymbol = config('cart.display.show_currency_symbol', true);
        
        if ($showSymbol) {
            return $this->money->format($locale);
        }

        // Format without currency symbol
        $formatter = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, config('cart.money.default_precision', 2));
        
        return $formatter->format($this->getAmount());
    }

    /**
     * Format the money without currency symbol
     */
    public function formatSimple(?string $locale = null): string
    {
        if (!self::shouldFormat()) {
            return (string) $this->getAmount();
        }

        $precision = config('cart.money.default_precision', 2);
        
        // Simple format without thousands separators
        return number_format($this->getAmount(), $precision, '.', '');
    }

    /**
     * Get raw amount as string (unformatted)
     */
    public function raw(): string
    {
        return (string) $this->getAmount();
    }

    /**
     * Set global currency override
     */
    public static function setCurrency(?string $currency): void
    {
        self::$globalCurrencyOverride = $currency;
    }

    /**
     * Enable/disable formatting globally
     */
    public static function enableFormatting(bool $enabled = true): void
    {
        self::$formattingEnabled = $enabled;
    }

    /**
     * Disable formatting globally
     */
    public static function disableFormatting(): void
    {
        self::$formattingEnabled = false;
    }

    /**
     * Reset formatting state (alias for reset)
     */
    public static function resetFormatting(): void
    {
        self::reset();
    }

    /**
     * Check if formatting should be applied
     */
    public static function shouldFormat(): bool
    {
        return self::$formattingEnabled || config('cart.display.formatting_enabled', true);
    }

    /**
     * Reset all static state (for Octane compatibility)
     */
    public static function reset(): void
    {
        self::$formattingEnabled = false;
        self::$globalCurrencyOverride = null;
    }

    /**
     * Create from major units (for backward compatibility)
     */
    public static function fromMajorUnits(int|float $amount, ?string $currency = null): self
    {
        return self::fromAmount($amount, $currency);
    }

    /**
     * Create from minor units (for backward compatibility)
     */
    public static function fromMinorUnits(int $amount, ?string $currency = null): self
    {
        return self::fromCents($amount, $currency);
    }

    /**
     * Get amount in major units (for backward compatibility)
     */
    public function getMajorUnits(): float
    {
        return $this->getAmount();
    }

    /**
     * Get amount in minor units (for backward compatibility)
     */
    public function getMinorUnits(): int
    {
        return $this->getCents();
    }

    /**
     * Convert to string
     */
    public function __toString(): string
    {
        return $this->format();
    }

    /**
     * JSON serialization
     */
    public function jsonSerialize(): array
    {
        return [
            'amount' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'formatted' => $this->format(),
            'raw' => $this->raw(),
        ];
    }

    /**
     * Create from array (for JSON deserialization)
     */
    public static function fromArray(array $data): self
    {
        return self::fromAmount($data['amount'], $data['currency']);
    }
}
