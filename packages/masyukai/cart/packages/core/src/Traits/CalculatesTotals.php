<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Traits;

use MasyukAI\Cart\Contracts\PriceTransformerInterface;
use MasyukAI\Cart\Models\CartItem;
use MasyukAI\Cart\Support\CartMoney;

trait CalculatesTotals
{
    /**
     * Get the price transformer instance
     */
    protected function getPriceTransformer(): PriceTransformerInterface
    {
        if (function_exists('app') && app()->bound(PriceTransformerInterface::class)) {
            return app(PriceTransformerInterface::class);
        }

        // Fallback to a default transformer if not in Laravel context
        return new \MasyukAI\Cart\PriceTransformers\DecimalPriceTransformer();
    }

    /**
     * Get cart subtotal with item-level conditions applied
     */
    protected function getSubTotalMoney(): CartMoney
    {
        $totalAmount = $this->getItems()->sum(fn (CartItem $item) => $item->getRawPriceSum());
        $transformer = $this->getPriceTransformer();
        $displayAmount = $transformer->fromStorage($totalAmount);

        return CartMoney::fromAmount($displayAmount);
    }

    /**
     * Get cart subtotal without any conditions
     */
    protected function getSubTotalWithoutConditionsMoney(): CartMoney
    {
        $totalAmount = $this->getItems()->sum(fn (CartItem $item) => $item->getRawPriceSumWithoutConditions());
        $transformer = $this->getPriceTransformer();
        $displayAmount = $transformer->fromStorage($totalAmount);

        return CartMoney::fromAmount($displayAmount);
    }

    /**
     * Get cart total with all conditions applied
     */
    protected function getTotalMoney(): CartMoney
    {
        $subtotal = $this->getSubTotalMoney();
        $subtotalAmount = $subtotal->getAmount();
        $totalAmount = $this->applyCartConditions($subtotalAmount);

        return CartMoney::fromAmount($totalAmount);
    }

    /**
     * Get cart subtotal (with item-level conditions applied) - returns Money object for chaining
     */
    public function subtotal(): CartMoney
    {
        return $this->getSubTotalMoney();
    }

    /**
     * Get cart subtotal without any conditions (raw base prices) - returns Money object for chaining
     */
    public function subtotalWithoutConditions(): CartMoney
    {
        return $this->getSubTotalWithoutConditionsMoney();
    }

    /**
     * Get cart total with all conditions applied (item + cart-level) - returns Money object for chaining
     */
    public function total(): CartMoney
    {
        return $this->getTotalMoney();
    }

    /**
     * Get cart total without any conditions (raw base prices) - returns Money object for chaining
     */
    public function totalWithoutConditions(): CartMoney
    {
        return $this->getSubTotalWithoutConditionsMoney();
    }

    /**
     * Get savings (subtotal without conditions - total) - returns Money object for chaining
     */
    public function savings(): CartMoney
    {
        $withoutConditions = $this->getSubTotalWithoutConditionsMoney();
        $withConditions = $this->getTotalMoney();

        $savings = $withoutConditions->subtract($withConditions);

        // Return zero if savings would be negative
        return $savings->isNegative() || $savings->isZero() ? CartMoney::fromAmount(0) : $savings;
    }

    /**
     * Formatted methods for backward compatibility
     */
    public function subtotalFormatted(): string
    {
        return $this->subtotal()->format();
    }

    public function totalFormatted(): string
    {
        return $this->total()->format();
    }

    public function savingsFormatted(): string
    {
        return $this->savings()->format();
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
        return $this->getTotalMoney()->getAmount();
    }

    /**
     * Get raw subtotal as float (for internal use like events and backward compatibility)
     */
    public function getRawSubtotal(): float
    {
        return $this->getSubTotalMoney()->getAmount();
    }

    /**
     * Get raw cart subtotal without any conditions (for internal use like events)
     */
    public function getRawSubTotalWithoutConditions(): float
    {
        return $this->getSubTotalWithoutConditionsMoney()->getAmount();
    }

    /**
     * Count items in cart (total quantity, shopping-cart style)
     */
    public function count(): int
    {
        return $this->getTotalQuantity();
    }
}
