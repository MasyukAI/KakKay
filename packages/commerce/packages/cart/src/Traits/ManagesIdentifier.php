<?php

declare(strict_types=1);

namespace AIArmada\Cart\Traits;

trait ManagesIdentifier
{
    /**
     * Get the cart identifier
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
