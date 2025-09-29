<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    protected static ?string $relatedResource = OrderResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Quantity')
                    ->alignCenter(),
                TextColumn::make('unit_price')
                    ->label('Unit Price')
                    ->formatStateUsing(fn ($state) => 'RM '.number_format($state / 100, 2))
                    ->alignRight(),
                TextColumn::make('total_price')
                    ->label('Total')
                    ->formatStateUsing(fn ($record) => 'RM '.number_format(($record->unit_price * $record->quantity) / 100, 2))
                    ->alignRight(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
