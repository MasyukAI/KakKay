<?php

declare(strict_types=1);

namespace AIArmada\Vouchers\Services;

use AIArmada\Vouchers\Contracts\VoucherOwnerResolver;
use AIArmada\Vouchers\Data\VoucherData;
use AIArmada\Vouchers\Data\VoucherValidationResult;
use AIArmada\Vouchers\Enums\VoucherStatus;
use AIArmada\Vouchers\Exceptions\ManualRedemptionNotAllowedException;
use AIArmada\Vouchers\Exceptions\VoucherNotFoundException;
use AIArmada\Vouchers\Models\Voucher as VoucherModel;
use AIArmada\Vouchers\Models\VoucherUsage;
use Akaunting\Money\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VoucherService
{
    public function __construct(
        protected VoucherValidator $validator,
        protected VoucherOwnerResolver $ownerResolver
    ) {}

    public function find(string $code): ?VoucherData
    {
        $voucher = $this->query()
            ->where('code', $this->normalizeCode($code))
            ->first();

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

        if (
            config('vouchers.owner.enabled', false)
            && config('vouchers.owner.auto_assign_on_create', true)
            && ! isset($data['owner_type'], $data['owner_id'])
        ) {
            $owner = $this->resolveOwner();

            if ($owner) {
                $data['owner_type'] = $owner->getMorphClass();
                $data['owner_id'] = $owner->getKey();
            }
        }

        $voucher = VoucherModel::create($data);

        return VoucherData::fromModel($voucher);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(string $code, array $data): VoucherData
    {
        $voucher = $this->query()
            ->where('code', $this->normalizeCode($code))
            ->firstOrFail();

        if (isset($data['code'])) {
            $data['code'] = $this->normalizeCode($data['code']);
        }

        $voucher->update($data);

        return VoucherData::fromModel($voucher->fresh());
    }

    public function delete(string $code): bool
    {
        $voucher = $this->query()
            ->where('code', $this->normalizeCode($code))
            ->first();

        return $voucher ? $voucher->delete() : false;
    }

    public function validate(string $code, mixed $cart): VoucherValidationResult
    {
        return $this->validator->validate($code, $cart);
    }

    public function isValid(string $code): bool
    {
        $voucher = $this->query()
            ->where('code', $this->normalizeCode($code))
            ->first();

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
        $voucher = $this->query()
            ->where('code', $this->normalizeCode($code))
            ->first();

        if (! $voucher) {
            return false;
        }

        if (! $voucher->usage_limit_per_user) {
            return true;
        }

        $usageCount = VoucherUsage::where('voucher_id', $voucher->id)
            ->where('user_identifier', $userIdentifier)
            ->count();

        return $usageCount < $voucher->usage_limit_per_user;
    }

    public function getRemainingUses(string $code): int
    {
        $voucher = $this->query()
            ->where('code', $this->normalizeCode($code))
            ->first();

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
        ?array $cartSnapshot = null,
        string $channel = VoucherUsage::CHANNEL_AUTOMATIC,
        ?array $metadata = null,
        ?Model $redeemedBy = null,
        ?string $notes = null,
        ?VoucherModel $voucherModel = null
    ): void {
        $voucher = $voucherModel ?? $this->query()
            ->where('code', $this->normalizeCode($code))
            ->firstOrFail();

        DB::transaction(function () use (
            $voucher,
            $userIdentifier,
            $discountAmount,
            $cartIdentifier,
            $cartSnapshot,
            $channel,
            $metadata,
            $redeemedBy,
            $notes
        ): void {
            $payload = [
                'voucher_id' => $voucher->id,
                'user_identifier' => $userIdentifier,
                'cart_identifier' => $cartIdentifier,
                'discount_amount' => $discountAmount->getValue(),
                'currency' => $discountAmount->getCurrency()->getCurrency(),
                'cart_snapshot' => config('vouchers.tracking.store_cart_snapshot') ? $cartSnapshot : null,
                'channel' => $channel,
                'metadata' => $metadata,
                'notes' => $notes,
                'used_at' => now(),
            ];

            if ($redeemedBy) {
                $payload['redeemed_by_type'] = $redeemedBy->getMorphClass();
                $payload['redeemed_by_id'] = $redeemedBy->getKey();
            }

            VoucherUsage::create($payload);

            $voucher->incrementUsage();
        });
    }

    public function redeemManually(
        string $code,
        string $userIdentifier,
        Money $discountAmount,
        ?string $reference = null,
        ?array $metadata = null,
        ?Model $redeemedBy = null,
        ?string $notes = null
    ): void {
        $voucher = $this->query()
            ->where('code', $this->normalizeCode($code))
            ->firstOrFail();

        if (
            config('vouchers.redemption.manual_requires_flag', true)
            && ! $voucher->allowsManualRedemption()
        ) {
            throw ManualRedemptionNotAllowedException::forVoucher($voucher->code);
        }

        $channel = config('vouchers.redemption.channels.manual')
            ?? VoucherUsage::CHANNEL_MANUAL;

        $this->recordUsage(
            code: $code,
            userIdentifier: $userIdentifier,
            discountAmount: $discountAmount,
            cartIdentifier: $reference,
            cartSnapshot: null,
            channel: $channel,
            metadata: $metadata,
            redeemedBy: $redeemedBy,
            notes: $notes,
            voucherModel: $voucher
        );
    }

    /**
     * @return Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function getUsageHistory(string $code): Collection
    {
        $voucher = $this->query()
            ->where('code', $this->normalizeCode($code))
            ->first();

        if (! $voucher) {
            return collect();
        }

        return collect(
            $voucher->usages()
                ->latest('used_at')
                ->get()
        );
    }

    protected function normalizeCode(string $code): string
    {
        if (config('vouchers.code.auto_uppercase', true)) {
            return mb_strtoupper(mb_trim($code));
        }

        return mb_trim($code);
    }

    protected function query(): Builder
    {
        return VoucherModel::query()->forOwner(
            $this->resolveOwner(),
            $this->shouldIncludeGlobal()
        );
    }

    protected function resolveOwner(): ?Model
    {
        if (! config('vouchers.owner.enabled', false)) {
            return null;
        }

        return $this->ownerResolver->resolve();
    }

    protected function shouldIncludeGlobal(): bool
    {
        return (bool) config('vouchers.owner.include_global', true);
    }
}
