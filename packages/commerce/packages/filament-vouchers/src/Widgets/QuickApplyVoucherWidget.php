<?php

declare(strict_types=1);

namespace AIArmada\FilamentVouchers\Widgets;

use AIArmada\FilamentCart\Models\Cart;
use AIArmada\FilamentCart\Services\CartInstanceManager;
use AIArmada\Vouchers\Exceptions\VoucherException;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Validate;

/**
 * Quick voucher apply widget with inline input field
 */
#[Lazy]
final class QuickApplyVoucherWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament-vouchers::widgets.quick-apply-voucher';

    public ?Model $record = null;

    protected int|string|array $columnSpan = 'full';

    #[Validate('required|string|max:255')]
    public string $voucherCode = '';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('voucherCode')
                    ->label('Voucher Code')
                    ->placeholder('Enter voucher code (e.g., SUMMER2024)')
                    ->required()
                    ->maxLength(255)
                    ->autocomplete(false)
                    ->suffixAction(
                        \Filament\Forms\Components\Actions\Action::make('apply')
                            ->label('Apply')
                            ->icon(Heroicon::OutlinedTicket)
                            ->action('applyVoucher')
                    )
                    ->hint('Press Enter or click Apply')
                    ->live(onBlur: false),
            ])
            ->statePath('data');
    }

    public function applyVoucher(): void
    {
        $this->validate();

        if (! $this->record instanceof Cart) {
            Notification::make()
                ->warning()
                ->title('Invalid Cart')
                ->body('Cannot apply voucher to this cart.')
                ->send();

            return;
        }

        $code = trim($this->voucherCode);

        if ($code === '') {
            Notification::make()
                ->warning()
                ->title('Empty Code')
                ->body('Please enter a voucher code.')
                ->send();

            return;
        }

        try {
            $cartInstance = app(CartInstanceManager::class)->resolve(
                $this->record->instance,
                $this->record->identifier
            );

            /** @phpstan-ignore-next-line - applyVoucher method added via proxy */
            $cartInstance->applyVoucher($code);

            Notification::make()
                ->success()
                ->title('Voucher Applied!')
                ->body("Voucher '{$code}' has been applied to this cart.")
                ->icon(Heroicon::OutlinedCheckCircle)
                ->duration(3000)
                ->send();

            // Clear the input
            $this->voucherCode = '';
            $this->form->fill(['voucherCode' => '']);

            // Refresh the page
            $this->dispatch('$refresh');

        } catch (VoucherException $exception) {
            Notification::make()
                ->danger()
                ->title('Cannot Apply Voucher')
                ->body($exception->getMessage())
                ->icon(Heroicon::OutlinedXCircle)
                ->persistent()
                ->send();

            Log::warning('Quick apply voucher failed', [
                'code' => $code,
                'cart_id' => $this->record->id,
                'error' => $exception->getMessage(),
            ]);
        } catch (\Throwable $exception) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('An unexpected error occurred. Please try again.')
                ->icon(Heroicon::OutlinedXCircle)
                ->send();

            Log::error('Quick apply voucher unexpected error', [
                'code' => $code,
                'cart_id' => $this->record->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
