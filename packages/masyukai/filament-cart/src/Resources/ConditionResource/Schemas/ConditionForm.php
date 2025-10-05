<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Resources\ConditionResource\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ConditionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Basic Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Condition Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('e.g., Holiday Discount 20%')
                                    ->helperText('A descriptive name for this condition'),

                                TextInput::make('display_name')
                                    ->label('Display Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g., Holiday Special')
                                    ->helperText('The name shown to users when applying this condition'),
                            ]),

                        MarkdownEditor::make('description')
                            ->label('Description')
                            ->placeholder('Describe when and how this condition should be used...')
                            ->helperText('Optional description for this condition'),
                    ]),

                Section::make('Condition Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('type')
                                    ->label('Condition Type')
                                    ->options([
                                        'discount' => 'Discount',
                                        'fee' => 'Fee',
                                        'tax' => 'Tax',
                                        'shipping' => 'Shipping',
                                        'surcharge' => 'Surcharge',
                                        'credit' => 'Credit',
                                        'adjustment' => 'Adjustment',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->helperText('The type of condition'),

                                Select::make('target')
                                    ->label('Apply To')
                                    ->options([
                                        'subtotal' => 'Cart Subtotal',
                                        'total' => 'Cart Total',
                                        'item' => 'Individual Items',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->helperText('What this condition should be applied to'),

                                TextInput::make('value')
                                    ->label('Value')
                                    ->required()
                                    ->placeholder('e.g., 20%, +15.00, -10')
                                    ->helperText('Use % for percentage, +/- for fixed amounts')
                                    ->rules([
                                        'regex:/^[+\-]?(\d+\.?\d*\%?|\d*\.\d+\%?)$/',
                                    ])
                                    ->validationMessages([
                                        'regex' => 'Value must be a number with optional +/- and % (e.g., 20%, +15.00, -10)',
                                    ]),

                                TextInput::make('order')
                                    ->label('Application Order')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Order in which conditions are applied (lower = first)'),
                            ]),
                    ]),

                Section::make('Advanced Options')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Condition')
                            ->default(true)
                            ->helperText('Whether this condition can be used to create new cart conditions'),

                        Toggle::make('is_global')
                            ->label('Global Condition')
                            ->default(false)
                            ->helperText('Automatically apply this condition to all new carts'),

                        KeyValue::make('rules')
                            ->label('Dynamic Rules (AND Logic)')
                            ->default(['min_items' => '1'])
                            ->keyLabel('Rule Type')
                            ->valueLabel('Value')
                            ->helperText('All rules must pass for the condition to be applied or removed. Leave empty for non-dynamic conditions.')
                            ->columnSpanFull()
                            ->addActionLabel('Add Rule')
                            ->keyPlaceholder('e.g., min_items')
                            ->valuePlaceholder('e.g., 3')
                            ->hint('Available rules: min_total, min_items, max_total, max_items, has_category, user_vip, specific_items, item_quantity, item_price')
                            ->hintIcon('heroicon-m-information-circle')
                            ->reorderable(false),

                        KeyValue::make('attributes')
                            ->label('Custom Attributes')
                            ->keyLabel('Attribute Name')
                            ->valueLabel('Attribute Value')
                            ->helperText('Additional attributes to store with conditions created from this record'),
                    ])
                    ->collapsible(),

                Section::make('Computed Fields (Auto-Generated)')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Placeholder::make('operator')
                                    ->label('Operator')
                                    ->content(fn ($record) => $record->operator ?? 'Not computed yet'),

                                Placeholder::make('parsed_value')
                                    ->label('Parsed Value')
                                    ->content(fn ($record) => $record->parsed_value ?? 'Not computed yet'),

                                Placeholder::make('is_discount')
                                    ->label('Is Discount')
                                    ->content(fn ($record) => $record?->is_discount ? '✓ Yes' : '✗ No'),

                                Placeholder::make('is_charge')
                                    ->label('Is Charge')
                                    ->content(fn ($record) => $record?->is_charge ? '✓ Yes' : '✗ No'),

                                Placeholder::make('is_percentage')
                                    ->label('Is Percentage')
                                    ->content(fn ($record) => $record?->is_percentage ? '✓ Yes' : '✗ No'),

                                Placeholder::make('is_dynamic')
                                    ->label('Is Dynamic')
                                    ->content(fn ($record) => $record?->is_dynamic ? '✓ Yes' : '✗ No'),

                                Placeholder::make('is_global_placeholder')
                                    ->label('Is Global')
                                    ->content(fn ($record) => $record?->is_global ? '✓ Yes' : '✗ No'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->hiddenOn('create')
                    ->description('These fields are automatically computed when you save the condition based on the value you enter.'),
            ]);
    }
}
