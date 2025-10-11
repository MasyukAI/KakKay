<?php

declare(strict_types=1);

namespace AIArmada\Vouchers\Models;

use AIArmada\Vouchers\Enums\VoucherStatus;
use AIArmada\Vouchers\Enums\VoucherType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use SoftDeletes;

    public string $code;

    public string $name;

    public ?string $description;

    public VoucherType $type;

    public string $value;

    public string $currency;

    public ?string $min_cart_value;

    public ?string $max_discount;

    public ?int $usage_limit;

    public ?int $usage_limit_per_user;

    public int $times_used;

    public ?\Illuminate\Support\Carbon $starts_at;

    public ?\Illuminate\Support\Carbon $expires_at;

    public VoucherStatus $status;

    /** @var ?array<int|string, mixed> */
    public ?array $applicable_products;

    /** @var ?array<int|string, mixed> */
    public ?array $excluded_products;

    /** @var ?array<int|string, mixed> */
    public ?array $applicable_categories;

    /** @var ?array<string, mixed> */
    public ?array $metadata;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'currency',
        'min_cart_value',
        'max_discount',
        'usage_limit',
        'usage_limit_per_user',
        'times_used',
        'starts_at',
        'expires_at',
        'status',
        'applicable_products',
        'excluded_products',
        'applicable_categories',
        'metadata',
    ];

    public function getTable(): string
    {
        return config('vouchers.table_names.vouchers', 'vouchers');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class);
    }

    public function isActive(): bool
    {
        return $this->status === VoucherStatus::Active;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function hasStarted(): bool
    {
        return ! $this->starts_at || $this->starts_at->isPast();
    }

    public function hasUsageLimitRemaining(): bool
    {
        if (! $this->usage_limit) {
            return true;
        }

        return $this->times_used < $this->usage_limit;
    }

    public function getRemainingUses(): ?int
    {
        if (! $this->usage_limit) {
            return null;
        }

        return max(0, $this->usage_limit - $this->times_used);
    }

    public function incrementUsage(): void
    {
        $this->increment('times_used');

        // Auto-update status if depleted
        if ($this->usage_limit && $this->times_used >= $this->usage_limit) {
            $this->update(['status' => VoucherStatus::Depleted]);
        }
    }

    protected function casts(): array
    {
        return [
            'type' => VoucherType::class,
            'status' => VoucherStatus::class,
            'value' => 'decimal:2',
            'min_cart_value' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'usage_limit' => 'integer',
            'usage_limit_per_user' => 'integer',
            'times_used' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'applicable_products' => 'array',
            'excluded_products' => 'array',
            'applicable_categories' => 'array',
            'metadata' => 'array',
        ];
    }
}
