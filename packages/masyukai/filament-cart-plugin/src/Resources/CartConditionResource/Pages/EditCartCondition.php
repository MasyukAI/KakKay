<?php

namespace MasyukAI\FilamentCartPlugin\Resources\CartConditionResource\Pages;

use Filament\Resources\Pages\EditRecord;
use MasyukAI\FilamentCartPlugin\Resources\CartConditionResource;

class EditCartCondition extends EditRecord
{
    protected static string $resource = CartConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ViewAction::make(),
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}