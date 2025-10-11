<?php

declare(strict_types=1);

namespace AIArmada\Vouchers\Services;

use AIArmada\Vouchers\Data\VoucherData;
use AIArmada\Vouchers\Data\VoucherValidationResult;
use AIArmada\Vouchers\Enums\VoucherStatus;
use AIArmada\Vouchers\Exceptions\VoucherNotFoundException;
use AIArmada\Vouchers\Models\Voucher as VoucherModel;
use AIArmada\Vouchers\Models\VoucherUsage;
use Akaunting\Money\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VoucherService
{
    public function __construct(
        protected VoucherValidator $validator
    ) {}

    public function find(string $code): ?VoucherData
    {
        $voucher = VoucherModel::where('code', $this->normalizeCode($code))->first();

        return $voucher ? VoucherData::fromModel($voucher) : null;
    }

    public function findOrFail(string $code): VoucherData
    {
        $voucher = $this->find($code);

        if (! $voucher) {
            throw VoucherNotFoundException::withCode($code);
        }

        return $voucher;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): VoucherData
    {
        $data['code'] = $this->normalizeCode($data['code']);
        $data['status'] ??= VoucherStatus::Active;

        $voucher = VoucherModel::create($data);

        return VoucherData::fromModel($voucher);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(string $code, array $data): VoucherData
    {
        $voucher = VoucherModel::where('code', $this->normalizeCode($code))->firstOrFail();

        if (isset($data['code'])) {
            $data['code'] = $this->normalizeCode($data['code']);
        }

        $voucher->update($data);

        return VoucherData::fromModel($voucher->fresh());
    }

    public function delete(string $code): bool
    {
        $voucher = VoucherModel::where('code', $this->normalizeCode($code))->first();

        return $voucher ? $voucher->delete() : false;
    }

    public function validate(string $code, mixed $cart): VoucherValidationResult
    {
        return $this->validator->validate($code, $cart);
    }

    public function isValid(string $code): bool
    {
        $voucher = VoucherModel::where('code', $this->normalizeCode($code))->first();

        if (! $voucher) {
            return false;
        }

        return $voucher->isActive()
            && $voucher->hasStarted()
            && ! $voucher->isExpired()
            && $voucher->hasUsageLimitRemaining();
    }

    public function canBeUsedBy(string $code, string $userIdentifier): bool
    {
        $voucher = VoucherModel::where('code', $this->normalizeCode($code))->first();

        if (! $voucher || ! $voucher->usage_limit_per_user) {
            return true;
        }

        $usageCount = VoucherUsage::where('voucher_id', $voucher->id)
            ->where('user_identifier', $userIdentifier)
            ->count();

        return $usageCount < $voucher->usage_limit_per_user;
    }

    public function getRemainingUses(string $code): int
    {
        $voucher = VoucherModel::where('code', $this->normalizeCode($code))->first();

        if (! $voucher) {
            return 0;
        }

        return $voucher->getRemainingUses() ?? PHP_INT_MAX;
    }

    /**
     * @param  ?array<string, mixed>  $cartSnapshot
     */
    public function recordUsage(
        string $code,
        string $userIdentifier,
        Money $discountAmount,
        ?string $cartIdentifier = null,
        ?array $cartSnapshot = null
    ): void {
        $voucher = VoucherModel::where('code', $this->normalizeCode($code))->firstOrFail();

        DB::transaction(function () use ($voucher, $userIdentifier, $discountAmount, $cartIdentifier, $cartSnapshot) {
            VoucherUsage::create([
                'voucher_id' => $voucher->id,
                'user_identifier' => $userIdentifier,
                'cart_identifier' => $cartIdentifier,
                'discount_amount' => $discountAmount->getValue(),
                'currency' => $discountAmount->getCurrency()->getCurrency(),
                'cart_snapshot' => config('vouchers.tracking.store_cart_snapshot') ? $cartSnapshot : null,
                'used_at' => now(),
            ]);

            $voucher->incrementUsage();
        });
    }

    /**
     * @return Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function getUsageHistory(string $code): Collection
    {
        $voucher = VoucherModel::where('code', $this->normalizeCode($code))->first();

        if (! $voucher) {
            return collect();
        }

        return collect($voucher->usages()->latest('used_at')->get());
    }

    protected function normalizeCode(string $code): string
    {
        if (config('vouchers.code.auto_uppercase', true)) {
            return mb_strtoupper(mb_trim($code));
        }

        return mb_trim($code);
    }
}
