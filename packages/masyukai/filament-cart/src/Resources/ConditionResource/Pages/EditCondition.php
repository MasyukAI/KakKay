<?php

namespace MasyukAI\FilamentCart\Resources\ConditionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use MasyukAI\FilamentCart\Resources\ConditionResource;

class EditCondition extends EditRecord
{
    protected static string $resource = ConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
