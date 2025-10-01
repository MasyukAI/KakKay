<?php

namespace MasyukAI\FilamentCart\Resources\CartResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use MasyukAI\FilamentCart\Resources\CartItemResource;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'cartItems';

    protected static ?string $relatedResource = CartItemResource::class;

    public function table(Table $table): Table
    {
        return CartItemResource::table($table)
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
