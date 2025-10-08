<?php

declare(strict_types=1);

namespace MasyukAI\Invoice\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MasyukAI\Invoice\Enums\InvoiceStatus;

class InvoiceStatusHistory extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'invoice_id',
        'status',
        'notes',
        'changed_by',
    ];

    protected $casts = [
        'status' => InvoiceStatus::class,
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
