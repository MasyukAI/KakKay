<?php

declare(strict_types=1);

namespace AIArmada\Docs\Models;

use AIArmada\Docs\Enums\DocumentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $document_number
 * @property string $document_type
 * @property string|null $document_template_id
 * @property string|null $documentable_type
 * @property string|null $documentable_id
 * @property DocumentStatus $status
 * @property \Illuminate\Support\Carbon $issue_date
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property string $subtotal
 * @property string $tax_amount
 * @property string $discount_amount
 * @property string $total
 * @property string $currency
 * @property string|null $notes
 * @property string|null $terms
 * @property array<string, mixed>|null $customer_data
 * @property array<string, mixed>|null $company_data
 * @property array<int, array<string, mixed>>|null $items
 * @property array<string, mixed>|null $metadata
 * @property string|null $pdf_path
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Document extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'document_number',
        'document_type',
        'document_template_id',
        'documentable_type',
        'documentable_id',
        'status',
        'issue_date',
        'due_date',
        'paid_at',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total',
        'currency',
        'notes',
        'terms',
        'customer_data',
        'company_data',
        'items',
        'metadata',
        'pdf_path',
    ];

    protected $casts = [
        'status' => DocumentStatus::class,
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'customer_data' => 'array',
        'company_data' => 'array',
        'items' => 'array',
        'metadata' => 'array',
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(DocumentStatusHistory::class);
    }

    public function isOverdue(): bool
    {
        if ($this->status === DocumentStatus::PAID || $this->status === DocumentStatus::CANCELLED) {
            return false;
        }

        return $this->due_date !== null && $this->due_date->isPast();
    }

    public function isPaid(): bool
    {
        return $this->status === DocumentStatus::PAID;
    }

    public function canBePaid(): bool
    {
        return $this->status->isPayable();
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => DocumentStatus::PAID,
            'paid_at' => now(),
        ]);
    }

    public function markAsSent(): void
    {
        if ($this->status === DocumentStatus::DRAFT || $this->status === DocumentStatus::PENDING) {
            $this->update(['status' => DocumentStatus::SENT]);
        }
    }

    public function cancel(): void
    {
        if ($this->status !== DocumentStatus::PAID) {
            $this->update(['status' => DocumentStatus::CANCELLED]);
        }
    }

    /**
     * Update status and check for overdue
     */
    public function updateStatus(): void
    {
        if ($this->isOverdue() && $this->status !== DocumentStatus::OVERDUE) {
            $this->update(['status' => DocumentStatus::OVERDUE]);
        }
    }
}
