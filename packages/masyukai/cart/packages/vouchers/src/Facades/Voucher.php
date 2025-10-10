<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Vouchers\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \MasyukAI\Cart\Vouchers\Data\VoucherData|null find(string $code)
 * @method static \MasyukAI\Cart\Vouchers\Data\VoucherData create(array $data)
 * @method static \MasyukAI\Cart\Vouchers\Data\VoucherData update(string $code, array $data)
 * @method static bool delete(string $code)
 * @method static \MasyukAI\Cart\Vouchers\Data\VoucherValidationResult validate(string $code, mixed $cart)
 * @method static bool isValid(string $code)
 * @method static bool canBeUsedBy(string $code, string $userIdentifier)
 * @method static int getRemainingUses(string $code)
 * @method static void recordUsage(string $code, string $userIdentifier, \Akaunting\Money\Money $discountAmount)
 * @method static \Illuminate\Support\Collection getUsageHistory(string $code)
 *
 * @see \MasyukAI\Cart\Vouchers\Services\VoucherService
 */
class Voucher extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'voucher';
    }
}
