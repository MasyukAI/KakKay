<?php

declare(strict_types=1);

use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Models\CartItem;

describe('CartItem Raw Methods', function () {
    beforeEach(function () {
        $this->item = new CartItem('product-1', 'Test Product', 100.00, 2);
        $this->discount = new CartCondition('discount', 'discount', 'subtotal', '-20%');
        $this->tax = new CartCondition('tax', 'tax', 'subtotal', '+10%');
    });

    it('returns correct raw price without conditions', function () {
        expect($this->item->getRawPriceWithoutConditions())->toBe(100.00);

        // Adding conditions shouldn't affect raw price without conditions
        $item = $this->item->addCondition($this->discount);
        expect($item->getRawPriceWithoutConditions())->toBe(100.00);
    });

    it('calculates raw price with single condition correctly', function () {
        $itemWithDiscount = $this->item->addCondition($this->discount);

        // Raw price with conditions: 100 - 20% = 80
        expect($itemWithDiscount->getRawPrice())->toBe(80.00);
    });

    it('calculates raw price with multiple conditions correctly', function () {
        $item = $this->item
            ->addCondition($this->discount)  // 100 - 20% = 80
            ->addCondition($this->tax);      // 80 + 10% = 88

        expect($item->getRawPrice())->toBe(88.00);
    });

    it('calculates raw price sum without conditions correctly', function () {
        // Price 100 * quantity 2 = 200
        expect($this->item->getRawPriceSumWithoutConditions())->toBe(200.00);

        // Adding conditions shouldn't affect raw price sum without conditions
        $item = $this->item->addCondition($this->discount);
        expect($item->getRawPriceSumWithoutConditions())->toBe(200.00);
    });

    it('calculates raw price sum with conditions correctly', function () {
        $itemWithDiscount = $this->item->addCondition($this->discount);

        // (100 - 20%) * 2 = 80 * 2 = 160
        expect($itemWithDiscount->getRawPriceSum())->toBe(160.00);
    });

    it('calculates raw price sum with multiple conditions correctly', function () {
        $item = $this->item
            ->addCondition($this->discount)  // 100 - 20% = 80
            ->addCondition($this->tax);      // 80 + 10% = 88

        // 88 * 2 = 176
        expect($item->getRawPriceSum())->toBe(176.00);
    });

    it('ensures raw methods return floats', function () {
        expect($this->item->getRawPriceWithoutConditions())->toBeFloat();
        expect($this->item->getRawPrice())->toBeFloat();
        expect($this->item->getRawPriceSumWithoutConditions())->toBeFloat();
        expect($this->item->getRawPriceSum())->toBeFloat();
    });

    it('handles edge cases with zero prices', function () {
        $freeItem = new CartItem('free-item', 'Free Item', 0.00, 1);

        expect($freeItem->getRawPriceWithoutConditions())->toBe(0.00);
        expect($freeItem->getRawPrice())->toBe(0.00);
        expect($freeItem->getRawPriceSumWithoutConditions())->toBe(0.00);
        expect($freeItem->getRawPriceSum())->toBe(0.00);
    });

    it('prevents negative prices from conditions', function () {
        $heavyDiscount = new CartCondition('heavy_discount', 'discount', 'subtotal', '-150%');
        $itemWithHeavyDiscount = $this->item->addCondition($heavyDiscount);

        // Should not go below 0
        expect($itemWithHeavyDiscount->getRawPrice())->toBe(0.00);
        expect($itemWithHeavyDiscount->getRawPriceSum())->toBe(0.00);
    });

    it('calculates discount amount correctly', function () {
        $itemWithDiscount = $this->item->addCondition($this->discount);

        // Original: 200, With conditions: 160, Discount: 40
        $expectedDiscount = 200.00 - 160.00;
        expect($itemWithDiscount->getDiscountAmount()->getAmount())->toBe($expectedDiscount);
    });
});
