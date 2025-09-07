<?php

namespace App\Filament\Resources\Carts\Pages;

use App\Filament\Resources\Carts\CartResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditCart extends EditRecord
{
    protected static string $resource = CartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon(Heroicon::OutlinedEye),
            
            Actions\DeleteAction::make()
                ->icon(Heroicon::OutlinedTrash),

            Actions\Action::make('clear_cart')
                ->label('Clear Cart')
                ->icon(Heroicon::OutlinedXCircle)
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'items' => [],
                        'conditions' => [],
                    ]);
                    
                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->visible(fn () => !$this->record->isEmpty()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure items and conditions are arrays
        $data['items'] = $data['items'] ?? [];
        $data['conditions'] = $data['conditions'] ?? [];
        $data['metadata'] = $data['metadata'] ?? [];

        return $data;
    }
}