<?php

declare(strict_types=1);

namespace MasyukAI\Stock\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class StockTransaction extends Model
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'stockable_type',
        'stockable_id',
        'user_id',
        'quantity',
        'type',
        'reason',
        'note',
        'transaction_date',
    ];

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('stock.table_name', 'stock_transactions');
    }

    /**
     * Get the stockable model (Product, Variant, etc.)
     */
    public function stockable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who performed the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Check if transaction is inbound.
     */
    public function isInbound(): bool
    {
        return $this->type === 'in';
    }

    /**
     * Check if transaction is outbound.
     */
    public function isOutbound(): bool
    {
        return $this->type === 'out';
    }

    /**
     * Check if transaction is a sale.
     */
    public function isSale(): bool
    {
        return $this->reason === 'sale';
    }

    /**
     * Check if transaction is an adjustment.
     */
    public function isAdjustment(): bool
    {
        return $this->reason === 'adjustment';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transaction_date' => 'datetime',
            'quantity' => 'integer',
        ];
    }
}
