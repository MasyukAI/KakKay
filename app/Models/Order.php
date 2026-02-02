<?php

declare(strict_types=1);

namespace App\Models;

use AIArmada\Orders\Models\Order as BaseOrder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * App Order model - extends the package model for demo purposes.
 *
 * This alias allows existing code to reference App\Models\Order
 * while using the full-featured package implementation.
 */
final class Order extends BaseOrder
{
    /**
     * @return MorphMany<Shipment, $this>
     */
    public function shipments(): MorphMany
    {
        return $this->morphMany(Shipment::class, 'shippable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \AIArmada\Orders\Models\OrderItem>
     */
    public function getOrderItemsAttribute(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->items;
    }

    public function getAddressAttribute(): ?\AIArmada\Orders\Models\OrderAddress
    {
        return $this->shippingAddress ?? $this->billingAddress;
    }

    public function getFormattedTotalAttribute(): string
    {
        return $this->formatMoney($this->grand_total);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getCheckoutFormDataAttribute(): ?array
    {
        $metadata = is_array($this->metadata) ? $this->metadata : [];

        return $metadata['checkout_form_data'] ?? null;
    }

    public function getDeliveryMethodAttribute(): ?string
    {
        $metadata = is_array($this->metadata) ? $this->metadata : [];

        return $metadata['delivery_method'] ?? null;
    }
}
