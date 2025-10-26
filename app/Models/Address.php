<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class Address extends Model
{
    /** @phpstan-ignore-next-line */
    use HasFactory, HasUuids;

    protected $fillable = [
        'addressable_type',
        'addressable_id',
        'name',
        'company',
        'street1',
        'street2',
        'city',
        'state',
        'postcode',
        'country',
        'phone',
        'type',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Create or update address for an addressable model
     *
     * @param  Model  $addressable
     * @param  array<string, mixed>  $data
     */
    public static function createOrUpdateFor($addressable, array $data, ?string $type = 'billing'): self
    {
        /** @phpstan-ignore-next-line */
        $query = $addressable->addresses();

        if ($type !== null) {
            $query = $query->where('type', $type);
        }

        $address = $query->first();

        if ($address) {
            $address->update($data);
        } else {
            $addressData = array_merge($data, [
                /** @phpstan-ignore-next-line */
                'is_primary' => $addressable->addresses()->count() === 0,
            ]);

            if ($type !== null) {
                $addressData['type'] = $type;
            }

            /** @phpstan-ignore-next-line */
            $address = $addressable->addresses()->create($addressData);
        }

        return $address;
    }

    /**
     * Get the addressable model (User, Order, etc.)
     *
     * @return MorphTo<Model>
     */
    /** @phpstan-ignore-next-line */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get formatted address string
     */
    public function getFormattedAttribute(): string
    {
        $parts = array_filter([
            $this->street1,
            $this->street2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get full address with name
     */
    public function getFullAddressAttribute(): string
    {
        $address = $this->formatted;

        if ($this->name) {
            $address = $this->name."\n".$address;
        }

        return $address;
    }

    /**
     * Scope for billing addresses
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Address>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Address>
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function billing($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('type', 'billing');
    }

    /**
     * Scope for shipping addresses
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Address>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Address>
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function shipping($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('type', 'shipping');
    }

    /**
     * Scope for primary addresses
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Address>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Address>
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function primary($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_primary', true);
    }
}
