<?php

declare(strict_types=1);

namespace MasyukAI\FilamentChip\Resources\PurchaseResource\Pages;

use MasyukAI\FilamentChip\Resources\Pages\ReadOnlyListRecords;
use MasyukAI\FilamentChip\Resources\PurchaseResource;

final class ListPurchases extends ReadOnlyListRecords
{
    protected static string $resource = PurchaseResource::class;

    public function getTitle(): string
    {
        return 'CHIP Purchases';
    }

    public function getSubheading(): ?string
    {
        return 'Review every purchase ingested from CHIP Collect with rich, read-only insights.';
    }

    // Read-only list page does not expose create actions or widgets.
}
