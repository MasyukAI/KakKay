<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia; 

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // public function getFormattedPriceAttribute(): string
    // {
    //     return 'RM ' . number_format($this->price / 100, 2);
    // }

    // public function getPriceInDollarsAttribute(): float
    // {
    //     return $this->price / 100;
    // }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function primaryImage()
    {
        return $this->images()->where('is_primary', true)->first();
    }
}
