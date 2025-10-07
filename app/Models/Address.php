<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class Address extends Model
{
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
     */
    public static function createOrUpdateFor($addressable, array $data, ?string $type = 'billing'): self
    {
        $query = $addressable->addresses();

        if ($type !== null) {
            $query = $query->where('type', $type);
        }

        $address = $query->first();

        if ($address) {
            $address->update($data);
        } else {
            $addressData = array_merge($data, [
                'is_primary' => $addressable->addresses()->count() === 0,
            ]);

            if ($type !== null) {
                $addressData['type'] = $type;
            }

            $address = $addressable->addresses()->create($addressData);
        }

        return $address;
    }

    /**
     * Get the addressable model (User, Order, etc.)
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for billing addresses
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function billing($query)
    {
        return $query->where('type', 'billing');
    }

    /**
     * Scope for shipping addresses
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function shipping($query)
    {
        return $query->where('type', 'shipping');
    }

    /**
     * Scope for primary addresses
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function primary($query)
    {
        return $query->where('is_primary', true);
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
}
