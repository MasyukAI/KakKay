<?php

declare(strict_types=1);

use MasyukAI\Cart\Support\CartMoney;

it('demonstrates how money should be used with the cart package', function () {
    // Create Money objects for precise price handling
    $itemPrice = Money::fromMajorUnits(19.99, 'USD');
    $shippingCost = Money::fromMajorUnits(9.95, 'USD');
    $taxRate = 8.25; // 8.25%

    // Verify Money precision
    expect($itemPrice->getMajorUnits())->toBe(19.99);
    expect($itemPrice->getMinorUnits())->toBe(1999);
    expect($itemPrice->format())->toBe('$19.99');
});

it('shows money arithmetic capabilities', function () {
    $price1 = Money::fromMajorUnits(10.50, 'USD');
    $price2 = Money::fromMajorUnits(5.25, 'USD');

    // Basic arithmetic
    $sum = $price1->add($price2);
    expect($sum->getMajorUnits())->toBe(15.75);

    $difference = $price1->subtract($price2);
    expect($difference->getMajorUnits())->toBe(5.25);

    $doubled = $price1->multiply(2);
    expect($doubled->getMajorUnits())->toBe(21.0);

    $halved = $price1->divide(2);
    expect($halved->getMajorUnits())->toBe(5.25);

    // Percentage calculations (useful for tax/discounts)
    $tax = $price1->percentage(8.25);
    expect($tax->getMajorUnits())->toBe(0.87); // 8.25% of $10.50
});

it('demonstrates currency safety', function () {
    $usdPrice = Money::fromMajorUnits(100.00, 'USD');
    $eurPrice = Money::fromMajorUnits(85.00, 'EUR');

    // Verify currencies are preserved
    expect($usdPrice->getCurrency())->toBe('USD');
    expect($eurPrice->getCurrency())->toBe('EUR');

    // Currency mismatch throws exception
    expect(fn () => $usdPrice->add($eurPrice))
        ->toThrow(InvalidArgumentException::class, 'Currency mismatch: USD vs EUR');
});

it('shows how to create money from various inputs', function () {
    // From clean decimals
    $price1 = Money::fromMajorUnits(99.99, 'USD');
    expect($price1->format())->toBe('$99.99');

    // From minor units (cents)
    $price2 = Money::fromMinorUnits(9999, 'USD');
    expect($price2->format())->toBe('$99.99');
    expect($price1->equals($price2))->toBeTrue();

    // From formatted strings (cleans automatically)
    $price3 = Money::fromMajorUnits('$1,234.56', 'USD');
    expect($price3->getMajorUnits())->toBe(1234.56);
    expect($price3->format())->toBe('$1,234.56');
});

it('demonstrates precision advantages over floats', function () {
    // Float precision issue
    $floatResult = 0.1 + 0.2; // Often 0.30000000000000004
    expect($floatResult)->not->toBe(0.3);

    // Money precision - always exact
    $money1 = Money::fromMajorUnits(0.1, 'USD');
    $money2 = Money::fromMajorUnits(0.2, 'USD');
    $moneyResult = $money1->add($money2);

    expect($moneyResult->getMajorUnits())->toBe(0.3);
    expect($moneyResult->getMinorUnits())->toBe(30);
});

it('shows complex calculation scenarios', function () {
    // E-commerce scenario: cart total with tax and shipping
    $item1 = Money::fromMajorUnits(29.99, 'USD');
    $item2 = Money::fromMajorUnits(19.50, 'USD');

    // Calculate subtotal
    $subtotal = $item1->add($item2);
    expect($subtotal->getMajorUnits())->toBe(49.49);

    // Apply discount (15% off)
    $discount = $subtotal->percentage(15);
    $subtotalAfterDiscount = $subtotal->subtract($discount);
    expect($subtotalAfterDiscount->getMajorUnits())->toBe(42.07); // 49.49 - 7.42

    // Add tax (8.5%)
    $tax = $subtotalAfterDiscount->percentage(8.5);
    $subtotalWithTax = $subtotalAfterDiscount->add($tax);
    expect($subtotalWithTax->getMajorUnits())->toBe(45.65); // 42.07 + 3.58

    // Add shipping
    $shipping = Money::fromMajorUnits(9.95, 'USD');
    $grandTotal = $subtotalWithTax->add($shipping);
    expect($grandTotal->getMajorUnits())->toBe(55.60);
    expect($grandTotal->format())->toBe('$55.60');
});

it('demonstrates different currency formatting', function () {
    $usd = Money::fromMajorUnits(1234.56, 'USD');
    $eur = Money::fromMajorUnits(1234.56, 'EUR');
    $jpy = Money::fromMinorUnits(123456, 'JPY', 0); // No decimals for JPY

    expect($usd->format())->toBe('$1,234.56');
    expect($eur->format())->toBe('€1,234.56');
    expect($jpy->format())->toBe('¥123,456');
});
