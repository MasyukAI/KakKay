<?php

namespace App\Filament\Resources\Products\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Asmit\ResizedColumn\HasResizableColumn;
use App\Filament\Resources\Products\ProductResource;

class ListProducts extends ListRecords
{
    use HasResizableColumn;
    
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
