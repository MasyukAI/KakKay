<?php

declare(strict_types=1);

namespace MasyukAI\Docs\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceTemplate extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'view_name',
        'is_default',
        'settings',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'settings' => 'array',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Scope to get default template
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true)->first();
    }

    /**
     * Set this template as default
     */
    public function setAsDefault(): void
    {
        // Remove default from all other templates
        static::where('id', '!=', $this->id)->update(['is_default' => false]);

        // Set this as default
        $this->update(['is_default' => true]);
    }
}
