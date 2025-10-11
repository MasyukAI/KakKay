<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Vouchers\Exceptions;

class VoucherUsageLimitException extends VoucherException
{
    public static function globalLimit(string $code): self
    {
        return new self("Voucher '{$code}' has reached its usage limit.");
    }

    public static function userLimit(string $code): self
    {
        return new self("You have already used voucher '{$code}' the maximum number of times.");
    }
}
