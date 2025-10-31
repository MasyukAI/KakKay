<?php

declare(strict_types=1);

namespace AIArmada\Vouchers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VoucherUsage extends Model
{
    public const CHANNEL_AUTOMATIC = 'automatic';
    public const CHANNEL_MANUAL = 'manual';
    public const CHANNEL_API = 'api';

    public $timestamps = false;

    protected $fillable = [
        'voucher_id',
        'user_identifier',
        'cart_identifier',
        'discount_amount',
        'currency',
        'cart_snapshot',
        'channel',
        'notes',
        'metadata',
        'redeemed_by_type',
        'redeemed_by_id',
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

    public function redeemedBy(): MorphTo
    {
        return $this->morphTo();
    }

    public function isManual(): bool
    {
        return $this->getAttribute('channel') === self::CHANNEL_MANUAL;
    }

    protected function casts(): array
    {
        return [
            'discount_amount' => 'integer', // Stored as cents
            'cart_snapshot' => 'array',
            'metadata' => 'array',
            'used_at' => 'datetime',
        ];
    }
}
