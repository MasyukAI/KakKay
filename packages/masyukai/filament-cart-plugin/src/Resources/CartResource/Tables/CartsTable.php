<?php

namespace MasyukAI\FilamentCartPlugin\Resources\CartResource\Tables;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;

use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;


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
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->alignCenter()
                    ->sortable()
                    ->icon(Heroicon::OutlinedShoppingBag),

                TextColumn::make('total_quantity')
                    ->label('Quantity')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('formatted_subtotal')
                    ->label('Subtotal')
                    ->alignEnd()
                    ->sortable(query: fn (Builder $query, string $direction): Builder =>
                        $query->orderByRaw('JSON_EXTRACT(items, "$[*].price") ' . $direction)
                    ),

                TextColumn::make('formatted_total')
                    ->label('Total')
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_weight')
                    ->label('Weight')
                    ->alignEnd()
                    ->formatStateUsing(fn ($state): string => number_format($state, 2) . ' kg')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                    ->query(fn (Builder $query): Builder =>
                        $query->whereDate('created_at', today())
                    ),

                // Cart Item Filters
                Filter::make('product_search')
                    ->label('Search by Product ID')
                    ->query(fn (Builder $query, array $data): Builder =>
                        $data['value'] ? $query->withProduct($data['value']) : $query
                    ),

                Filter::make('item_count')
                    ->label('Items Count')
                    ->form([
                        Select::make('operator')
                            ->options([
                                '=' => 'Equals',
                                '>' => 'Greater than',
                                '<' => 'Less than',
                                '>=' => 'Greater than or equal',
                                '<=' => 'Less than or equal',
                            ])
                            ->default('='),
                        TextInput::make('count')
                            ->numeric()
                            ->placeholder('Enter item count'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['count']) && !empty($data['operator'])) {
                            return $query->withItemCount((int) $data['count'], $data['operator']);
                        }
                        return $query;
                    }),

                Filter::make('subtotal_range')
                    ->label('Subtotal Range')
                    ->form([
                        TextInput::make('min')
                            ->numeric()
                            ->placeholder('Min amount'),
                        TextInput::make('max')
                            ->numeric()
                            ->placeholder('Max amount'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['min']) && !empty($data['max'])) {
                            return $query->withSubtotalBetween((float) $data['min'], (float) $data['max']);
                        }
                        return $query;
                    }),

                // Condition-Based Filters
                Filter::make('condition_name')
                    ->label('Search by Condition Name')
                    ->query(fn (Builder $query, array $data): Builder =>
                        $data['value'] ? $query->withCondition($data['value']) : $query
                    ),

                SelectFilter::make('condition_type')
                    ->label('Condition Type')
                    ->options([
                        'static' => 'Static',
                        'dynamic' => 'Dynamic',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value']) {
                            'static' => $query->withStaticConditions(),
                            'dynamic' => $query->withDynamicConditions(),
                            default => $query,
                        };
                    }),

                Filter::make('condition_value')
                    ->label('Search by Condition Value')
                    ->query(fn (Builder $query, array $data): Builder =>
                        $data['value'] ? $query->withConditionValue($data['value']) : $query
                    ),

                TernaryFilter::make('has_conditions')
                    ->label('Has Conditions')
                    ->placeholder('All carts')
                    ->trueLabel('With conditions')
                    ->falseLabel('Without conditions')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('conditions')
                            ->where('conditions', '!=', '[]')
                            ->where('conditions', '!=', '{}'),
                        false: fn (Builder $query) => $query->where(function ($q) {
                            $q->whereNull('conditions')
                              ->orWhere('conditions', '[]')
                              ->orWhere('conditions', '{}');
                        })
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
                            $record->update([
                                'items' => [],
                                'conditions' => [],
                            ]);
                        }),
                        // ->visible(fn ($record) => !$record->isEmpty()),

                    Action::make('add_condition')
                        ->label('Add Condition')
                        ->icon(Heroicon::OutlinedPlus)
                        ->color('success')
                        ->form([
                            Select::make('condition_id')
                                ->label('Condition')
                                ->options(
                                    \MasyukAI\FilamentCartPlugin\Models\CartCondition::active()
                                        ->pluck('name', 'id')
                                        ->toArray()
                                )
                                ->required()
                                ->searchable()
                                ->preload(),

                            Select::make('target')
                                ->label('Apply to')
                                ->options([
                                    'cart' => 'Entire Cart',
                                    'item' => 'Specific Item',
                                ])
                                ->default('cart')
                                ->reactive(),

                            Select::make('item_id')
                                ->label('Item')
                                ->options(function ($get, $record) {
                                    if (!is_array($record->items)) {
                                        return [];
                                    }

                                    $options = [];
                                    foreach ($record->items as $item) {
                                        $options[$item['id']] = $item['name'] ?? $item['id'];
                                    }
                                    return $options;
                                })
                                ->visible(fn ($get) => $get('target') === 'item')
                                ->required(fn ($get) => $get('target') === 'item'),
                        ])
                        ->action(function ($record, array $data) {
                            $condition = \MasyukAI\FilamentCartPlugin\Models\CartCondition::find($data['condition_id']);
                            if (!$condition) return;

                            $conditions = $record->conditions ?? [];

                            $newCondition = [
                                'name' => $condition->name,
                                'type' => $condition->type,
                                'target' => $data['target'],
                                'value' => $condition->value,
                                'item_id' => $data['item_id'] ?? null,
                                'applied_at' => now()->toISOString(),
                            ];

                            $conditions[] = $newCondition;

                            $record->update(['conditions' => $conditions]);
                        }),

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
                        $records->each(function ($record) {
                            $record->update([
                                'items' => [],
                                'conditions' => [],
                            ]);
                        });
                    }),

                // BulkAction::make('delete_empty')
                //     ->label('Delete Empty Carts')
                //     ->icon(Heroicon::OutlinedXMark)
                //     ->color('danger')
                //     ->requiresConfirmation()
                //     ->action(function (Collection $records) {
                //         $records->filter(fn ($record) => $record->isEmpty())->each->delete();
                //     }),
            ])
            ->defaultSort('updated_at', 'desc')
            ->poll('30s')
            ->striped();
    }
}
