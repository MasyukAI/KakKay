<?php

declare(strict_types=1);

if (! function_exists('commerce_config')) {
    /**
     * Get commerce configuration value with dot notation.
     *
     * @param  string  $key  Configuration key (e.g., 'cart.storage', 'chip.api_key')
     * @param  mixed  $default  Default value if key doesn't exist
     */
    function commerce_config(string $key, mixed $default = null): mixed
    {
        return config($key, $default);
    }
}
