<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Vouchers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherUsage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'voucher_id',
        'user_identifier',
        'cart_identifier',
        'discount_amount',
        'currency',
        'cart_snapshot',
        'used_at',
    ];

    public function getTable(): string
    {
        return config('vouchers.table_names.voucher_usage', 'voucher_usage');
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    protected function casts(): array
    {
        return [
            'discount_amount' => 'decimal:2',
            'cart_snapshot' => 'array',
            'used_at' => 'datetime',
        ];
    }
}
