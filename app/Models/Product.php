<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'integer',
        'weight' => 'integer',
        'length' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'is_digital' => 'boolean',
        'free_shipping' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category_id',
        'price',
        'weight',
        'length',
        'width',
        'height',
        'is_digital',
        'free_shipping',
        'is_featured',
        'is_active',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
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

    // Shipping-related methods
    public function getVolumetricWeight(): float
    {
        // Calculate volumetric weight using mm and convert to grams
        // Formula: (length x width x height in mm) / 5000000 = kg, then * 1000 = grams
        if (! $this->length || ! $this->width || ! $this->height) {
            return (float) $this->weight;
        }

        $volumetricWeightGrams = ($this->length * $this->width * $this->height) / 5000;

        // Return the higher of actual weight or volumetric weight
        return max((float) $this->weight, $volumetricWeightGrams);
    }

    public function getShippingWeight(): float
    {
        return $this->requiresShipping() ? $this->getVolumetricWeight() : 0;
    }

    public function getWeightInKg(): float
    {
        return $this->weight / 1000;
    }

    public function getDimensionsInCm(): array
    {
        return [
            'length' => $this->length ? $this->length / 10 : null,
            'width' => $this->width ? $this->width / 10 : null,
            'height' => $this->height ? $this->height / 10 : null,
        ];
    }

    public function isDigital(): bool
    {
        return $this->is_digital;
    }

    public function requiresShipping(): bool
    {
        return ! $this->is_digital;
    }

    public function hasFreeShipping(): bool
    {
        return $this->free_shipping;
    }

    public function getDimensions(): array
    {
        return [
            'length' => $this->length, // in mm
            'width' => $this->width,   // in mm
            'height' => $this->height, // in mm
            'weight' => $this->weight, // in grams
        ];
    }
}
