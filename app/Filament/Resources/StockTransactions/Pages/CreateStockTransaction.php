<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockTransactions\Pages;

use App\Filament\Resources\StockTransactions\StockTransactionResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateStockTransaction extends CreateRecord
{
    protected static string $resource = StockTransactionResource::class;
}
