<?php

namespace MasyukAI\FilamentCart\Resources\CartResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use MasyukAI\FilamentCart\Resources\CartResource;

class CreateCart extends CreateRecord
{
    protected static string $resource = CartResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure items and conditions are arrays
        $data['items'] = $data['items'] ?? [];
        $data['conditions'] = $data['conditions'] ?? [];
        $data['metadata'] = $data['metadata'] ?? [];

        return $data;
    }
}
