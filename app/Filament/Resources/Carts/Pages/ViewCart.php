<?php

namespace App\Filament\Resources\Carts\Pages;

use App\Filament\Resources\Carts\CartResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewCart extends ViewRecord
{
    protected static string $resource = CartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon(Heroicon::OutlinedPencil),

            Actions\Action::make('clear_cart')
                ->label('Clear Cart')
                ->icon(Heroicon::OutlinedXCircle)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Clear Cart')
                ->modalDescription('Are you sure you want to clear all items from this cart? This action cannot be undone.')
                ->action(function () {
                    $this->record->update([
                        'items' => [],
                        'conditions' => [],
                    ]);
                    
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                })
                ->visible(fn () => !$this->record->isEmpty()),

            Actions\Action::make('export_cart')
                ->label('Export Cart')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('info')
                ->action(function () {
                    return response()->download(
                        storage_path('app/temp/cart_' . $this->record->identifier . '.json'),
                        'cart_' . $this->record->identifier . '.json',
                        ['Content-Type' => 'application/json']
                    );
                })
                ->before(function () {
                    // Create the export file
                    $cartData = [
                        'identifier' => $this->record->identifier,
                        'instance' => $this->record->instance,
                        'items' => $this->record->items,
                        'conditions' => $this->record->conditions,
                        'metadata' => $this->record->metadata,
                        'exported_at' => now()->toISOString(),
                    ];

                    if (!file_exists(storage_path('app/temp'))) {
                        mkdir(storage_path('app/temp'), 0755, true);
                    }

                    file_put_contents(
                        storage_path('app/temp/cart_' . $this->record->identifier . '.json'),
                        json_encode($cartData, JSON_PRETTY_PRINT)
                    );
                }),

            Actions\DeleteAction::make()
                ->icon(Heroicon::OutlinedTrash),
        ];
    }

    public function getTitle(): string
    {
        return 'Cart: ' . $this->record->identifier;
    }

    public function getSubheading(): ?string
    {
        if ($this->record->isEmpty()) {
            return 'This cart is empty';
        }

        $itemCount = $this->record->items_count;
        $totalQty = $this->record->total_quantity;
        
        return "{$itemCount} " . str('item')->plural($itemCount) . 
               " ({$totalQty} " . str('unit')->plural($totalQty) . ") • " .
               $this->record->formatted_subtotal;
    }
}