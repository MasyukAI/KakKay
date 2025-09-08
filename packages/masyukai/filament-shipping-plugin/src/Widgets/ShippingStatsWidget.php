<?php

declare(strict_types=1);

namespace MasyukAI\FilamentShippingPlugin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use MasyukAI\Shipping\Models\Shipment;

class ShippingStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalShipments = Shipment::count();
        $inTransitShipments = Shipment::whereIn('status', ['dispatched', 'in_transit', 'out_for_delivery'])->count();
        $deliveredShipments = Shipment::where('status', 'delivered')->count();
        $totalRevenue = Shipment::where('status', 'delivered')->sum('cost');

        return [
            Stat::make('Total Shipments', $totalShipments)
                ->description('All time shipments')
                ->descriptionIcon('heroicon-m-truck')
                ->color('primary'),

            Stat::make('In Transit', $inTransitShipments)
                ->description('Currently shipping')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),

            Stat::make('Delivered', $deliveredShipments)
                ->description('Successfully delivered')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Revenue', 'RM ' . number_format($totalRevenue / 100, 2))
                ->description('From delivered shipments')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}