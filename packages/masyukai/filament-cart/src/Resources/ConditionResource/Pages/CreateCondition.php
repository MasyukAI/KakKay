<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Resources\ConditionResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use MasyukAI\FilamentCart\Resources\ConditionResource;

final class CreateCondition extends CreateRecord
{
    protected static string $resource = ConditionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // The model's computeDerivedFields() method will run automatically
        // via the saving event, but we ensure the data is clean here
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
