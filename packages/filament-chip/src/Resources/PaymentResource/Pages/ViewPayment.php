<?php

declare(strict_types=1);

namespace MasyukAI\FilamentChip\Resources\PaymentResource\Pages;

use MasyukAI\FilamentChip\Resources\Pages\ReadOnlyViewRecord;
use MasyukAI\FilamentChip\Resources\PaymentResource;
use Override;

final class ViewPayment extends ReadOnlyViewRecord
{
    protected static string $resource = PaymentResource::class;

    #[Override]
    public function getTitle(): string
    {
        return 'Payment Details';
    }
}
