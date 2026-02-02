<?php

declare(strict_types=1);

namespace App\Models;

use AIArmada\Pricing\Contracts\Priceable;
use AIArmada\Pricing\Models\Price;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class Product extends Model implements HasMedia, Priceable
{
    /** @phpstan-ignore-next-line */
    use HasFactory, HasUuids, InteractsWithMedia;

    /**
     * Cast attributes to native types - aligned with commerce package schema.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_featured' => 'boolean',
        'is_taxable' => 'boolean',
        'requires_shipping' => 'boolean',
        'price' => 'integer',
        'compare_price' => 'integer',
        'cost' => 'integer',
        'weight' => 'float',
        'length' => 'float',
        'width' => 'float',
        'height' => 'float',
        'metadata' => 'array',
        'published_at' => 'datetime',
    ];

    /**
     * Fillable attributes - aligned with commerce package schema.
     *
     * @var list<string>
     */
    protected $fillable = [
        'owner_type',
        'owner_id',
        'name',
        'slug',
        'description',
        'short_description',
        'sku',
        'barcode',
        'type',
        'status',
        'visibility',
        'price',
        'compare_price',
        'cost',
        'currency',
        'weight',
        'length',
        'width',
        'height',
        'weight_unit',
        'dimension_unit',
        'is_featured',
        'is_taxable',
        'requires_shipping',
        'meta_title',
        'meta_description',
        'tax_class',
        'metadata',
        'published_at',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getVolumetricWeight(): float
    {
        if (! $this->length || ! $this->width || ! $this->height) {
            return (float) ($this->weight ?? 0);
        }

        $volumetricWeightGrams = ($this->length * $this->width * $this->height) / 5000;

        return max((float) ($this->weight ?? 0), $volumetricWeightGrams);
    }

    public function getShippingWeight(): float
    {
        return $this->requiresShipping() ? $this->getVolumetricWeight() : 0;
    }

    public function getWeightInKg(): float
    {
        return ($this->weight ?? 0) / 1000;
    }

    /**
     * @return array<string, float|null>
     */
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
        return ! $this->requires_shipping;
    }

    public function requiresShipping(): bool
    {
        return (bool) ($this->requires_shipping ?? true);
    }

    /**
     * @return array<string, float|null>
     */
    public function getDimensions(): array
    {
        return [
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'weight' => $this->weight,
        ];
    }

    /**
     * Prices from the pricing package (morphMany relationship).
     *
     * @return MorphMany<Price, $this>
     */
    public function prices(): MorphMany
    {
        return $this->morphMany(Price::class, 'priceable');
    }

    /**
     * Priceable: Get the unique identifier for the priceable item.
     */
    public function getBuyableIdentifier(): string
    {
        return (string) $this->id;
    }

    /**
     * Priceable: Get the base price in cents from prices table.
     * Falls back to Product.price if no price record exists.
     */
    public function getBasePrice(): int
    {
        $priceRecord = $this->getActivePriceRecord();

        if ($priceRecord) {
            return (int) $priceRecord->amount;
        }

        return (int) ($this->price ?? 0);
    }

    /**
     * Priceable: Get the compare price (original/MSRP) in cents from prices table.
     * Falls back to Product.compare_price if no price record exists.
     */
    public function getComparePrice(): ?int
    {
        $priceRecord = $this->getActivePriceRecord();

        if ($priceRecord && $priceRecord->compare_amount) {
            return (int) $priceRecord->compare_amount;
        }

        return $this->compare_price ? (int) $this->compare_price : null;
    }

    /**
     * Priceable: Check if the item is on sale.
     */
    public function isOnSale(): bool
    {
        $comparePrice = $this->getComparePrice();
        $basePrice = $this->getBasePrice();

        return $comparePrice !== null && $comparePrice > $basePrice;
    }

    /**
     * Priceable: Get the discount percentage if on sale.
     */
    public function getDiscountPercentage(): ?float
    {
        if (! $this->isOnSale()) {
            return null;
        }

        $comparePrice = $this->getComparePrice();
        $basePrice = $this->getBasePrice();

        if (! $comparePrice || $comparePrice <= 0) {
            return null;
        }

        return round((($comparePrice - $basePrice) / $comparePrice) * 100, 2);
    }

    /**
     * Get the active price record from the default price list.
     */
    protected function getActivePriceRecord(): ?Price
    {
        return $this->prices()
            ->whereHas('priceList', fn ($q) => $q->where('is_default', true)->where('is_active', true))
            ->active()
            ->forQuantity(1)
            ->orderBy('min_quantity', 'desc')
            ->first();
    }
}
