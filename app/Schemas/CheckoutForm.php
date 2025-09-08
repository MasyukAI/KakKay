<?php

namespace App\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CheckoutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Maklumat Penghantaran')
                    ->description('Masukkan maklumat untuk penghantaran')
                    ->icon('heroicon-o-truck')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('name')
                                    ->label('Nama Penuh')
                                    ->required()
                                    ->placeholder('Nama penuh anda')
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label('Alamat Email')
                                    ->email()
                                    ->required()
                                    ->placeholder('nama@email.com')
                                    ->maxLength(255),

                                Select::make('country')
                                    ->label('Negara')
                                    ->required()
                                    ->default('Malaysia')
                                    ->options([
                                        'Malaysia' => 'Malaysia',
                                        'Singapore' => 'Singapura',
                                        'Indonesia' => 'Indonesia',
                                        'Thailand' => 'Thailand',
                                        'Brunei' => 'Brunei',
                                    ]),

                                Select::make('city')
                                    ->label('Bandar')
                                    ->required()
                                    ->default('Kuala Lumpur')
                                    ->options([
                                        'Kuala Lumpur' => 'Kuala Lumpur',
                                        'Johor Bahru' => 'Johor Bahru',
                                        'Penang' => 'Pulau Pinang',
                                        'Kota Kinabalu' => 'Kota Kinabalu',
                                        'Kuching' => 'Kuching',
                                    ]),

                                TextInput::make('phone')
                                    ->label('Nombor Telefon')
                                    ->required()
                                    ->placeholder('123456789')
                                    ->tel()
                                    ->maxLength(20)
                                    ->helperText('Kod negara akan ditambah secara automatik'),
                            ]),

                        TextInput::make('address')
                            ->label('Alamat Baris 1')
                            ->required()
                            ->placeholder('Nombor rumah, nama jalan')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('address2')
                            ->label('Alamat Baris 2 (Opsional)')
                            ->placeholder('Taman, kawasan, dll')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->components([
                                TextInput::make('state')
                                    ->label('Negeri')
                                    ->placeholder('Contoh: Selangor')
                                    ->maxLength(100),

                                TextInput::make('postal_code')
                                    ->label('Poskod')
                                    ->placeholder('Contoh: 40000')
                                    ->maxLength(10),

                                TextInput::make('company_name')
                                    ->label('Nama Syarikat (Opsional)')
                                    ->placeholder('Nama syarikat')
                                    ->maxLength(255),

                                TextInput::make('vat_number')
                                    ->label('VAT/SST Number (Opsional)')
                                    ->placeholder('Nombor VAT/SST')
                                    ->maxLength(50),
                            ]),
                    ]),

                Section::make('Cara Penghantaran')
                    ->description('Pilih cara penghantaran yang sesuai')
                    ->icon('heroicon-o-truck')
                    ->components([
                        Select::make('delivery_method')
                            ->label('Kaedah Penghantaran')
                            ->required()
                            ->default('standard')
                            ->options([
                                'standard' => 'RM5 - Penghantaran Standard (3-5 hari bekerja)',
                                'fast' => 'RM15 - Penghantaran Pantas (1-2 hari bekerja)',
                                'express' => 'RM49 - Penghantaran Ekspres (Hari yang sama)',
                            ])
                            ->live()
                            ->native(false)
                            ->helperText('Kos penghantaran akan dikira secara automatik'),
                    ]),

                Section::make('Kod Promosi')
                    ->description('Masukkan kod voucher atau promosi (jika ada)')
                    ->icon('heroicon-o-ticket')
                    ->components([
                        TextInput::make('voucher_code')
                            ->label('Kod Voucher')
                            ->placeholder('Masukkan kod voucher')
                            ->maxLength(50),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
