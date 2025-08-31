<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Events;

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Collections\CartCollection;

final readonly class CartMerged
{
    public function __construct(
        public Cart $targetCart,
        public Cart $sourceCart,
        public CartCollection $mergedItems,
        public string $targetInstance,
        public string $sourceInstance,
        public int $totalItemsMerged,
        public bool $hadConflicts
    ) {}
}
