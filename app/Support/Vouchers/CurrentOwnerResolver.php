<?php

declare(strict_types=1);

namespace App\Support\Vouchers;

use AIArmada\Vouchers\Contracts\VoucherOwnerResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

final class CurrentOwnerResolver implements VoucherOwnerResolver
{
    public function resolve(): ?Model
    {
        $user = Auth::user();

        if (! $user instanceof Model) {
            return null;
        }

        // Multi-staff vendor support:
        // Staff members manage vouchers for their vendor
        // Returns the vendor so all staff share the same voucher pool
        if (isset($user->vendor_id) && $user->vendor_id) {
            return $user->vendor;
        }

        // Multi-store support:
        // If you have a Store model, return the store
        // This allows vouchers to be store-specific
        if (isset($user->store_id) && $user->store_id) {
            return $user->store;
        }

        // For direct voucher ownership:
        // - $user for user-owned vouchers
        // - $user->merchant for marketplace scenarios
        // - $user->organization for SaaS scenarios
        // - $user->tenant for multi-tenancy
        return $user;
    }
}
