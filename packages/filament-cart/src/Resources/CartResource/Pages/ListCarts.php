<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Resources\CartResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use MasyukAI\FilamentCart\Resources\CartResource;

final class ListCarts extends ListRecords
{
    protected static string $resource = CartResource::class;

    public function getTitle(): string
    {
        return 'Shopping Carts';
    }

    public function getSubheading(): string
    {
        return 'Manage customer shopping carts and cart sessions';
    }

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
}
