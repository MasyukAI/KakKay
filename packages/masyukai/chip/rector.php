<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorLaravel\Set\LaravelSetProvider;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/routes',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withSetProviders(LaravelSetProvider::class)
    ->withComposerBased(laravel: true);
