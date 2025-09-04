<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name'),
                Select::make('address_id')
                    ->relationship('address', 'name'),
                TextInput::make('cart_items'),
                TextInput::make('delivery_method'),
                TextInput::make('checkout_form_data'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
