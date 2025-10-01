<?php

namespace MasyukAI\FilamentCart\Resources\CartResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use MasyukAI\FilamentCart\Actions\ApplyConditionAction;
use MasyukAI\FilamentCart\Actions\RemoveConditionAction;
use MasyukAI\FilamentCart\Resources\CartConditionResource;

class ConditionsRelationManager extends RelationManager
{
    protected static string $relationship = 'cartConditions';

    protected static ?string $relatedResource = CartConditionResource::class;

    public function table(Table $table): Table
    {
        return CartConditionResource::table($table)
            ->headerActions([
                ApplyConditionAction::make(),
                ApplyConditionAction::makeCustom(),
                RemoveConditionAction::makeClearByType(),
                RemoveConditionAction::makeClearAll(),
            ])
            ->recordActions([
                RemoveConditionAction::make(),
            ]);
    }
}
