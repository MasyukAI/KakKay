<?php

declare(strict_types=1);

namespace AIArmada\FilamentVouchers\Resources\VoucherUsageResource\Tables;

use AIArmada\FilamentVouchers\Models\VoucherUsage;
use Akaunting\Money\Money;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

final class VoucherUsagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('used_at', 'desc')
            ->columns([
                TextColumn::make('user_identifier')
                    ->label('User Identifier')
                    ->searchable()
                    ->copyable()
                    ->wrap(),

                TextColumn::make('channel')
                    ->label('Channel')
                    ->badge()
                    ->color(static fn (string $state): string => match ($state) {
                        VoucherUsage::CHANNEL_MANUAL => 'warning',
                        VoucherUsage::CHANNEL_API => 'info',
                        default => 'success',
                    })
                    ->icon(static fn (string $state): ?Heroicon => match ($state) {
                        VoucherUsage::CHANNEL_MANUAL => Heroicon::OutlinedClipboardDocumentCheck,
                        VoucherUsage::CHANNEL_API => Heroicon::OutlinedCommandLine,
                        default => Heroicon::OutlinedBolt,
                    }),

                TextColumn::make('discount_amount')
                    ->label('Discount')
                    ->formatStateUsing(static function ($state, VoucherUsage $record): string {
                        $currency = mb_strtoupper((string) ($record->currency ?? config('filament-vouchers.default_currency', 'MYR')));
                        $minor = (int) round(((float) $state) * 100);

                        return (string) Money::{$currency}($minor);
                    })
                    ->alignEnd(),

                TextColumn::make('cart_identifier')
                    ->label('Cart ID')
                    ->copyable()
                    ->toggleable()
                    ->url(fn (VoucherUsage $record): ?string => $record->cart_url)
                    ->openUrlInNewTab(),

                TextColumn::make('used_at')
                    ->label('Redeemed At')
                    ->dateTime()
                    ->sortable(),

                IconColumn::make('metadata')
                    ->label('Notes?')
                    ->boolean()
                    ->tooltip('Voucher usage contains metadata or notes')
                    ->state(static fn (VoucherUsage $record): bool => ! empty($record->metadata) || ! empty($record->notes))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('channel')
                    ->label('Channel')
                    ->options([
                        VoucherUsage::CHANNEL_AUTOMATIC => 'Automatic',
                        VoucherUsage::CHANNEL_MANUAL => 'Manual',
                        VoucherUsage::CHANNEL_API => 'API',
                    ]),

                // Additional filters can be added once voucher usage gains soft deletes or status metadata.
            ])
            ->recordUrl(null);
    }
}
