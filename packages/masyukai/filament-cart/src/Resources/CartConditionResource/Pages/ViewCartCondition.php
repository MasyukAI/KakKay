<?php

namespace MasyukAI\FilamentCart\Resources\CartConditionResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use MasyukAI\FilamentCart\Resources\CartConditionResource;

class ViewCartCondition extends ViewRecord
{
    protected static string $resource = CartConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No edit/delete actions - read-only resource
        ];
    }
}
