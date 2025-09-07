<?php

namespace MasyukAI\FilamentCartPlugin\Resources\CartResource\Pages;

use MasyukAI\FilamentCartPlugin\Resources\CartResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListCarts extends ListRecords
{
    protected static string $resource = CartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon(Heroicon::OutlinedPlus),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Can add cart statistics widgets here
        ];
    }

    public function getTitle(): string
    {
        return 'Shopping Carts';
    }

    public function getSubheading(): ?string
    {
        return 'Manage customer shopping carts and cart sessions';
    }
}