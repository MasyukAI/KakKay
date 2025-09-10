<?php

namespace MasyukAI\FilamentCartPlugin\Resources\CartConditionResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use MasyukAI\FilamentCartPlugin\Resources\CartConditionResource;

class ViewCartCondition extends ViewRecord
{
    protected static string $resource = CartConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\EditAction::make(),
        ];
    }
}