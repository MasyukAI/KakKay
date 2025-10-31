<?php

declare(strict_types=1);

namespace AIArmada\FilamentVouchers\Extensions;

use AIArmada\FilamentCart\Models\Cart;
use AIArmada\FilamentCart\Services\CartInstanceManager;
use AIArmada\Vouchers\Exceptions\VoucherException;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Throwable;

/**
 * CartVoucherActions provides voucher management actions for Filament Cart pages.
 *
 * Usage in CartResource ViewCart page:
 * ```php
 * use AIArmada\FilamentVouchers\Extensions\CartVoucherActions;
 *
 * protected function getHeaderActions(): array
 * {
 *     return array_merge(parent::getHeaderActions(), [
 *         CartVoucherActions::applyVoucher(),
 *         CartVoucherActions::showAppliedVouchers(),
 *     ]);
 * }
 * ```
 */
final class CartVoucherActions
{
    /**
     * Create an action to apply a voucher to a cart
     */
    public static function applyVoucher(): Action
    {
        return Action::make('apply_voucher')
            ->label('Apply Voucher')
            ->icon(Heroicon::OutlinedTicket)
            ->color('success')
            ->modalHeading('Apply Voucher Code')
            ->modalDescription('Enter a voucher code to apply a discount to this cart.')
            ->modalSubmitActionLabel('Apply')
            ->modalWidth('md')
            ->form([
                TextInput::make('voucher_code')
                    ->label('Voucher Code')
                    ->placeholder('e.g., SUMMER2024, SAVE10')
                    ->required()
                    ->maxLength(255)
                    ->autocomplete(false)
                    ->helperText('Enter the voucher code you want to apply.')
                    ->columnSpanFull(),
            ])
            ->action(function (array $data, Cart $record): void {
                $code = trim($data['voucher_code'] ?? '');

                if ($code === '') {
                    Notification::make()
                        ->warning()
                        ->title('Invalid Code')
                        ->body('Please enter a valid voucher code.')
                        ->send();

                    return;
                }

                try {
                    $cartInstance = app(CartInstanceManager::class)->resolve(
                        $record->instance,
                        $record->identifier
                    );

                    // Apply voucher - method is added dynamically via CartManagerWithVouchers proxy
                    /** @phpstan-ignore-next-line */
                    $cartInstance->applyVoucher($code);

                    Notification::make()
                        ->success()
                        ->title('Voucher Applied!')
                        ->body("The voucher code '{$code}' has been successfully applied to this cart.")
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->send();

                } catch (VoucherException $exception) {
                    Notification::make()
                        ->danger()
                        ->title('Cannot Apply Voucher')
                        ->body($exception->getMessage())
                        ->icon(Heroicon::OutlinedXCircle)
                        ->persistent()
                        ->send();

                    Log::warning('Voucher application failed', [
                        'code' => $code,
                        'cart_id' => $record->id,
                        'identifier' => $record->identifier,
                        'error' => $exception->getMessage(),
                    ]);
                } catch (Throwable $exception) {
                    Notification::make()
                        ->danger()
                        ->title('Unexpected Error')
                        ->body('An error occurred while applying the voucher. Please try again.')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->persistent()
                        ->send();

                    Log::error('Unexpected voucher application error', [
                        'code' => $code,
                        'cart_id' => $record->id,
                        'identifier' => $record->identifier,
                        'error' => $exception->getMessage(),
                        'trace' => $exception->getTraceAsString(),
                    ]);
                }
            });
    }

    /**
     * Create an action to view applied vouchers on a cart
     */
    public static function showAppliedVouchers(): Action
    {
        return Action::make('show_applied_vouchers')
            ->label('View Vouchers')
            ->icon(Heroicon::OutlinedTicket)
            ->color('info')
            ->modalHeading('Applied Vouchers')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->modalWidth('lg')
            ->infolist(fn (Cart $record): array => self::getAppliedVouchersInfolist($record));
    }

    /**
     * Create an action to remove a specific voucher from cart
     */
    public static function removeVoucher(string $voucherCode): Action
    {
        return Action::make('remove_voucher_'.$voucherCode)
            ->label('Remove')
            ->icon(Heroicon::OutlinedXMark)
            ->color('danger')
            ->size('xs')
            ->requiresConfirmation()
            ->modalHeading('Remove Voucher')
            ->modalDescription("Are you sure you want to remove the voucher '{$voucherCode}'?")
            ->action(function (Cart $record) use ($voucherCode): void {
                try {
                    $cartInstance = app(CartInstanceManager::class)->resolve(
                        $record->instance,
                        $record->identifier
                    );

                    // Remove voucher - method is added dynamically via CartManagerWithVouchers proxy
                    /** @phpstan-ignore-next-line */
                    $cartInstance->removeVoucher($voucherCode);

                    Notification::make()
                        ->success()
                        ->title('Voucher Removed')
                        ->body("The voucher '{$voucherCode}' has been removed from this cart.")
                        ->send();

                } catch (Throwable $exception) {
                    Notification::make()
                        ->danger()
                        ->title('Error')
                        ->body('Failed to remove the voucher. Please try again.')
                        ->send();

                    Log::error('Failed to remove voucher', [
                        'code' => $voucherCode,
                        'cart_id' => $record->id,
                        'error' => $exception->getMessage(),
                    ]);
                }
            });
    }

    /**
     * Get infolist schema for applied vouchers
     *
     * @return array<mixed>
     */
    protected static function getAppliedVouchersInfolist(Cart $record): array
    {
        try {
            $cartInstance = app(CartInstanceManager::class)->resolve(
                $record->instance,
                $record->identifier
            );

            /** @phpstan-ignore-next-line */
            $vouchers = $cartInstance->getAppliedVouchers();

            if (empty($vouchers)) {
                return [
                    Placeholder::make('no_vouchers')
                        ->content(new HtmlString('
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                </svg>
                                <p class="mt-4 text-sm text-gray-500">No vouchers are currently applied to this cart.</p>
                            </div>
                        ')),
                ];
            }

            $entries = [];
            foreach ($vouchers as $voucher) {
                $entries[] = Grid::make(2)
                    ->schema([
                        Placeholder::make('code_'.$voucher->code)
                            ->label('Code')
                            ->content($voucher->code),
                        Placeholder::make('type_'.$voucher->code)
                            ->label('Type')
                            ->content(ucfirst($voucher->type->value ?? 'unknown')),
                    ]);
            }

            return $entries;

        } catch (Throwable $exception) {
            return [
                Placeholder::make('error')
                    ->content('Unable to load voucher information. The voucher integration may not be properly configured.'),
            ];
        }
    }
}
