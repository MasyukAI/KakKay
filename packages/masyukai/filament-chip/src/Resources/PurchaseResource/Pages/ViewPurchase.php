<?php

declare(strict_types=1);

namespace MasyukAI\FilamentChip\Resources\PurchaseResource\Pages;

use Filament\Support\Icons\Heroicon;
use MasyukAI\FilamentChip\Resources\Pages\ReadOnlyViewRecord;
use MasyukAI\FilamentChip\Resources\PurchaseResource;

final class ViewPurchase extends ReadOnlyViewRecord
{
    protected static string $resource = PurchaseResource::class;

    public function getTitle(): string
    {
        $record = $this->getRecord();

        return sprintf('Purchase %s', $record->reference ?? $record->getKey());
    }

    public function getHeadingIcon(): Heroicon|int|string|null
    {
        return Heroicon::OutlinedRectangleStack;
    }
}
