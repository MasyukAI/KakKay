<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Resources\CartConditionResource\Pages;

use Filament\Resources\Pages\ListRecords;
use MasyukAI\FilamentCart\Resources\CartConditionResource;

final class ListCartConditions extends ListRecords
{
    protected static string $resource = CartConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action - read-only resource
        ];
    }
}
