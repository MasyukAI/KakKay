<?php

namespace MasyukAI\FilamentCart\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use MasyukAI\FilamentCart\Models\CartCondition;
use MasyukAI\FilamentCart\Resources\CartConditionResource\Pages\ListCartConditions;
use MasyukAI\FilamentCart\Resources\CartConditionResource\Pages\ViewCartCondition;
use MasyukAI\FilamentCart\Resources\CartConditionResource\Schemas\CartConditionForm;
use MasyukAI\FilamentCart\Resources\CartConditionResource\Tables\CartConditionsTable;
use UnitEnum;

class CartConditionResource extends Resource
{
    protected static ?string $model = CartCondition::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'E-commerce';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Cart Conditions';

    protected static ?string $modelLabel = 'Cart Condition';

    protected static ?string $pluralModelLabel = 'Cart Conditions';

    protected static ?int $navigationSort = 32;

    public static function form(Schema $schema): Schema
    {
        return CartConditionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CartConditionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCartConditions::route('/'),
            'view' => ViewCartCondition::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        return false; // Read-only resource
    }

    /**
     * @param \MasyukAI\FilamentCart\Models\CartCondition $record
     */
    public static function canEdit($record): bool
    {
        return false; // Read-only resource
    }

    /**
     * @param \MasyukAI\FilamentCart\Models\CartCondition $record
     */
    public static function canDelete($record): bool
    {
        return false; // Read-only resource
    }
}
