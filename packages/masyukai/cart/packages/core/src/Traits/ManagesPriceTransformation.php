<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Traits;

use MasyukAI\Cart\Contracts\PriceTransformerInterface;

/**
 * Trait for handling price transformation between storage and display formats
 */
trait ManagesPriceTransformation
{
    /**
     * Get the price transformer instance
     */
    protected function getPriceTransformer(): ?PriceTransformerInterface
    {
        if (app()->bound(PriceTransformerInterface::class)) {
            return app(PriceTransformerInterface::class);
        }

        return null;
    }

    /**
     * Transform price for storage (e.g., dollars to cents)
     */
    protected function transformForStorage(float $price): float
    {
        $transformer = $this->getPriceTransformer();
        
        if ($transformer) {
            return $transformer->toStorage($price);
        }

        return $price;
    }

    /**
     * Transform price from storage for display (e.g., cents to dollars)
     */
    protected function transformFromStorage(float $price): float
    {
        $transformer = $this->getPriceTransformer();
        
        if ($transformer) {
            return $transformer->fromStorage($price);
        }

        return $price;
    }

    /**
     * Get the precision for price operations
     */
    protected function getPricePrecision(): int
    {
        $transformer = $this->getPriceTransformer();
        
        if ($transformer) {
            return $transformer->getPrecision();
        }

        // Fallback to config or default
        return $this->config['money']['default_precision'] ?? config('cart.money.default_precision', 2);
    }

    /**
     * Check if price transformer is available
     */
    protected function hasPriceTransformer(): bool
    {
        return $this->getPriceTransformer() !== null;
    }
}