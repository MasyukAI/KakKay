<?php

declare(strict_types=1);

namespace AIArmada\Vouchers\Traits;

use AIArmada\Vouchers\Conditions\VoucherCondition;
use AIArmada\Vouchers\Events\VoucherApplied;
use AIArmada\Vouchers\Events\VoucherRemoved;
use AIArmada\Vouchers\Exceptions\InvalidVoucherException;
use AIArmada\Vouchers\Facades\Voucher;
use AIArmada\Vouchers\Support\CartWithVouchers;

/**
 * HasVouchers trait adds voucher management capabilities to the Cart.
 *
 * This trait provides convenient methods for applying, removing, and checking vouchers
 * while integrating seamlessly with the cart's condition system.
 */
trait HasVouchers
{
    /**
     * Apply a voucher to the cart by code.
     *
     * This method validates the voucher, creates a VoucherCondition,
     * and adds it to the cart's conditions.
     *
     * @param  string  $code  The voucher code to apply
     * @param  int  $order  The order in which the voucher condition should be applied (default: 100)
     *
     * @throws InvalidVoucherException If the voucher is invalid or cannot be applied
     */
    public function applyVoucher(string $code, int $order = 100): self
    {
        // Validate the voucher against current cart state
        $validationResult = Voucher::validate($code, $this);

        if (! $validationResult->isValid) {
            throw new InvalidVoucherException(
                "Voucher '{$code}' cannot be applied: {$validationResult->reason}"
            );
        }

        // Check if cart already has maximum allowed vouchers
        $maxVouchers = config('vouchers.cart.max_vouchers_per_cart', 1);
        $currentVoucherCount = count($this->getAppliedVouchers());

        if ($currentVoucherCount >= $maxVouchers && $maxVouchers > 0) {
            throw new InvalidVoucherException(
                "Cart already has the maximum number of vouchers ({$maxVouchers})"
            );
        }

        // Check if this specific voucher is already applied
        if ($this->hasVoucher($code)) {
            throw new InvalidVoucherException(
                "Voucher '{$code}' is already applied to this cart"
            );
        }

        // Find the voucher
        $voucherData = Voucher::find($code);

        if ($voucherData === null) {
            throw new InvalidVoucherException(
                "Voucher '{$code}' not found"
            );
        }

        // Create and add the voucher condition
        $voucherCondition = new VoucherCondition($voucherData, $order);
        $cart = $this instanceof CartWithVouchers ? $this->getCart() : $this;
        $cart->addCondition($voucherCondition);

        // Dispatch event if events are enabled
        if ($this instanceof CartWithVouchers) {
            // For CartWithVouchers wrapper, dispatch through Laravel's event system
            \Illuminate\Support\Facades\Event::dispatch(new VoucherApplied($this->getCart(), $voucherData));
        }

        return $this;
    }

    /**
     * Remove a voucher from the cart by code.
     *
     * @param  string  $code  The voucher code to remove
     */
    public function removeVoucher(string $code): self
    {
        $conditionName = "voucher_{$code}";

        // Get the voucher before removing to dispatch event
        $voucherCondition = $this->getVoucherCondition($code);

        // Remove the condition
        $cart = $this instanceof CartWithVouchers ? $this->getCart() : $this;
        $cart->removeCondition($conditionName);

        // Dispatch event if events are enabled
        if ($voucherCondition) {
            if ($this instanceof CartWithVouchers) {
                // For CartWithVouchers wrapper, dispatch through Laravel's event system
                \Illuminate\Support\Facades\Event::dispatch(new VoucherRemoved($this->getCart(), $voucherCondition->getVoucher()));
            }
        }

        return $this;
    }

    /**
     * Remove all vouchers from the cart.
     */
    public function clearVouchers(): self
    {
        $vouchers = $this->getAppliedVouchers();

        foreach ($vouchers as $voucher) {
            $this->removeVoucher($voucher->getVoucherCode());
        }

        return $this;
    }

    /**
     * Check if the cart has a specific voucher applied, or any voucher if code is null.
     *
     * @param  string|null  $code  Optional voucher code to check for. If null, checks for any voucher.
     */
    public function hasVoucher(?string $code = null): bool
    {
        if ($code === null) {
            return count($this->getAppliedVouchers()) > 0;
        }

        $conditionName = "voucher_{$code}";
        $cart = $this instanceof CartWithVouchers ? $this->getCart() : $this;

        return $cart->getCondition($conditionName) !== null;
    }

    /**
     * Get a specific voucher condition by code.
     *
     * @param  string  $code  The voucher code
     */
    public function getVoucherCondition(string $code): ?VoucherCondition
    {
        $conditionName = "voucher_{$code}";
        $cart = $this instanceof CartWithVouchers ? $this->getCart() : $this;
        $condition = $cart->getCondition($conditionName);

        return $condition instanceof VoucherCondition ? $condition : null;
    }

    /**
     * Get all applied voucher conditions.
     *
     * @return array<VoucherCondition>
     */
    public function getAppliedVouchers(): array
    {
        $cart = $this instanceof CartWithVouchers ? $this->getCart() : $this;
        $conditions = $cart->getConditions();

        return array_filter(
            $conditions->toArray(),
            fn ($condition) => $condition instanceof VoucherCondition
        );
    }

    /**
     * Get the codes of all applied vouchers.
     *
     * @return array<string>
     */
    public function getAppliedVoucherCodes(): array
    {
        return array_map(
            fn (VoucherCondition $voucher) => $voucher->getVoucherCode(),
            $this->getAppliedVouchers()
        );
    }

    /**
     * Calculate the total discount from all applied vouchers.
     *
     * @return float The total voucher discount amount
     */
    public function getVoucherDiscount(): float
    {
        $discount = 0.0;
        $cart = $this instanceof CartWithVouchers ? $this->getCart() : $this;
        $subtotal = $cart->subtotal();

        foreach ($this->getAppliedVouchers() as $voucher) {
            $discountAmount = abs($voucher->getCalculatedValue($subtotal));
            $discount += $discountAmount;

            // Update subtotal for next voucher calculation if stacking
            if (config('vouchers.cart.allow_stacking', false)) {
                $subtotal -= $discountAmount;
            }
        }

        return $discount;
    }

    /**
     * Check if the cart can accept more vouchers.
     */
    public function canAddVoucher(): bool
    {
        $maxVouchers = config('vouchers.cart.max_vouchers_per_cart', 1);

        if ($maxVouchers === 0) {
            return false; // Vouchers disabled
        }

        $currentVoucherCount = count($this->getAppliedVouchers());

        return $currentVoucherCount < $maxVouchers || $maxVouchers === -1;
    }

    /**
     * Validate all currently applied vouchers and remove invalid ones.
     *
     * This is useful to call after cart modifications to ensure all vouchers
     * are still valid (e.g., minimum cart value still met).
     *
     * @return array<string> Array of voucher codes that were removed
     */
    public function validateAppliedVouchers(): array
    {
        $removedVouchers = [];

        foreach ($this->getAppliedVouchers() as $voucherCondition) {
            $code = $voucherCondition->getVoucherCode();
            $validationResult = Voucher::validate($code, $this);

            if (! $validationResult->isValid) {
                $this->removeVoucher($code);
                $removedVouchers[] = $code;
            }
        }

        return $removedVouchers;
    }
}
