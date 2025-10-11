<?php

declare(strict_types=1);

namespace AIArmada\Vouchers\Data;

use AIArmada\Vouchers\Enums\VoucherStatus;
use AIArmada\Vouchers\Enums\VoucherType;
use DateTimeInterface;

readonly class VoucherData
{
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public ?string $description,
        public VoucherType $type,
        public float $value,
        public string $currency,
        public ?float $minCartValue,
        public ?float $maxDiscount,
        public ?int $usageLimit,
        public ?int $usageLimitPerUser,
        public int $timesUsed,
        public ?DateTimeInterface $startsAt,
        public ?DateTimeInterface $expiresAt,
        public VoucherStatus $status,
        /** @var ?array<int|string, mixed> */
        public ?array $applicableProducts,
        /** @var ?array<int|string, mixed> */
        public ?array $excludedProducts,
        /** @var ?array<int|string, mixed> */
        public ?array $applicableCategories,
        /** @var ?array<string, mixed> */
        public ?array $metadata,
    ) {}

    public static function fromModel(mixed $voucher): self
    {
        return new self(
            id: $voucher->id,
            code: $voucher->code,
            name: $voucher->name,
            description: $voucher->description,
            type: VoucherType::from($voucher->type),
            value: (float) $voucher->value,
            currency: $voucher->currency,
            minCartValue: $voucher->min_cart_value ? (float) $voucher->min_cart_value : null,
            maxDiscount: $voucher->max_discount ? (float) $voucher->max_discount : null,
            usageLimit: $voucher->usage_limit,
            usageLimitPerUser: $voucher->usage_limit_per_user,
            timesUsed: $voucher->times_used,
            startsAt: $voucher->starts_at,
            expiresAt: $voucher->expires_at,
            status: VoucherStatus::from($voucher->status),
            applicableProducts: $voucher->applicable_products,
            excludedProducts: $voucher->excluded_products,
            applicableCategories: $voucher->applicable_categories,
            metadata: $voucher->metadata,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type->value,
            'value' => $this->value,
            'currency' => $this->currency,
            'min_cart_value' => $this->minCartValue,
            'max_discount' => $this->maxDiscount,
            'usage_limit' => $this->usageLimit,
            'usage_limit_per_user' => $this->usageLimitPerUser,
            'times_used' => $this->timesUsed,
            'starts_at' => $this->startsAt?->format('Y-m-d H:i:s'),
            'expires_at' => $this->expiresAt?->format('Y-m-d H:i:s'),
            'status' => $this->status->value,
            'applicable_products' => $this->applicableProducts,
            'excluded_products' => $this->excludedProducts,
            'applicable_categories' => $this->applicableCategories,
            'metadata' => $this->metadata,
        ];
    }
}
