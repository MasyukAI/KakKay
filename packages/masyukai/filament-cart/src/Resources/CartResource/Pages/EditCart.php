<?php

namespace MasyukAI\FilamentCart\Resources\CartResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\FilamentCart\Resources\CartResource;

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
                    // Clear cart items and conditions manually without deleting the cart record
                    // This allows admins to manually add items/conditions after clearing

                    // Delete normalized cart_items records
                    DB::table('cart_items')->where('cart_id', $this->record->id)->delete();

                    // Delete normalized cart_conditions records
                    DB::table('cart_conditions')->where('cart_id', $this->record->id)->delete();

                    // Clear cart data and increment version
                    $this->record->update([
                        'items' => [],
                        'conditions' => [],
                        'metadata' => [],
                        'version' => $this->record->version + 1,
                    ]);

                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                })
                ->visible(fn () => ! $this->record->isEmpty()),
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
