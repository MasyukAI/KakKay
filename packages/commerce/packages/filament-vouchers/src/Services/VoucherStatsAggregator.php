<?php

declare(strict_types=1);

namespace AIArmada\FilamentVouchers\Services;

use AIArmada\FilamentVouchers\Models\Voucher;
use AIArmada\FilamentVouchers\Models\VoucherUsage;
use AIArmada\Vouchers\Enums\VoucherStatus;

final class VoucherStatsAggregator
{
    /**
     * @return array{
     *     total: int,
     *     active: int,
     *     upcoming: int,
     *     expired: int,
     *     manual_redemptions: int,
     *     total_discount_minor: int,
     * }
     */
    public function overview(): array
    {
        return [
            'total' => Voucher::query()->count(),
            'active' => Voucher::query()->where('status', VoucherStatus::Active)->count(),
            'upcoming' => Voucher::query()
                ->where(function ($query): void {
                    $query
                        ->whereNull('starts_at')
                        ->orWhere('starts_at', '>', now());
                })
                ->count(),
            'expired' => Voucher::query()->where('status', VoucherStatus::Expired)->count(),
            'manual_redemptions' => VoucherUsage::query()->where('channel', VoucherUsage::CHANNEL_MANUAL)->count(),
            'total_discount_minor' => $this->sumDiscountMinor(),
        ];
    }

    private function sumDiscountMinor(): int
    {
        $sum = (string) VoucherUsage::query()->sum('discount_amount');

        if ($sum === '' || $sum === '0' || $sum === '0.00') {
            return 0;
        }

        return (int) round(((float) $sum) * 100);
    }
}
