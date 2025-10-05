<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Resources\ConditionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use MasyukAI\FilamentCart\Resources\ConditionResource;

final class EditCondition extends EditRecord
{
    protected static string $resource = ConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
