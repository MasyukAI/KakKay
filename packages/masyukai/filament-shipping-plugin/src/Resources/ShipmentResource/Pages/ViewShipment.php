<?php

declare(strict_types=1);

namespace MasyukAI\FilamentShippingPlugin\Resources\ShipmentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use MasyukAI\FilamentShippingPlugin\Resources\ShipmentResource;

class ViewShipment extends ViewRecord
{
    protected static string $resource = ShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}