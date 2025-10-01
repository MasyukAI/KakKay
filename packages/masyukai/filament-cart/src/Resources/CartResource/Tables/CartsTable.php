<?php

namespace MasyukAI\FilamentCart\Resources\CartResource\Tables;

use Akaunting\Money\Money;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use MasyukAI\Cart\Facades\Cart;

class CartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('identifier')
                    ->label('Cart ID')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->tooltip('Click to copy'),

                TextColumn::make('instance')
                    ->label('Instance')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'default' => 'gray',
                        'wishlist' => 'warning',
                        'comparison' => 'info',
                        'quote' => 'success',
                        default => 'primary',
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label('Items')
                    // ->alignCenter()
                    ->sortable()
                    ->icon(Heroicon::OutlinedShoppingBag),

                TextColumn::make('total_quantity')
                    ->label('Quantity')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    // ->alignEnd()
                    ->formatStateUsing(fn ($state) => Money::MYR($state))
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderByRaw('JSON_EXTRACT(items, "$[*].price") '.$direction)
                    ),

                TextColumn::make('total')
                    ->label('Total')
                    ->alignEnd()
                    ->formatStateUsing(fn ($state) => Money::MYR($state))
                    ->sortable(),

                TextColumn::make('version')
                    ->label('Version')
                    ->alignCenter()
                    ->sortable(),

                // IconColumn::make('isEmpty')
                //     ->label('Status')
                //     ->boolean()
                //     ->trueIcon(Heroicon::OutlinedXCircle)
                //     ->falseIcon(Heroicon::OutlinedCheckCircle)
                //     ->trueColor('danger')
                //     ->falseColor('success'),
                // ->tooltip(fn ($record): string => $record->isEmpty() ? 'Empty Cart' : 'Has Items'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('instance')
                    ->options([
                        'default' => 'Default',
                        'wishlist' => 'Wishlist',
                        'comparison' => 'Comparison',
                        'quote' => 'Quote',
                    ])
                    ->multiple(),

                Filter::make('has_items')
                    ->label('Has Items')
                    ->query(fn (Builder $query): Builder => $query->notEmpty()),

                Filter::make('recent')
                    ->label('Recent (7 days)')
                    ->query(fn (Builder $query): Builder => $query->recent()),

                Filter::make('created_today')
                    ->label('Created Today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today())
                    ),
            ])
            ->recordActions([
                ViewAction::make()
                    ->icon(Heroicon::OutlinedEye),

                EditAction::make()
                    ->icon(Heroicon::OutlinedPencil),

                ActionGroup::make([
                    Action::make('clear_cart')
                        ->label('Clear Cart')
                        ->icon(Heroicon::OutlinedTrash)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            // Clear cart items and conditions manually without deleting the cart record
                            // This allows admins to manually add items/conditions after clearing

                            // Delete normalized cart_items records
                            DB::table('cart_items')->where('cart_id', $record->id)->delete();

                            // Delete normalized cart_conditions records
                            DB::table('cart_conditions')->where('cart_id', $record->id)->delete();

                            // Clear cart data and increment version
                            $record->update([
                                'items' => [],
                                'conditions' => [],
                                'metadata' => [],
                                'version' => $record->version + 1,
                            ]);
                        }),
                    // ->visible(fn ($record) => !$record->isEmpty()),

                    Action::make('view_items')
                        ->label('View Items')
                        ->icon(Heroicon::OutlinedListBullet)
                        ->url(fn ($record) => route('filament.admin.resources.carts.view', $record)),
                    // ->visible(fn ($record) => !$record->isEmpty()),

                    DeleteAction::make()
                        ->icon(Heroicon::OutlinedXMark),
                ])
                    ->icon(Heroicon::OutlinedEllipsisVertical)
                    ->tooltip('More actions'),
            ])
            ->toolbarActions([
                BulkAction::make('clear_selected')
                    ->label('Clear Selected Carts')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $cartIds = $records->pluck('id')->toArray();
                        DB::table('cart_items')->whereIn('cart_id', $cartIds)->delete();
                        DB::table('cart_conditions')->whereIn('cart_id', $cartIds)->delete();
                        $records->each(function ($record) {
                            $record->update([
                                'items' => [],
                                'conditions' => [],
                                'metadata' => [],
                                'version' => $record->version + 1,
                            ]);
                        });
                    }),

                BulkAction::make('delete_selected')
                    ->label('Delete Selected Carts')
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $cartIds = $records->pluck('id')->toArray();
                        // Delete normalized cart_items and cart_conditions for selected carts
                        DB::table('cart_items')->whereIn('cart_id', $cartIds)->delete();
                        DB::table('cart_conditions')->whereIn('cart_id', $cartIds)->delete();
                        // Delete the carts themselves
                        $records->each->delete();
                    }),
            ])
            ->defaultSort('updated_at', 'desc')
            ->poll('30s')
            ->striped();
    }
}
