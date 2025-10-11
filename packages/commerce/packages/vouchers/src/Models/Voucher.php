<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Vouchers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use MasyukAI\Cart\Vouchers\Enums\VoucherStatus;
use MasyukAI\Cart\Vouchers\Enums\VoucherType;

class Voucher extends Model
{
    use SoftDeletes;

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
