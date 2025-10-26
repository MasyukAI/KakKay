<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Category extends Model
{
    /** @phpstan-ignore-next-line */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
    ];

    /**
     * @return HasMany<Product>
     */
    /** @phpstan-ignore-next-line */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
