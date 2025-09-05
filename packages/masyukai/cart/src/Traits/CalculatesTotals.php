<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Traits;

use MasyukAI\Cart\Models\CartItem;

trait CalculatesTotals
{
    /**
     * Get cart subtotal (before conditions)
     */
    public function getSubTotal(): float
    {
        return $this->getItems()->sum(fn (CartItem $item) => $item->getPriceSum());
    }

    /**
     * Get cart subtotal (alias for more intuitive API)
     */
    public function subtotal(): float
    {
        return $this->getSubTotal();
    }

    /**
     * Get cart subtotal without any conditions (base price only)
     */
    public function getSubTotalWithoutConditions(): float
    {
        return $this->getSubTotal(); // This is already the subtotal without conditions
    }

    /**
     * Get cart subtotal with item conditions applied
     */
    public function getSubTotalWithConditions(): float
    {
        return $this->getItems()->sum(fn (CartItem $item) => $item->getPriceSumWithConditions());
    }

    /**
     * Get cart total with all conditions applied
     */
    public function getTotal(): float
    {
        $subtotal = $this->getSubTotalWithConditions();

        return $this->applyCartConditions($subtotal);
    }

    /**
     * Get cart total (alias for more intuitive API)
     */
    public function total(): float
    {
        return $this->getTotal();
    }

    /**
     * Get total quantity of all items
     */
    public function getTotalQuantity(): int
    {
        return $this->getItems()->sum('quantity');
    }

    /**
     * Get count of unique items in cart
     */
    public function countItems(): int
    {
        return $this->getItems()->count();
    }

    /**
     * Count items in cart (total quantity, shopping-cart style)
     */
    public function count(): int
    {
        return $this->getTotalQuantity();
    }
}
