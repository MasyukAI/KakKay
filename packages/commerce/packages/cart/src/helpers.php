<?php

declare(strict_types=1);

if (! function_exists('cart')) {
    /**
     * Get the Cart instance.
     */
    function cart(?string $instance = null): AIArmada\Cart\Cart
    {
        return app('cart')->instance($instance);
    }
}
