<?php

declare(strict_types=1);

namespace MasyukAI\Docs\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Backward compatibility wrapper for DocumentTemplate model
 * @deprecated Use DocumentTemplate model instead
 */
class InvoiceTemplate extends DocumentTemplate
{
    protected $table = 'document_templates';

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

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'document_template_id');
    }
}
