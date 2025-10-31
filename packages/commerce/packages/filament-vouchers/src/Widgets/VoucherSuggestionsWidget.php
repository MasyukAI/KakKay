<?php

declare(strict_types=1);

namespace AIArmada\FilamentVouchers\Widgets;

use AIArmada\FilamentCart\Models\Cart;
use AIArmada\FilamentCart\Services\CartInstanceManager;
use AIArmada\Vouchers\Enums\VoucherStatus;
use AIArmada\Vouchers\Enums\VoucherType;
use AIArmada\Vouchers\Exceptions\VoucherException;
use AIArmada\Vouchers\Models\Voucher;
use Akaunting\Money\Money;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Lazy;

/**
 * Smart widget that suggests eligible vouchers based on cart contents
 */
#[Lazy]
final class VoucherSuggestionsWidget extends Widget
{
    protected static string $view = 'filament-vouchers::widgets.voucher-suggestions';

    public ?Model $record = null;

    protected int|string|array $columnSpan = 'full';

    /**
     * Get eligible voucher suggestions for this cart
     *
     * @return Collection<int, array{voucher: Voucher, potential_savings: int, savings_text: string, recommendation: string}>
     */
    public function getEligibleVouchers(): Collection
    {
        if (! $this->record instanceof Cart) {
            return collect();
        }

        try {
            $cartTotal = $this->record->subtotal;
            $cartCurrency = $this->record->currency;

            // Get active vouchers
            $vouchers = Voucher::query()
                ->where('status', VoucherStatus::Active)
                ->where(function ($query) {
                    $query->whereNull('start_date')
                        ->orWhere('start_date', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', now());
                })
                ->where(function ($query) {
                    // Has remaining uses or unlimited
                    $query->whereNull('usage_limit')
                        ->orWhereRaw('(SELECT COUNT(*) FROM voucher_usage WHERE voucher_id = vouchers.id) < usage_limit');
                })
                ->where('currency', $cartCurrency)
                ->get();

            // Filter and calculate potential savings
            $suggestions = $vouchers
                ->filter(function (Voucher $voucher) use ($cartTotal) {
                    // Check minimum cart value
                    if ($voucher->min_cart_value && $cartTotal < $voucher->min_cart_value) {
                        return false;
                    }

                    // Check if already applied
                    try {
                        $cartInstance = app(CartInstanceManager::class)->resolve(
                            $this->record->instance,
                            $this->record->identifier
                        );

                        /** @phpstan-ignore-next-line */
                        $appliedVouchers = $cartInstance->getAppliedVouchers();
                        $appliedCodes = collect($appliedVouchers)->pluck('code')->toArray();

                        if (in_array($voucher->code, $appliedCodes, true)) {
                            return false;
                        }
                    } catch (\Throwable $exception) {
                        // If we can't check, assume it's not applied
                    }

                    return true;
                })
                ->map(function (Voucher $voucher) use ($cartTotal, $cartCurrency) {
                    $potentialSavings = $this->calculatePotentialSavings($voucher, $cartTotal);
                    $savingsText = Money::{$cartCurrency}($potentialSavings)->format();

                    $recommendation = $this->generateRecommendation($voucher, $cartTotal, $potentialSavings);

                    return [
                        'voucher' => $voucher,
                        'potential_savings' => $potentialSavings,
                        'savings_text' => $savingsText,
                        'recommendation' => $recommendation,
                    ];
                })
                ->filter(fn (array $suggestion) => $suggestion['potential_savings'] > 0)
                ->sortByDesc('potential_savings')
                ->take(5); // Show top 5 suggestions

            return $suggestions;
        } catch (\Throwable $exception) {
            Log::error('Failed to load voucher suggestions', [
                'cart_id' => $this->record->id ?? null,
                'error' => $exception->getMessage(),
            ]);

            return collect();
        }
    }

    /**
     * Calculate potential savings for a voucher
     */
    protected function calculatePotentialSavings(Voucher $voucher, int $cartTotal): int
    {
        $savings = match ($voucher->type) {
            VoucherType::Percentage => (int) round(($cartTotal * $voucher->value) / 10000), // value is in basis points
            VoucherType::Fixed => $voucher->value, // value is in cents
            VoucherType::FreeShipping => 0, // Can't calculate shipping savings
            default => 0,
        };

        // Apply max discount cap if set
        if ($voucher->max_discount && $savings > $voucher->max_discount) {
            $savings = $voucher->max_discount;
        }

        return $savings;
    }

    /**
     * Generate recommendation text
     */
    protected function generateRecommendation(Voucher $voucher, int $cartTotal, int $potentialSavings): string
    {
        if ($voucher->type === VoucherType::FreeShipping) {
            return 'Get free shipping on this order';
        }

        $savingsPercentage = $cartTotal > 0 ? round(($potentialSavings / $cartTotal) * 100, 1) : 0;

        if ($savingsPercentage >= 20) {
            return "Save {$savingsPercentage}% on your order!";
        }

        if ($voucher->end_date && $voucher->end_date->diffInDays() <= 3) {
            return 'Expires in '.$voucher->end_date->diffInDays().' days!';
        }

        if ($voucher->usage_limit) {
            $remaining = $voucher->getRemainingUses();
            if ($remaining && $remaining <= 10) {
                return "Only {$remaining} uses left!";
            }
        }

        return 'Apply now to save on your order';
    }

    /**
     * Apply a suggested voucher
     */
    public function applySuggestion(string $voucherCode): void
    {
        if (! $this->record instanceof Cart) {
            return;
        }

        try {
            $cartInstance = app(CartInstanceManager::class)->resolve(
                $this->record->instance,
                $this->record->identifier
            );

            /** @phpstan-ignore-next-line */
            $cartInstance->applyVoucher($voucherCode);

            Notification::make()
                ->success()
                ->title('Voucher Applied!')
                ->body("Voucher '{$voucherCode}' has been applied.")
                ->icon(Heroicon::OutlinedCheckCircle)
                ->send();

            // Refresh the page
            $this->dispatch('$refresh');

        } catch (VoucherException $exception) {
            Notification::make()
                ->danger()
                ->title('Cannot Apply Voucher')
                ->body($exception->getMessage())
                ->send();
        } catch (\Throwable $exception) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Failed to apply voucher. Please try again.')
                ->send();

            Log::error('Failed to apply suggested voucher', [
                'code' => $voucherCode,
                'cart_id' => $this->record->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
