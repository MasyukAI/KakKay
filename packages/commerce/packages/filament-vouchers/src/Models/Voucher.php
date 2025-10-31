<?php

declare(strict_types=1);

namespace AIArmada\FilamentVouchers\Models;

use AIArmada\FilamentVouchers\Support\OwnerTypeRegistry;
use AIArmada\Vouchers\Enums\VoucherType;
use AIArmada\Vouchers\Models\Voucher as BaseVoucher;
use Akaunting\Money\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;

final class Voucher extends BaseVoucher
{
    /**
     * Provides a human-readable representation of the polymorphic owner using
     * the configured owner registry.
     */
    protected function ownerDisplayName(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                $owner = $this->owner;

                if (! $owner) {
                    return null;
                }

                return app(OwnerTypeRegistry::class)->resolveDisplayLabel($owner);
            }
        );
    }

    /**
     * Returns the percentage of the global usage limit that has been consumed.
     */
    protected function usageProgress(): Attribute
    {
        return Attribute::make(
            get: function (): ?float {
                $usageLimit = $this->getAttribute('usage_limit');

                if (! $usageLimit || $usageLimit <= 0) {
                    return null;
                }

                $timesUsed = (int) $this->getAttribute('times_used');

                return min(100, ($timesUsed / $usageLimit) * 100);
            }
        );
    }

    protected function valueLabel(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $value = (int) $this->getAttribute('value');
                $type = $this->getAttribute('type');

                $enumType = $type instanceof VoucherType ? $type : VoucherType::tryFrom((string) $type);

                if ($enumType === VoucherType::Percentage) {
                    // Value is stored as basis points (e.g., 1050 = 10.50%)
                    $percentage = $value / 100;

                    return rtrim(rtrim(number_format($percentage, 2), '0'), '.').' %';
                }

                // Value is stored as cents
                $currency = mb_strtoupper((string) ($this->getAttribute('currency') ?? config('filament-vouchers.default_currency', 'MYR')));

                return (string) Money::{$currency}($value);
            }
        );
    }
}
