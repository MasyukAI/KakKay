<?php

declare(strict_types=1);

namespace AIArmada\FilamentVouchers\Resources\VoucherResource\Schemas;

use AIArmada\FilamentVouchers\Support\OwnerTypeRegistry;
use AIArmada\Vouchers\Enums\VoucherStatus;
use AIArmada\Vouchers\Enums\VoucherType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

final class VoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        $currencyOptions = [
            'MYR' => 'MYR',
            'USD' => 'USD',
            'SGD' => 'SGD',
            'IDR' => 'IDR',
        ];

        $defaultCurrency = mb_strtoupper((string) config('filament-vouchers.default_currency', 'MYR'));
        $currencyOptions[$defaultCurrency] = $defaultCurrency;
        $ownerRegistry = app(OwnerTypeRegistry::class);

        $sections = [
            Section::make('Voucher Details')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('code')
                                ->label('Code')
                                ->required()
                                ->maxLength(64)
                                ->unique(ignoreRecord: true)
                                ->helperText('Alphanumeric voucher code shown to customers')
                                ->afterStateUpdated(static function (?string $state, Set $set): void {
                                    if ($state !== null) {
                                        $set('code', strtoupper($state));
                                    }
                                }),

                            TextInput::make('name')
                                ->label('Name')
                                ->required()
                                ->maxLength(120)
                                ->helperText('Internal friendly name for this voucher'),
                        ]),

                    Textarea::make('description')
                        ->label('Description')
                        ->rows(3)
                        ->helperText('Optional copy shown in customer-facing surfaces'),

                    Grid::make(3)
                        ->schema([
                            Select::make('type')
                                ->label('Type')
                                ->required()
                                ->options(static fn (): array => collect(VoucherType::cases())
                                    ->mapWithKeys(static fn (VoucherType $type): array => [$type->value => $type->label()])
                                    ->toArray()
                                ),

                            TextInput::make('value')
                                ->label('Value')
                                ->numeric()
                                ->minValue(0.01)
                                ->required()
                                ->helperText('Percentage for percentage vouchers, fixed amount for other types')
                                ->suffix(fn (Get $get): string => $get('type') === VoucherType::Percentage->value ? '%' : $get('currency') ?? $defaultCurrency)
                                ->live(),

                            Select::make('currency')
                                ->label('Currency')
                                ->required()
                                ->options($currencyOptions)
                                ->default($defaultCurrency),
                        ]),
                ]),

            Section::make('Usage Rules')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('min_cart_value')
                                ->label('Minimum Cart Value')
                                ->numeric()
                                ->helperText('Optional minimum subtotal required to redeem')
                                ->suffix($defaultCurrency),

                            TextInput::make('max_discount')
                                ->label('Max Discount')
                                ->numeric()
                                ->helperText('Cap the total discount for percentage vouchers')
                                ->suffix(fn (Get $get): string => $get('currency') ?? $defaultCurrency),

                            Toggle::make('allows_manual_redemption')
                                ->label('Manual Redemption')
                                ->helperText('Allow staff to redeem this voucher through point-of-sale flows')
                                ->inline(false),
                        ]),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('usage_limit')
                                ->label('Global Usage Limit')
                                ->numeric()
                                ->minValue(1)
                                ->helperText('Leave empty for unlimited global redemptions'),

                            TextInput::make('usage_limit_per_user')
                                ->label('Per User Limit')
                                ->numeric()
                                ->minValue(1)
                                ->helperText('Leave empty to allow unlimited redemptions per user'),
                        ]),
                ]),

            Section::make('Scheduling & Status')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            DateTimePicker::make('starts_at')
                                ->label('Starts At')
                                ->seconds(false),

                            DateTimePicker::make('expires_at')
                                ->label('Expires At')
                                ->seconds(false),

                            Select::make('status')
                                ->label('Status')
                                ->options(static fn (): array => collect(VoucherStatus::cases())
                                    ->mapWithKeys(static fn (VoucherStatus $status): array => [$status->value => $status->label()])
                                    ->toArray()
                                )
                                ->default(VoucherStatus::Active->value)
                                ->required(),
                        ]),
                ]),

            Section::make('Applicability')
                ->schema([
                    TagsInput::make('applicable_products')
                        ->label('Applicable Products')
                        ->placeholder('SKU or identifier')
                        ->helperText('Restrict redemption to specific SKUs'),

                    TagsInput::make('excluded_products')
                        ->label('Excluded Products')
                        ->placeholder('SKU or identifier'),

                    TagsInput::make('applicable_categories')
                        ->label('Applicable Categories')
                        ->placeholder('Category identifier'),
                ])
                ->collapsed(),
        ];

        if ($ownerRegistry->hasDefinitions()) {
            $sections[] = Section::make('Ownership')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('owner_type')
                                ->label('Owner Type')
                                ->options($ownerRegistry->options())
                                ->placeholder('Global voucher (no owner)')
                                ->live()
                                ->helperText('Determines which vendor or store can manage this voucher'),

                            Select::make('owner_id')
                                ->label('Owner')
                                ->searchable()
                                ->placeholder('Select owner')
                                ->getSearchResultsUsing(static function (Get $get, ?string $search) use ($ownerRegistry): array {
                                    $ownerType = $get('owner_type');

                                    if (! is_string($ownerType) || $ownerType === '') {
                                        return [];
                                    }

                                    return $ownerRegistry->search($ownerType, $search);
                                })
                                ->getOptionLabelUsing(static function (Get $get, $value) use ($ownerRegistry): ?string {
                                    $ownerType = $get('owner_type');

                                    if (! is_string($ownerType) || $ownerType === '' || $value === null || $value === '') {
                                        return null;
                                    }

                                    return $ownerRegistry->resolveLabelForKey($ownerType, $value);
                                })
                                ->hidden(fn (Get $get): bool => ! is_string($get('owner_type')) || $get('owner_type') === '')
                                ->dehydrated(fn (Get $get): bool => is_string($get('owner_type')) && $get('owner_type') !== '')
                                ->helperText('Optional owner assignment when ownership is enabled'),
                        ]),
                ])
                ->collapsible();
        }

        $sections[] = Section::make('Metadata')
            ->schema([
                KeyValue::make('metadata')
                    ->label('Metadata')
                    ->helperText('Attach arbitrary key-value pairs for integrations'),
            ])
            ->collapsed();

        return $schema->schema($sections);
    }
}
