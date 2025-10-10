<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'order_id',
        'gateway_transaction_id',
        'gateway_payment_id',
        'gateway_response',
        'amount',
        'status',
        'method',
        'currency',
        'paid_at',
        'failed_at',
        'refunded_at',
        'note',
        'reference',
    ];

    protected $casts = [
        'amount' => 'integer',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    /**
     * Get the order this payment belongs to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return $this->currency.' '.number_format($this->amount / 100, 2);
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if payment is refunded
     */
    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    /**
     * Mark payment as completed
     */
    public function markCompleted(): bool
    {
        return $this->update([
            'status' => 'completed',
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark payment as failed
     */
    public function markFailed(?string $reason = null): bool
    {
        return $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'note' => $reason,
        ]);
    }

    /**
     * Mark payment as refunded
     */
    public function markRefunded(): bool
    {
        return $this->update([
            'status' => 'refunded',
            'refunded_at' => now(),
        ]);
    }

    /**
     * Scope for completed payments
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function completed($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending payments
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function pending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for failed payments
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function failed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for refunded payments
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function refunded($query)
    {
        return $query->where('status', 'refunded');
    }

    /**
     * Scope by gateway method
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function byGateway($query, string $gatewayMethod)
    {
        return $query->where('method', $gatewayMethod);
    }
}
