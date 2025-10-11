<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Vouchers\Data;

readonly class VoucherValidationResult
{
    public function __construct(
        public bool $isValid,
        public ?string $reason = null,
        public ?array $details = null,
    ) {}

    public static function valid(): self
    {
        return new self(isValid: true);
    }

    public static function invalid(string $reason, array $details = []): self
    {
        return new self(
            isValid: false,
            reason: $reason,
            details: $details
        );
    }
}
