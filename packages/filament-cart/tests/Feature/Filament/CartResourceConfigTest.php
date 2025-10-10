<?php

declare(strict_types=1);

use Filament\Support\Icons\Heroicon;
use MasyukAI\FilamentCart\Resources\CartResource;

test('cart resource navigation uses configuration', function (): void {
    config([
        'filament-cart.navigation_group' => 'Operations',
        'filament-cart.resources.navigation_sort.carts' => 42,
    ]);

    expect(CartResource::getNavigationGroup())->toBe('Operations');
    expect(CartResource::getNavigationSort())->toBe(42);
    expect(CartResource::getNavigationIcon())->toBe(Heroicon::OutlinedShoppingCart);
});
