<?php

declare(strict_types=1);

namespace MasyukAI\FilamentShippingPlugin\Resources\ShipmentResource\Pages;

use Filament\Resources\Pages\ListRecords;
use MasyukAI\FilamentShippingPlugin\Resources\ShipmentResource;

class ListShipments extends ListRecords
{
    protected static string $resource = ShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}