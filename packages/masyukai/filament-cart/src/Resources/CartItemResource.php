<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use MasyukAI\FilamentCart\Models\CartItem;
use MasyukAI\FilamentCart\Resources\CartItemResource\Pages\ListCartItems;
use MasyukAI\FilamentCart\Resources\CartItemResource\Pages\ViewCartItem;
use MasyukAI\FilamentCart\Resources\CartItemResource\Schemas\CartItemForm;
use MasyukAI\FilamentCart\Resources\CartItemResource\Tables\CartItemsTable;
use UnitEnum;

final class CartItemResource extends Resource
{
    protected static ?string $model = CartItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'E-commerce';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Cart Items';

    protected static ?string $modelLabel = 'Cart Item';

    protected static ?string $pluralModelLabel = 'Cart Items';

    protected static ?int $navigationSort = 31;

    public static function form(Schema $schema): Schema
    {
        return CartItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CartItemsTable::configure($table);
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
            'index' => ListCartItems::route('/'),
            'view' => ViewCartItem::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): string
    {
        return (string) self::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'info';
    }

    public static function canCreate(): bool
    {
        return false; // Read-only resource
    }

    /**
     * @param  CartItem  $record
     */
    public static function canEdit($record): bool
    {
        return false; // Read-only resource
    }

    /**
     * @param  CartItem  $record
     */
    public static function canDelete($record): bool
    {
        return false; // Read-only resource
    }
}
