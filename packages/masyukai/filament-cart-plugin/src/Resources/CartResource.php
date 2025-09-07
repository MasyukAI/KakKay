<?php

namespace MasyukAI\FilamentCartPlugin\Resources;

use MasyukAI\FilamentCartPlugin\Resources\CartResource\Pages\CreateCart;
use MasyukAI\FilamentCartPlugin\Resources\CartResource\Pages\EditCart;
use MasyukAI\FilamentCartPlugin\Resources\CartResource\Pages\ListCarts;
use MasyukAI\FilamentCartPlugin\Resources\CartResource\Pages\ViewCart;
use MasyukAI\FilamentCartPlugin\Resources\CartResource\Schemas\CartForm;
use MasyukAI\FilamentCartPlugin\Resources\CartResource\Schemas\CartInfolist;
use MasyukAI\FilamentCartPlugin\Resources\CartResource\Tables\CartsTable;
use MasyukAI\FilamentCartPlugin\Models\Cart;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CartResource extends Resource
{
    protected static ?string $model = Cart::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static string|UnitEnum|null $navigationGroup = 'E-commerce';

    protected static ?string $recordTitleAttribute = 'identifier';

    protected static ?string $navigationLabel = 'Carts';

    protected static ?string $modelLabel = 'Cart';

    protected static ?string $pluralModelLabel = 'Carts';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return CartForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CartInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CartsTable::configure($table);
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
            'index' => ListCarts::route('/'),
            'create' => CreateCart::route('/create'),
            'view' => ViewCart::route('/{record}'),
            'edit' => EditCart::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::notEmpty()->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }
}