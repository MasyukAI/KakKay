<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Contracts;

interface PriceTransformerInterface
{
    /**
     * Transform price from input format to storage format
     */
    public function toStorage(int|float|string $price): int|float;

    /**
     * Transform from storage format to a numeric amount for calculations
     */
    public function fromStorage(int|float $storageValue): float;

    /**
     * Get the precision used by this transformer
     */
    public function getPrecision(): int;
}
