<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\ToggleColumn;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('main_image')
                    ->collection('product-image-main')
                    ->disk('public')
                    ->label('Cover')
                    ->width(60),
                TextColumn::make('name')
                    ->searchable(),
                // TextColumn::make('category.name')
                //     ->searchable(),
                TextColumn::make('price')
                    ->numeric(decimalPlaces: 2)
                    ->money(currency: 'MYR', divideBy: 100)
                    ->sortable(),
                ToggleColumn::make('is_featured')
                    ->disabled(fn ($record) => !$record->is_active)
                    ->afterStateUpdated(function ($record, $state) {
                        if ($state) {
                            $record::query()
                                ->where('id', '!=', $record->id)
                                ->update(['is_featured' => false]);
                        }
                    }),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
