<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Traits;

use Akaunting\Money\Money;
use MasyukAI\Cart\Models\CartItem;

trait CalculatesTotals
{
    /**
     * Get cart subtotal with conditions applied
     */
    protected function getSubtotal(): Money
    {
        $totalAmount = $this->getItems()->sum(fn (CartItem $item) => $item->getRawSubtotal());
        $currency = config('cart.money.default_currency', 'USD');

        return Money::{$currency}($totalAmount);
    }

    /**
     * Get cart subtotal without any conditions
     */
    protected function getSubtotalWithoutConditions(): Money
    {
        $totalAmount = $this->getItems()->sum(fn (CartItem $item) => $item->getRawSubtotalWithoutConditions());
        $currency = config('cart.money.default_currency', 'USD');

        return Money::{$currency}($totalAmount);
    }

    /**
     * Get cart total with all conditions applied
     */
    protected function getTotal(): Money
    {
        // Start with subtotal (items with their conditions)
        $subtotalAmount = $this->getItems()->sum(fn (CartItem $item) => $item->getRawSubtotal());

        // Apply cart-level conditions
        $cartConditions = $this->getConditions() ?? collect();
        $finalAmount = $cartConditions->reduce(function ($amount, $condition) {
            return $condition->apply($amount);
        }, $subtotalAmount);

        $currency = config('cart.money.default_currency', 'USD');

        return Money::{$currency}($finalAmount);
    }

    /**
     * Get cart subtotal (with item-level conditions applied) - returns Money object for chaining
     */
    public function subtotal(): Money
    {
        return $this->getSubtotal();
    }

    /**
     * Get cart subtotal without any conditions (raw base prices) - returns Money object for chaining
     */
    public function subtotalWithoutConditions(): Money
    {
        return $this->getSubtotalWithoutConditions();
    }

    /**
     * Get cart total with all conditions applied (item + cart-level) - returns Money object for chaining
     */
    public function total(): Money
    {
        return $this->getTotal();
    }

    /**
     * Get cart total without any conditions (raw base prices) - returns Money object for chaining
     */
    public function totalWithoutConditions(): Money
    {
        return $this->getSubtotalWithoutConditions();
    }

    /**
     * Get savings (subtotal without conditions - total) - returns Money object for chaining
     */
    public function savings(): Money
    {
        $withoutConditions = $this->getSubtotalWithoutConditions();
        $withConditions = $this->getTotal();

        $savings = $withoutConditions->subtract($withConditions);

        $currency = config('cart.money.default_currency', 'USD');

        // Return zero if savings would be negative
        return $savings->isNegative() || $savings->isZero() ? Money::{$currency}(0) : $savings;
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
     * Get raw total as float (for internal use like events and backward compatibility)
     */
    public function getRawTotal(): float
    {
        return $this->getTotal()->getAmount();
    }

    /**
     * Get raw subtotal as float (for internal use like events and backward compatibility)
     */
    public function getRawSubtotal(): float
    {
        return $this->getSubtotal()->getAmount();
    }

    /**
     * Get raw cart subtotal without any conditions (for internal use like events)
     */
    public function getRawSubtotalWithoutConditions(): float
    {
        return $this->getSubtotalWithoutConditions()->getAmount();
    }

    /**
     * Count items in cart (total quantity, shopping-cart style)
     */
    public function count(): int
    {
        return $this->getTotalQuantity();
    }
}
