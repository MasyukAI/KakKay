<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Resources\ConditionResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use MasyukAI\FilamentCart\Models\Condition;
use MasyukAI\FilamentCart\Resources\ConditionResource;

final class CreateCondition extends CreateRecord
{
    protected static string $resource = ConditionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['rules'] = Condition::normalizeRulesDefinition(
            $data['rules'] ?? null,
            ! empty($data['rules']['factory_keys'] ?? [])
        );

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
