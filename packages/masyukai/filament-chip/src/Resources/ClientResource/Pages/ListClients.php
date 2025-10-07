<?php

declare(strict_types=1);

namespace MasyukAI\FilamentChip\Resources\ClientResource\Pages;

use MasyukAI\FilamentChip\Resources\ClientResource;
use MasyukAI\FilamentChip\Resources\Pages\ReadOnlyListRecords;

final class ListClients extends ReadOnlyListRecords
{
    protected static string $resource = ClientResource::class;

    public function getTitle(): string
    {
        return 'Clients';
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }
}
