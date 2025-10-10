<?php

declare(strict_types=1);

namespace MasyukAI\Docs\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MasyukAI\Docs\Enums\DocumentStatus;

/**
 * Backward compatibility wrapper for Document model
 * @deprecated Use Document model instead
 */
class Invoice extends Document
{
    protected $table = 'documents';

    protected static function booted(): void
    {
        parent::booted();
        
        static::addGlobalScope('invoiceType', function ($builder) {
            $builder->where('document_type', 'invoice');
        });
        
        static::creating(function ($model) {
            if (! isset($model->document_type)) {
                $model->document_type = 'invoice';
            }
        });
    }

    // Backward compatibility accessors
    public function getInvoiceNumberAttribute(): ?string
    {
        return $this->document_number;
    }

    public function setInvoiceNumberAttribute(?string $value): void
    {
        $this->attributes['document_number'] = $value;
    }

    public function getInvoiceTemplateIdAttribute(): ?string
    {
        return $this->document_template_id;
    }

    public function setInvoiceTemplateIdAttribute(?string $value): void
    {
        $this->attributes['document_template_id'] = $value;
    }

    public function getInvoiceableTypeAttribute(): ?string
    {
        return $this->documentable_type;
    }

    public function setInvoiceableTypeAttribute(?string $value): void
    {
        $this->attributes['documentable_type'] = $value;
    }

    public function getInvoiceableIdAttribute(): ?string
    {
        return $this->documentable_id;
    }

    public function setInvoiceableIdAttribute(?string $value): void
    {
        $this->attributes['documentable_id'] = $value;
    }

    public function invoiceable()
    {
        return $this->documentable();
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(InvoiceTemplate::class, 'document_template_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(InvoiceStatusHistory::class, 'document_id');
    }
}
