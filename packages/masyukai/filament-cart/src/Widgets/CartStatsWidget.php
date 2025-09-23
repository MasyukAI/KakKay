<?php

namespace MasyukAI\FilamentCart\Widgets;

use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use MasyukAI\FilamentCart\Models\Cart;

class CartStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Carts', Cart::count())
                ->description('All cart sessions')
                ->descriptionIcon(Heroicon::OutlinedShoppingCart)
                ->color('primary'),

            Stat::make('Active Carts', Cart::notEmpty()->count())
                ->description('Carts with items')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->color('success'),

            Stat::make('Total Items', $this->getTotalItemsCount())
                ->description('Across all carts')
                ->descriptionIcon(Heroicon::OutlinedShoppingBag)
                ->color('info'),

            Stat::make('Cart Value', '$'.number_format($this->getTotalCartValue(), 2))
                ->description('Total potential revenue')
                ->descriptionIcon(Heroicon::OutlinedCurrencyDollar)
                ->color('warning'),
        ];
    }

    private function getTotalItemsCount(): int
    {
        return Cart::notEmpty()
            ->get()
            ->sum('total_quantity');
    }

    private function getTotalCartValue(): float
    {
        return Cart::notEmpty()
            ->get()
            ->sum('subtotal');
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
