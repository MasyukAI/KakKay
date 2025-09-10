<?php

namespace MasyukAI\FilamentCartPlugin\Resources\CartConditionResource\Pages;

use Filament\Resources\Pages\ListRecords;
use MasyukAI\FilamentCartPlugin\Resources\CartConditionResource;

class ListCartConditions extends ListRecords
{
    protected static string $resource = CartConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}