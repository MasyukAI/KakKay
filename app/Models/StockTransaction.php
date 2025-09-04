<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'order_item_id',
        'user_id',
        'quantity',
        'type',
        'reason',
        'note',
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'datetime',
            'quantity' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeInbound($query)
    {
        return $query->where('type', 'in');
    }

    public function scopeOutbound($query)
    {
        return $query->where('type', 'out');
    }

    public function scopeByReason($query, string $reason)
    {
        return $query->where('reason', $reason);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper methods
    public function isInbound(): bool
    {
        return $this->type === 'in';
    }

    public function isOutbound(): bool
    {
        return $this->type === 'out';
    }

    public function isSale(): bool
    {
        return $this->reason === 'sale';
    }

    public function isAdjustment(): bool
    {
        return $this->reason === 'adjustment';
    }
}
