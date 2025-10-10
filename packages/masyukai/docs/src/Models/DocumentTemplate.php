<?php

declare(strict_types=1);

namespace MasyukAI\Docs\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentTemplate extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'view_name',
        'document_type',
        'is_default',
        'settings',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'settings' => 'array',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Scope to get default template
     */
    public function scopeDefault($query, ?string $documentType = null)
    {
        $query = $query->where('is_default', true);
        
        if ($documentType) {
            $query->where('document_type', $documentType);
        }
        
        return $query->first();
    }

    /**
     * Set this template as default
     */
    public function setAsDefault(): void
    {
        // Remove default from all other templates of the same type
        static::where('id', '!=', $this->id)
            ->where('document_type', $this->document_type)
            ->update(['is_default' => false]);

        // Set this as default
        $this->update(['is_default' => true]);
    }
}
