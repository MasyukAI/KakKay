<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Resources\ConditionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use MasyukAI\FilamentCart\Resources\ConditionResource;

final class ListConditions extends ListRecords
{
    protected static string $resource = ConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon(Heroicon::OutlinedPlus),
        ];
    }
}
