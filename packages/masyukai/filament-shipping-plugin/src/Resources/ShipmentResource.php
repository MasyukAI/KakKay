<?php

declare(strict_types=1);

namespace MasyukAI\FilamentShippingPlugin\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use MasyukAI\Shipping\Models\Shipment;
use MasyukAI\FilamentShippingPlugin\Resources\ShipmentResource\Pages;

class ShipmentResource extends Resource
{
    protected static ?string $model = Shipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Shipping';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Shipment Details')
                    ->schema([
                        Forms\Components\Select::make('provider')
                            ->options([
                                'local' => 'Local Shipping',
                            ])
                            ->required(),
                        
                        Forms\Components\Select::make('method')
                            ->options([
                                'standard' => 'Standard Shipping',
                                'fast' => 'Fast Shipping',
                                'express' => 'Express Shipping',
                                'pickup' => 'Store Pickup',
                            ])
                            ->required(),
                        
                        Forms\Components\TextInput::make('tracking_number')
                            ->unique(ignoreRecord: true),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'created' => 'Created',
                                'dispatched' => 'Dispatched',
                                'in_transit' => 'In Transit',
                                'out_for_delivery' => 'Out for Delivery',
                                'delivered' => 'Delivered',
                                'failed' => 'Failed',
                            ])
                            ->required(),
                    ]),
                
                Forms\Components\Section::make('Destination Address')
                    ->schema([
                        Forms\Components\TextInput::make('destination_address.name')
                            ->label('Name')
                            ->required(),
                        
                        Forms\Components\TextInput::make('destination_address.line1')
                            ->label('Address Line 1')
                            ->required(),
                        
                        Forms\Components\TextInput::make('destination_address.line2')
                            ->label('Address Line 2'),
                        
                        Forms\Components\TextInput::make('destination_address.city')
                            ->label('City')
                            ->required(),
                        
                        Forms\Components\TextInput::make('destination_address.state')
                            ->label('State')
                            ->required(),
                        
                        Forms\Components\TextInput::make('destination_address.postal_code')
                            ->label('Postal Code')
                            ->required(),
                        
                        Forms\Components\TextInput::make('destination_address.country')
                            ->label('Country')
                            ->default('MY')
                            ->required(),
                    ]),
                
                Forms\Components\Section::make('Package Details')
                    ->schema([
                        Forms\Components\TextInput::make('weight')
                            ->label('Weight (grams)')
                            ->numeric()
                            ->suffix('g'),
                        
                        Forms\Components\TextInput::make('cost')
                            ->label('Cost (cents)')
                            ->numeric()
                            ->required(),
                        
                        Forms\Components\TextInput::make('currency')
                            ->default('MYR')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('tracking_number')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Tracking number copied!')
                    ->icon('heroicon-m-clipboard'),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'created',
                        'warning' => 'dispatched',
                        'primary' => 'in_transit',
                        'info' => 'out_for_delivery',
                        'success' => 'delivered',
                        'danger' => 'failed',
                    ]),
                
                Tables\Columns\TextColumn::make('provider')
                    ->badge(),
                
                Tables\Columns\TextColumn::make('method')
                    ->label('Shipping Method'),
                
                Tables\Columns\TextColumn::make('destination_address.name')
                    ->label('Recipient'),
                
                Tables\Columns\TextColumn::make('destination_address.city')
                    ->label('City'),
                
                Tables\Columns\TextColumn::make('cost')
                    ->label('Cost')
                    ->money('MYR', divideBy: 100)
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('delivered_at')
                    ->label('Delivered')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'created' => 'Created',
                        'dispatched' => 'Dispatched',
                        'in_transit' => 'In Transit',
                        'out_for_delivery' => 'Out for Delivery',
                        'delivered' => 'Delivered',
                        'failed' => 'Failed',
                    ]),
                
                Tables\Filters\SelectFilter::make('provider')
                    ->options([
                        'local' => 'Local Shipping',
                    ]),
                
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Shipment Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('tracking_number'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'created' => 'gray',
                                'dispatched' => 'warning',
                                'in_transit' => 'primary',
                                'out_for_delivery' => 'info',
                                'delivered' => 'success',
                                'failed' => 'danger',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('provider'),
                        Infolists\Components\TextEntry::make('method'),
                        Infolists\Components\TextEntry::make('cost')
                            ->money('MYR', divideBy: 100),
                    ])
                    ->columns(2),
                
                Infolists\Components\Section::make('Destination Address')
                    ->schema([
                        Infolists\Components\TextEntry::make('destination_address.name'),
                        Infolists\Components\TextEntry::make('destination_address.line1'),
                        Infolists\Components\TextEntry::make('destination_address.line2'),
                        Infolists\Components\TextEntry::make('destination_address.city'),
                        Infolists\Components\TextEntry::make('destination_address.state'),
                        Infolists\Components\TextEntry::make('destination_address.postal_code'),
                        Infolists\Components\TextEntry::make('destination_address.country'),
                    ])
                    ->columns(2),
                
                Infolists\Components\Section::make('Tracking Events')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('trackingEvents')
                            ->schema([
                                Infolists\Components\TextEntry::make('status'),
                                Infolists\Components\TextEntry::make('description'),
                                Infolists\Components\TextEntry::make('location'),
                                Infolists\Components\TextEntry::make('event_date')
                                    ->dateTime(),
                            ])
                            ->columns(4),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShipments::route('/'),
            'create' => Pages\CreateShipment::route('/create'),
            'view' => Pages\ViewShipment::route('/{record}'),
            'edit' => Pages\EditShipment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'in_transit')->count();
    }
}