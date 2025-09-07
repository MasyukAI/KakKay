<?php

namespace App\Filament\Resources\Carts;

use App\Filament\Resources\Carts\Pages\CreateCart;
use App\Filament\Resources\Carts\Pages\EditCart;
use App\Filament\Resources\Carts\Pages\ListCarts;
use App\Filament\Resources\Carts\Pages\ViewCart;
use App\Filament\Resources\Carts\Schemas\CartForm;
use App\Filament\Resources\Carts\Schemas\CartInfolist;
use App\Filament\Resources\Carts\Tables\CartsTable;
use App\Models\Cart;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CartResource extends Resource
{
    protected static ?string $model = Cart::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?string $navigationGroup = 'E-commerce';

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