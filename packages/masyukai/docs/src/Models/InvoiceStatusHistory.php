<?php

declare(strict_types=1);

namespace MasyukAI\Docs\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Backward compatibility wrapper for DocumentStatusHistory model
 * @deprecated Use DocumentStatusHistory model instead
 */
class InvoiceStatusHistory extends DocumentStatusHistory
{
    protected $table = 'document_status_histories';

    public function getInvoiceIdAttribute(): ?string
    {
        return $this->document_id;
    }

    public function setInvoiceIdAttribute(?string $value): void
    {
        $this->attributes['document_id'] = $value;
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'document_id');
    }
}
