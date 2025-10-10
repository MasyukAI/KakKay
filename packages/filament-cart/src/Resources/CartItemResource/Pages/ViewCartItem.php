<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Resources\CartItemResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use MasyukAI\FilamentCart\Resources\CartItemResource;

final class ViewCartItem extends ViewRecord
{
    protected static string $resource = CartItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No edit/delete actions - read-only resource
        ];
    }
}
