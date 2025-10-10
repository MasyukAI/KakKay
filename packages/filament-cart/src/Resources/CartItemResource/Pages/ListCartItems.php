<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Resources\CartItemResource\Pages;

use Filament\Resources\Pages\ListRecords;
use MasyukAI\FilamentCart\Resources\CartItemResource;

final class ListCartItems extends ListRecords
{
    protected static string $resource = CartItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action - read-only resource
        ];
    }
}
