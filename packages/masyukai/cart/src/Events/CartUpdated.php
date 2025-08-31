<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Events;

use MasyukAI\Cart\Collections\CartCollection;
use MasyukAI\Cart\Collections\CartConditionCollection;

final readonly class CartUpdated
{
    public function __construct(
        public CartCollection $items,
        public CartConditionCollection $conditions,
        public string $instance,
        public float $total,
    ) {}
}
