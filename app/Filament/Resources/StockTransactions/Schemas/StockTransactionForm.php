<?php

namespace App\Filament\Resources\StockTransactions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class StockTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Stock Transaction Details')
                    ->description('Record stock movements for inventory management')
                    ->components([
                        Select::make('product_id')
                            ->label('Product')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->default(Auth::id())
                            ->columnSpan(1),

                        Select::make('order_item_id')
                            ->label('Related Order Item')
                            ->relationship('orderItem', 'id')
                            ->searchable()
                            ->preload()
                            ->placeholder('Leave empty for manual adjustments')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Transaction Details')
                    ->components([
                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->columnSpan(1),

                        Select::make('type')
                            ->label('Transaction Type')
                            ->options([
                                'in' => 'Stock In (+)',
                                'out' => 'Stock Out (-)',
                            ])
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('reason')
                            ->label('Reason')
                            ->placeholder('e.g., restock, damaged, adjustment, sale')
                            ->datalist([
                                'restock',
                                'damaged',
                                'adjustment',
                                'sale',
                                'return',
                                'initial',
                            ])
                            ->columnSpanFull(),

                        Textarea::make('note')
                            ->label('Notes')
                            ->placeholder('Additional details about this transaction...')
                            ->rows(3)
                            ->columnSpanFull(),

                        DateTimePicker::make('transaction_date')
                            ->label('Transaction Date')
                            ->default(now())
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
