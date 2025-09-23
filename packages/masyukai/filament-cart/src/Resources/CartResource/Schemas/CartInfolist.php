<?php

namespace MasyukAI\FilamentCart\Resources\CartResource\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CartInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cart Overview')
                    ->schema([
                        //                        Split::make([
                        //                            Grid::make(2)
                        //                                ->schema([
                        TextEntry::make('identifier')
                            ->label('Cart ID')
                            ->copyable()
                            ->icon(Heroicon::OutlinedIdentification),

                        TextEntry::make('instance')
                            ->label('Instance')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'default' => 'gray',
                                'wishlist' => 'warning',
                                'comparison' => 'info',
                                'quote' => 'success',
                                default => 'primary',
                            }),

                        TextEntry::make('items_count')
                            ->label('Total Items')
                            ->icon(Heroicon::OutlinedShoppingBag),

                        TextEntry::make('total_quantity')
                            ->label('Total Quantity')
                            ->icon(Heroicon::OutlinedHashtag),

                        TextEntry::make('formatted_subtotal')
                            ->label('Subtotal')
                            ->icon(Heroicon::OutlinedCurrencyDollar),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime()
                            ->icon(Heroicon::OutlinedCalendar),
                        //                                ]),
                        //                        ]),
                    ]),
                //
                //                Section::make('Cart Items')
                //                    ->schema([
                //                        RepeatableEntry::make('items')
                //                            ->label('')
                //                            ->schema([
                // //                                Split::make([
                // //                                    Grid::make(2)
                // //                                        ->schema([
                //                                            TextEntry::make('id')
                //                                                ->label('Product ID')
                //                                                ->copyable(),
                //
                //                                            TextEntry::make('name')
                //                                                ->label('Product Name')
                //                                                ->weight('semibold'),
                //
                //                                            TextEntry::make('quantity')
                //                                                ->label('Quantity')
                //                                                ->icon(Heroicon::OutlinedHashtag),
                //
                //                                            TextEntry::make('price')
                //                                                ->label('Unit Price')
                //                                                ->money('USD')
                //                                                ->icon(Heroicon::OutlinedCurrencyDollar),
                // //                                        ]),
                //
                // //                                    Grid::make(1)
                // //                                        ->schema([
                //                                            TextEntry::make('subtotal')
                //                                                ->label('Subtotal')
                //                                                ->getStateUsing(fn (array $state): string =>
                //                                                    '$' . number_format(($state['price'] ?? 0) * ($state['quantity'] ?? 0), 2)
                //                                                )
                //                                                ->color('success')
                //                                                ->weight('semibold'),
                //
                //                                            KeyValueEntry::make('attributes')
                //                                                ->label('Attributes')
                //                                                ->visible(fn (array $state): bool =>
                //                                                    !empty($state['attributes'])
                //                                                ),
                // //                                        ]),
                // //                                ]),
                //                            ])
                //                            ->contained(false)
                //                            ->visible(fn ($record): bool => !$record->isEmpty()),
                //                    ])
                //                    ->collapsible()
                //                    ->visible(fn ($record): bool => !$record->isEmpty()),

                Section::make('Cart Conditions')
                    ->schema([
                        RepeatableEntry::make('conditions')
                            ->label('')
                            ->schema([
                                //                                Grid::make(4)
                                //                                    ->schema([
                                TextEntry::make('name')
                                    ->label('Name')
                                    ->weight('semibold'),

                                TextEntry::make('type')
                                    ->label('Type')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'discount' => 'success',
                                        'tax' => 'warning',
                                        'shipping' => 'info',
                                        'fee' => 'danger',
                                        default => 'gray',
                                    }),

                                TextEntry::make('value')
                                    ->label('Value')
                                    ->icon(Heroicon::OutlinedCurrencyDollar),

                                TextEntry::make('description')
                                    ->label('Description')
                                    ->limit(50),
                                //                                    ]),
                            ])
                            ->contained(false)
                            ->visible(fn ($record): bool => is_array($record->conditions) && ! empty($record->conditions)
                            ),
                    ])
                    ->collapsible()
                    ->visible(fn ($record): bool => is_array($record->conditions) && ! empty($record->conditions)
                    ),

                Section::make('Metadata')
                    ->schema([
                        KeyValueEntry::make('metadata')
                            ->label('Additional Information'),
                    ])
                    ->collapsible()
                    ->visible(fn ($record): bool => is_array($record->metadata) && ! empty($record->metadata)
                    ),

                Section::make('Timestamps')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime()
                                    ->icon(Heroicon::OutlinedCalendar),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime()
                                    ->since()
                                    ->icon(Heroicon::OutlinedClock),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}
