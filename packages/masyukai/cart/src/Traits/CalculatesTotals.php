<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Traits;

use MasyukAI\Cart\Models\CartItem;

trait CalculatesTotals
{
    /**
     * Get cart subtotal with item-level conditions applied (raw value)
     *
     * @internal For internal calculations only
     */
    protected function getSubTotal(): float
    {
        return $this->getItems()->sum(fn (CartItem $item) => $item->getRawPriceSumWithConditions());
    }

    /**
     * Get cart subtotal without any conditions (raw value)
     *
     * @internal For internal calculations only
     */
    protected function getSubTotalWithoutConditions(): float
    {
        return $this->getItems()->sum(fn (CartItem $item) => $item->getRawPriceSumWithoutConditions());
    }

    /**
     * Get raw cart subtotal without any conditions (for internal use like events)
     */
    public function getRawSubTotalWithoutConditions(): float
    {
        return $this->getSubTotalWithoutConditions();
    }

    /**
     * Get cart total with all conditions applied (raw value)
     *
     * @internal For internal calculations only
     */
    protected function getTotal(): float
    {
        $subtotal = $this->getSubTotal();

        return $this->applyCartConditions($subtotal);
    }

    /**
     * Get formatted cart subtotal (with item-level conditions applied)
     */
    public function subtotal(): string|int|float
    {
        return $this->formatPriceValue($this->getSubTotal());
    }

    /**
     * Get formatted cart subtotal without any conditions (raw base prices)
     */
    public function subtotalWithoutConditions(): string|int|float
    {
        return $this->formatPriceValue($this->getRawSubTotalWithoutConditions());
    }

    /**
     * Get formatted cart total with all conditions applied (item + cart-level)
     */
    public function total(): string|int|float
    {
        return $this->formatPriceValue($this->getTotal());
    }

    /**
     * Get formatted cart total without any conditions (raw base prices)
     */
    public function totalWithoutConditions(): string|int|float
    {
        return $this->formatPriceValue($this->getRawSubTotalWithoutConditions());
    }

    /**
     * Get formatted savings (subtotal without conditions - total)
     */
    public function savings(): string|int|float
    {
        $savings = $this->getRawSubTotalWithoutConditions() - $this->getTotal();

        return $this->formatPriceValue($savings > 0 ? $savings : 0);
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
     * Get raw total as float (for internal use like events)
     */
    public function getRawTotal(): float
    {
        return $this->getTotal();
    }

    /**
     * Get raw subtotal as float (for internal use like events)
     */
    public function getRawSubtotal(): float
    {
        return $this->getSubTotal();
    }

    /**
     * Count items in cart (total quantity, shopping-cart style)
     */
    public function count(): int
    {
        return $this->getTotalQuantity();
    }
}
