<?php

declare(strict_types=1);

namespace MasyukAI\Invoice\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MasyukAI\Invoice\Enums\InvoiceStatus;

class Invoice extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'invoice_number',
        'invoice_template_id',
        'invoiceable_type',
        'invoiceable_id',
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
        'status' => InvoiceStatus::class,
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

    public function invoiceable()
    {
        return $this->morphTo();
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(InvoiceTemplate::class, 'invoice_template_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(InvoiceStatusHistory::class);
    }

    public function isOverdue(): bool
    {
        if ($this->status === InvoiceStatus::PAID || $this->status === InvoiceStatus::CANCELLED) {
            return false;
        }

        return $this->due_date !== null && $this->due_date->isPast();
    }

    public function isPaid(): bool
    {
        return $this->status === InvoiceStatus::PAID;
    }

    public function canBePaid(): bool
    {
        return $this->status->isPayable();
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => InvoiceStatus::PAID,
            'paid_at' => now(),
        ]);
    }

    public function markAsSent(): void
    {
        if ($this->status === InvoiceStatus::DRAFT || $this->status === InvoiceStatus::PENDING) {
            $this->update(['status' => InvoiceStatus::SENT]);
        }
    }

    public function cancel(): void
    {
        if ($this->status !== InvoiceStatus::PAID) {
            $this->update(['status' => InvoiceStatus::CANCELLED]);
        }
    }

    /**
     * Update status and check for overdue
     */
    public function updateStatus(): void
    {
        if ($this->isOverdue() && $this->status !== InvoiceStatus::OVERDUE) {
            $this->update(['status' => InvoiceStatus::OVERDUE]);
        }
    }
}
