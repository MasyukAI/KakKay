<?php

declare(strict_types=1);

namespace MasyukAI\FilamentChip\Resources\ClientResource\Pages;

use MasyukAI\FilamentChip\Resources\ClientResource;
use MasyukAI\FilamentChip\Resources\Pages\ReadOnlyViewRecord;
use Override;

final class ViewClient extends ReadOnlyViewRecord
{
    protected static string $resource = ClientResource::class;

    #[Override]
    public function getTitle(): string
    {
        return 'Client Details';
    }
}
