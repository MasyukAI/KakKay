<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StockTransaction extends Model
{
    use HasFactory, HasUuids;

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

    // Scopes
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function forProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function inbound($query)
    {
        return $query->where('type', 'in');
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function outbound($query)
    {
        return $query->where('type', 'out');
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function byReason($query, string $reason)
    {
        return $query->where('reason', $reason);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function byUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    protected function casts(): array
    {
        return [
            'transaction_date' => 'datetime',
            'quantity' => 'integer',
        ];
    }
}
