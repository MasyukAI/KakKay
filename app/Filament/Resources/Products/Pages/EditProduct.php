<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

final class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // If this record is being marked as featured
        if (! empty($data['is_featured'])) {
            // Un-feature all other records
            $record::query()
                ->where('id', '!=', $record->id)
                ->update(['is_featured' => false]);
        }

        $record->update($data);

        return $record;
    }
}
