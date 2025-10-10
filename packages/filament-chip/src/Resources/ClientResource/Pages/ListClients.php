<?php

declare(strict_types=1);

namespace MasyukAI\FilamentChip\Resources\ClientResource\Pages;

use MasyukAI\FilamentChip\Resources\ClientResource;
use MasyukAI\FilamentChip\Resources\Pages\ReadOnlyListRecords;
use Override;

final class ListClients extends ReadOnlyListRecords
{
    protected static string $resource = ClientResource::class;

    #[Override]
    public function getTitle(): string
    {
        return 'Clients';
    }

    #[Override]
    protected function getHeaderWidgets(): array
    {
        return [];
    }
}
