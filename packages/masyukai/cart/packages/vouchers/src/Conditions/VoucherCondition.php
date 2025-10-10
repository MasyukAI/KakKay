<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Vouchers\Conditions;

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Models\CartItem;
use MasyukAI\Cart\Vouchers\Data\VoucherData;
use MasyukAI\Cart\Vouchers\Enums\VoucherType;
use MasyukAI\Cart\Vouchers\Facades\Voucher;

/**
 * VoucherCondition bridges the voucher system with cart's condition system.
 *
 * This class extends CartCondition to provide voucher-specific behavior
 * while maintaining compatibility with the cart's pricing engine.
 */
class VoucherCondition extends CartCondition
{
    private VoucherData $voucher;

    /**
     * Create a new voucher condition.
     *
     * @param  VoucherData  $voucher  The voucher data to apply
     * @param  int  $order  The order in which this condition should be applied
     * @param  bool  $dynamic  Whether to add validation rules (true for dynamic, false for static)
     */
    public function __construct(VoucherData $voucher, int $order = 0, bool $dynamic = true)
    {
        $this->voucher = $voucher;

        // Convert voucher to cart condition format
        $value = $this->formatVoucherValue($voucher);
        $target = $this->determineTarget($voucher);

        parent::__construct(
            name: "voucher_{$voucher->code}",
            type: 'voucher',
            target: $target,
            value: $value,
            attributes: [
                'voucher_id' => $voucher->id,
                'voucher_code' => $voucher->code,
                'voucher_type' => $voucher->type->value,
                'description' => $voucher->description,
                'original_value' => $voucher->value,
            ],
            order: $order,
            rules: $dynamic ? [$this, 'validateVoucher'] : null
        );
    }

    /**
     * Validate that the voucher can still be applied to the cart.
     *
     * This is called by the cart's condition system before applying the discount.
     *
     * @param  Cart  $cart  The cart to validate against
     * @param  CartItem|null  $item  The item being evaluated (null for cart-level conditions)
     * @return bool True if voucher is valid, false otherwise
     */
    public function validateVoucher(Cart $cart, ?CartItem $item = null): bool
    {
        // Re-validate the voucher against current cart state
        $validationResult = Voucher::validate($this->voucher->code, $cart);

        return $validationResult->isValid;
    }

    /**
     * Get the voucher data.
     */
    public function getVoucher(): VoucherData
    {
        return $this->voucher;
    }

    /**
     * Get the voucher code.
     */
    public function getVoucherCode(): string
    {
        return $this->voucher->code;
    }

    /**
     * Get the voucher ID.
     */
    public function getVoucherId(): int
    {
        return $this->voucher->id;
    }

    /**
     * Check if this is a free shipping voucher.
     */
    public function isFreeShipping(): bool
    {
        return $this->voucher->type === VoucherType::FreeShipping;
    }

    /**
     * Apply the condition to a value.
     *
     * For free shipping vouchers, this should remove shipping costs.
     * For other vouchers, use the parent's apply logic.
     *
     * @param  float  $value  The base value to apply the condition to
     * @return float The modified value
     */
    public function apply(float $value): float
    {
        if ($this->isFreeShipping()) {
            // Free shipping logic can be handled here or by the shipping calculator
            // For now, we return the value as-is and let shipping conditions handle it
            return $value;
        }

        // Apply max discount cap if set
        $result = parent::apply($value);

        if ($this->voucher->maxDiscountAmount !== null) {
            $discount = $value - $result;
            $maxDiscount = $this->voucher->maxDiscountAmount;

            if ($discount > $maxDiscount) {
                $result = $value - $maxDiscount;
            }
        }

        return $result;
    }

    /**
     * Convert to array with voucher-specific data.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        $array['voucher'] = [
            'id' => $this->voucher->id,
            'code' => $this->voucher->code,
            'type' => $this->voucher->type->value,
            'value' => $this->voucher->value,
            'description' => $this->voucher->description,
            'max_discount_amount' => $this->voucher->maxDiscountAmount,
            'is_free_shipping' => $this->isFreeShipping(),
        ];

        return $array;
    }

    /**
     * Format the voucher value for cart condition system.
     *
     * @return string The formatted value (e.g., '-10%', '-50', '+0')
     */
    private function formatVoucherValue(VoucherData $voucher): string
    {
        return match ($voucher->type) {
            VoucherType::Percentage => "-{$voucher->value}%",
            VoucherType::Fixed => "-{$voucher->value}",
            VoucherType::FreeShipping => '+0', // Free shipping is handled separately
        };
    }

    /**
     * Determine the condition target based on voucher type.
     *
     * @return string The target ('subtotal' or 'total')
     */
    private function determineTarget(VoucherData $voucher): string
    {
        // Most vouchers apply to subtotal
        // Free shipping vouchers would apply to total (after shipping is added)
        return match ($voucher->type) {
            VoucherType::FreeShipping => 'total',
            default => 'subtotal',
        };
    }
}
