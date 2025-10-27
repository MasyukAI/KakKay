<?php

declare(strict_types=1);

namespace AIArmada\FilamentVouchers\Models;

use AIArmada\FilamentVouchers\Support\Integrations\FilamentCartBridge;
use AIArmada\Vouchers\Models\VoucherUsage as BaseVoucherUsage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

final class VoucherUsage extends BaseVoucherUsage
{
    public function cartSnapshot(): BelongsTo
    {
        $cartModel = app(FilamentCartBridge::class)->getCartModel();

        if (! $cartModel) {
            throw new LogicException('Cart integration is unavailable. Install aiarmada/filament-cart to enable cart relations.');
        }

        /** @var class-string<\Illuminate\Database\Eloquent\Model> $cartModel */
        return $this->belongsTo($cartModel, 'cart_identifier', 'identifier');
    }

    protected function cartUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                $identifier = $this->getAttribute('cart_identifier');

                return app(FilamentCartBridge::class)->resolveCartUrl(is_string($identifier) ? $identifier : null);
            }
        );
    }
}
