<?php

declare(strict_types=1);

namespace MasyukAI\FilamentShippingPlugin\Resources\ShipmentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use MasyukAI\FilamentShippingPlugin\Resources\ShipmentResource;

class EditShipment extends EditRecord
{
    protected static string $resource = ShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}