<?php

declare(strict_types=1);

namespace AIArmada\FilamentVouchers\Widgets;

use AIArmada\FilamentVouchers\Models\Voucher;
use AIArmada\Vouchers\Models\VoucherUsage;
use Akaunting\Money\Money;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Attributes\Lazy;

/**
 * Displays voucher usage history as a timeline
 * Uses existing voucher_usage table data - no database changes needed!
 */
#[Lazy]
final class VoucherUsageTimelineWidget extends Widget
{
    protected string $view = 'filament-vouchers::widgets.voucher-usage-timeline';

    public ?Model $record = null;

    protected int|string|array $columnSpan = 'full';

    /**
     * Get timeline events from voucher usage history
     *
     * @return Collection<int, array{
     *     id: int,
     *     type: string,
     *     title: string,
     *     description: string,
     *     timestamp: \Carbon\Carbon,
     *     timestamp_human: string,
     *     icon: string,
     *     color: string,
     *     details: array
     * }>
     */
    public function getTimelineEvents(): Collection
    {
        if (! $this->record instanceof Voucher) {
            return collect();
        }

        // Get all usage records for this voucher
        $usages = VoucherUsage::query()
            ->where('voucher_id', $this->record->id)
            ->with('redeemedBy') // Load the polymorphic relation (User, Order, etc.)
            ->orderBy('used_at', 'desc')
            ->get();

        // Transform usage records into timeline events
        return $usages->map(function (VoucherUsage $usage) {
            $event = $this->buildTimelineEvent($usage);

            return $event;
        });
    }

    /**
     * Build a timeline event from a usage record
     *
     * @return array{
     *     id: int,
     *     type: string,
     *     title: string,
     *     description: string,
     *     timestamp: \Carbon\Carbon,
     *     timestamp_human: string,
     *     icon: string,
     *     color: string,
     *     details: array
     * }
     */
    protected function buildTimelineEvent(VoucherUsage $usage): array
    {
        $savings = Money::{$usage->currency}($usage->discount_amount)->format();

        // Determine event type based on channel and redemption
        $isManual = $usage->channel === VoucherUsage::CHANNEL_MANUAL;
        $hasOrder = $usage->redeemedBy && $usage->redeemed_by_type === 'App\Models\Order';

        // Build title
        $title = $hasOrder
            ? 'Redeemed in Order'
            : ($isManual ? 'Manual Redemption' : 'Redeemed');

        // Build description
        $description = "Discount applied: {$savings}";

        if ($usage->redeemedBy) {
            $description .= " • Customer: {$this->getCustomerName($usage)}";
        }

        // Build details array
        $details = [
            'savings' => $savings,
            'cart_identifier' => $usage->cart_identifier,
            'user_identifier' => $usage->user_identifier,
            'channel' => $usage->channel,
            'notes' => $usage->notes,
            'order_id' => $hasOrder ? $usage->redeemed_by_id : null,
            'cart_snapshot' => $usage->cart_snapshot,
        ];

        // Add cart details if available
        if ($usage->cart_snapshot) {
            $details['cart_items_count'] = $usage->cart_snapshot['items_count'] ?? null;
            $details['cart_total'] = $usage->cart_snapshot['total'] ?? null;
        }

        return [
            'id' => $usage->id,
            'type' => $hasOrder ? 'order_redemption' : ($isManual ? 'manual_redemption' : 'redemption'),
            'title' => $title,
            'description' => $description,
            'timestamp' => $usage->used_at,
            'timestamp_human' => $usage->used_at->diffForHumans(),
            'icon' => $this->getEventIcon($usage),
            'color' => $this->getEventColor($usage),
            'details' => $details,
        ];
    }

    /**
     * Get customer name from redeemed by relationship
     */
    protected function getCustomerName(VoucherUsage $usage): string
    {
        if (! $usage->redeemedBy) {
            return 'Guest';
        }

        // Try to get name from common attributes
        if (isset($usage->redeemedBy->name)) {
            return $usage->redeemedBy->name;
        }

        if (isset($usage->redeemedBy->email)) {
            return $usage->redeemedBy->email;
        }

        // Fallback to identifier
        return $usage->user_identifier;
    }

    /**
     * Get icon for event based on usage details
     */
    protected function getEventIcon(VoucherUsage $usage): string
    {
        if ($usage->redeemedBy && $usage->redeemed_by_type === 'App\Models\Order') {
            return 'heroicon-o-shopping-bag';
        }

        if ($usage->channel === VoucherUsage::CHANNEL_MANUAL) {
            return 'heroicon-o-hand-raised';
        }

        return 'heroicon-o-check-circle';
    }

    /**
     * Get color for event based on usage details
     */
    protected function getEventColor(VoucherUsage $usage): string
    {
        if ($usage->redeemedBy && $usage->redeemed_by_type === 'App\Models\Order') {
            return 'success';
        }

        if ($usage->channel === VoucherUsage::CHANNEL_MANUAL) {
            return 'info';
        }

        return 'primary';
    }

    /**
     * Get summary statistics
     *
     * @return array{total_redemptions: int, total_savings: string, unique_customers: int}
     */
    public function getSummaryStats(): array
    {
        if (! $this->record instanceof Voucher) {
            return [
                'total_redemptions' => 0,
                'total_savings' => 'RM0.00',
                'unique_customers' => 0,
            ];
        }

        $usages = VoucherUsage::query()
            ->where('voucher_id', $this->record->id)
            ->get();

        $totalSavings = $usages->sum('discount_amount');
        $currency = $usages->first()->currency ?? 'MYR';
        $uniqueCustomers = $usages->pluck('user_identifier')->unique()->count();

        return [
            'total_redemptions' => $usages->count(),
            'total_savings' => Money::{$currency}($totalSavings)->format(),
            'unique_customers' => $uniqueCustomers,
        ];
    }
}
