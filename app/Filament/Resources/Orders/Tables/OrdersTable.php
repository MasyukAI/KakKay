<?php

namespace App\Filament\Resources\Orders\Tables;

use Akaunting\Money\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('address.name')
                    ->searchable(),
                TextColumn::make('delivery_method')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('items_count')
                    ->label('Items Count')
                    ->formatStateUsing(function ($record) {
                        // Try to count from orderItems relationship first
                        if ($record->orderItems && $record->orderItems->isNotEmpty()) {
                            return $record->orderItems->sum('quantity');
                        }
                        // Fallback to cart_items JSON data
                        elseif (! empty($record->cart_items)) {
                            return collect($record->cart_items)->sum('quantity');
                        }

                        return 0;
                    })
                    ->alignCenter()
                    ->sortable(false),
                TextColumn::make('total')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => Money::MYR($state))
                    ->sortable(),
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
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
