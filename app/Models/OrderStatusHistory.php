<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderStatusHistory extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'actor_type',
        'changed_by',
        'meta',
        'note',
        'changed_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'changed_at' => 'datetime',
    ];

    /**
     * Get the order this history belongs to
     */
    /** @phpstan-ignore-next-line */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who changed the status (if applicable)
     */
    /** @phpstan-ignore-next-line */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
