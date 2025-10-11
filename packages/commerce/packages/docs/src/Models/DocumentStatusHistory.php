<?php

declare(strict_types=1);

namespace MasyukAI\Docs\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MasyukAI\Docs\Enums\DocumentStatus;

class DocumentStatusHistory extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'document_id',
        'status',
        'notes',
        'changed_by',
    ];

    protected $casts = [
        'status' => DocumentStatus::class,
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
