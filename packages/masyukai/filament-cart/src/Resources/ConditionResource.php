<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use MasyukAI\FilamentCart\Models\Condition;
use MasyukAI\FilamentCart\Resources\ConditionResource\Pages\CreateCondition;
use MasyukAI\FilamentCart\Resources\ConditionResource\Pages\EditCondition;
use MasyukAI\FilamentCart\Resources\ConditionResource\Pages\ListConditions;
use MasyukAI\FilamentCart\Resources\ConditionResource\Schemas\ConditionForm;
use MasyukAI\FilamentCart\Resources\ConditionResource\Tables\ConditionsTable;
use UnitEnum;

final class ConditionResource extends Resource
{
    protected static ?string $model = Condition::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'E-commerce';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Conditions';

    protected static ?string $modelLabel = 'Condition';

    protected static ?string $pluralModelLabel = 'Conditions';

    protected static ?int $navigationSort = 33;

    public static function form(Schema $schema): Schema
    {
        return ConditionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConditionsTable::configure($table);
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
            'index' => ListConditions::route('/'),
            'create' => CreateCondition::route('/create'),
            'edit' => EditCondition::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): string
    {
        return (string) self::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'primary';
    }
}
