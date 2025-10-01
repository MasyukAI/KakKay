<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\FilamentCart\Models\Cart as CartModel;
use MasyukAI\FilamentCart\Models\Condition;
use MasyukAI\FilamentCart\Services\RuleConverter;

class ApplyConditionAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'applyCondition';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Apply Condition')
            ->icon(Heroicon::OutlinedTag)
            ->color('primary')
            ->modalHeading('Apply Condition to Cart')
            ->modalDescription('Select a condition to apply to this cart.')
            ->modalSubmitActionLabel('Apply Condition')
            ->schema([
                Select::make('condition_id')
                    ->label('Condition')
                    ->placeholder('Select a condition...')
                    ->options(
                        Condition::active()
                            ->orderBy('type')
                            ->orderBy('name')
                            ->get()
                            ->groupBy('type')
                            ->map(fn ($conditions) => $conditions->pluck('display_name', 'id'))
                            ->toArray()
                    )
                    ->required()
                    ->searchable()
                    ->helperText('Choose from available conditions'),

                TextInput::make('custom_name')
                    ->label('Custom Name (Optional)')
                    ->placeholder('Leave empty to use condition name')
                    ->helperText('Override the default condition name if needed'),
            ])
            ->action(function (array $data, $record, $livewire): void {
                // Get the cart record - either directly or from relation manager
                $cart = $record instanceof CartModel ? $record : $livewire->getOwnerRecord();

                $conditionModel = Condition::findOrFail($data['condition_id']);
                $customName = ! empty($data['custom_name']) ? $data['custom_name'] : null;

                try {
                    // Get a cart instance for this specific cart record
                    $cartInstance = Cart::getCartInstance($cart->instance, $cart->identifier);

                    // Create condition from stored definition
                    $condition = $conditionModel->createCondition($customName);

                    // Apply condition to cart
                    $cartInstance->addCondition($condition);

                    Notification::make()
                        ->title('Condition Applied')
                        ->body("The '{$condition->getName()}' condition has been applied to the cart.")
                        ->success()
                        ->send();

                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Failed to Apply Condition')
                        ->body('An error occurred while applying the condition: '.$e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    /**
     * Create action for applying condition to specific cart item
     */
    public static function makeForItem(): static
    {
        return static::make('applyItemCondition')
            ->label('Apply Item Condition')
            ->modalHeading('Apply Condition to Item')
            ->modalDescription('Select a condition to apply to this specific item.')
            ->schema([
                Select::make('condition_id')
                    ->label('Condition')
                    ->placeholder('Select a condition...')
                    ->options(
                        Condition::active()
                            ->forItems()
                            ->orderBy('type')
                            ->orderBy('name')
                            ->get()
                            ->groupBy('type')
                            ->map(fn ($conditions) => $conditions->pluck('display_name', 'id'))
                            ->toArray()
                    )
                    ->required()
                    ->searchable()
                    ->helperText('Only item-level conditions are shown'),

                TextInput::make('custom_name')
                    ->label('Custom Name (Optional)')
                    ->placeholder('Leave empty to use condition name')
                    ->helperText('Override the default condition name if needed'),
            ])
            ->action(function (array $data, $record): void {
                $conditionModel = Condition::findOrFail($data['condition_id']);
                $customName = ! empty($data['custom_name']) ? $data['custom_name'] : null;

                try {
                    // Get the cart and item
                    $cart = $record->cart;
                    $cartInstance = Cart::getCartInstance($cart->instance, $cart->identifier);

                    // Create condition from stored definition
                    $condition = $conditionModel->createCondition($customName);

                    // Apply condition to specific item
                    $success = $cartInstance->addItemCondition($record->item_id, $condition);

                    if ($success) {
                        Notification::make()
                            ->title('Item Condition Applied')
                            ->body("The '{$condition->getName()}' condition has been applied to the item.")
                            ->success()
                            ->send();
                    } else {
                        throw new \Exception('Item not found in cart');
                    }

                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Failed to Apply Item Condition')
                        ->body('An error occurred while applying the condition: '.$e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    /**
     * Create action for creating and applying custom condition
     */
    public static function makeCustom(): static
    {
        return static::make('applyCustomCondition')
            ->label('Add Custom Condition')
            ->icon(Heroicon::OutlinedPlus)
            ->color('success')
            ->modalHeading('Add Custom Condition')
            ->modalDescription('Create and apply a custom condition without using a saved definition.')
            ->schema([
                TextInput::make('name')
                    ->label('Condition Name')
                    ->required()
                    ->placeholder('e.g., Special Discount')
                    ->helperText('A unique name for this condition'),

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

                Toggle::make('is_dynamic')
                    ->label('Dynamic Condition')
                    ->helperText('Enable if this condition should auto-apply/remove based on rules')
                    ->reactive()
                    ->default(false),

                Textarea::make('rules')
                    ->label('Dynamic Rules (JSON)')
                    ->placeholder('{"min_items": 3, "min_total": 100}')
                    ->helperText('JSON rules for auto-applying this condition')
                    ->visible(fn ($get) => $get('is_dynamic'))
                    ->rows(3),

                KeyValue::make('attributes')
                    ->label('Custom Attributes')
                    ->keyLabel('Key')
                    ->valueLabel('Value')
                    ->helperText('Additional attributes for this condition')
                    ->default([]),
            ])
            ->action(function (array $data, $record, $livewire): void {
                // Get the cart record - either directly or from relation manager
                $cart = $record instanceof CartModel ? $record : $livewire->getOwnerRecord();

                try {
                    // Get a cart instance for this specific cart record
                    $cartInstance = Cart::getCartInstance($cart->instance, $cart->identifier);

                    // Parse and convert rules if provided
                    $rules = null;
                    if (! empty($data['is_dynamic']) && ! empty($data['rules'])) {
                        $parsedRules = json_decode($data['rules'], true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new \Exception('Invalid JSON format for rules');
                        }
                        $rules = RuleConverter::convertRules($parsedRules);
                    }

                    // Merge custom attributes with source marker
                    $attributes = array_merge(
                        $data['attributes'] ?? [],
                        ['source' => 'custom']
                    );

                    // Create condition manually
                    $condition = new \MasyukAI\Cart\Conditions\CartCondition(
                        name: $data['name'],
                        type: $data['type'],
                        target: $data['target'],
                        value: $data['value'],
                        attributes: $attributes,
                        order: (int) $data['order'],
                        rules: $rules
                    );

                    // Apply condition to cart
                    $cartInstance->addCondition($condition);

                    Notification::make()
                        ->title('Custom Condition Applied')
                        ->body("The '{$condition->getName()}' condition has been applied to the cart.")
                        ->success()
                        ->send();

                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Failed to Apply Custom Condition')
                        ->body('An error occurred while applying the condition: '.$e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
