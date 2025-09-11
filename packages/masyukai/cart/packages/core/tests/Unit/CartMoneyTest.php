<?php

declare(strict_types=1);

use MasyukAI\Cart\Support\CartMoney;

it('creates money from major units', function () {
    $money = Money::fromMajorUnits(99.99, 'USD');

    expect($money->getMajorUnits())->toBe(99.99);
    expect($money->getMinorUnits())->toBe(9999);
    expect($money->getCurrency())->toBe('USD');
});

it('creates money from minor units', function () {
    $money = Money::fromMinorUnits(9999, 'USD');

    expect($money->getMajorUnits())->toBe(99.99);
    expect($money->getMinorUnits())->toBe(9999);
});

it('performs arithmetic operations correctly', function () {
    $money1 = Money::fromMajorUnits(10.50, 'USD');
    $money2 = Money::fromMajorUnits(5.25, 'USD');

    $sum = CartMoney::fromMajorUnits(10.50, 'USD')->add(CartMoney::fromMajorUnits(5.25, 'USD'));
    expect($sum->getMajorUnits())->toBe(15.75);

    $difference = $money1->subtract($money2);
    expect($difference->getMajorUnits())->toBe(5.25);

    $product = $money1->multiply(2);
    expect($product->getMajorUnits())->toBe(21.0);

    $quotient = $money1->divide(2);
    expect($quotient->getMajorUnits())->toBe(5.25);
});

it('handles percentage calculations', function () {
    $money = Money::fromMajorUnits(100, 'USD');

    // Manual percentage calculations
    $discount = CartMoney::fromMajorUnits(100, 'USD')->multiply(0.10); // 10%
    expect($discount->getMajorUnits())->toBe(10.0);

    $tax = $money->multiply(0.0825); // 8.25%
    expect($tax->getMajorUnits())->toBe(8.25);
});

it('compares money amounts correctly', function () {
    $money1 = Money::fromMajorUnits(10.50, 'USD');
    $money2 = Money::fromMajorUnits(5.25, 'USD');
    $money3 = Money::fromMajorUnits(10.50, 'USD');

    expect($money1->greaterThan($money2))->toBeTrue();
    expect($money2->lessThan($money1))->toBeTrue();
    expect($money1->equals($money3))->toBeTrue();
    expect($money1->isPositive())->toBeTrue();

    $zeroMoney = Money::fromMajorUnits(0, 'USD');
    expect($zeroMoney->isZeroOrNegative())->toBeTrue();
});

it('throws exception for currency mismatch', function () {
    $usd = Money::fromMajorUnits(10, 'USD');
    $eur = Money::fromMajorUnits(10, 'EUR');

    expect(fn () => CartMoney::fromMajorUnits(10, 'USD')->add(CartMoney::fromMajorUnits(10, 'EUR')))->toThrow(InvalidArgumentException::class);
});

it('formats currency correctly', function () {
    $money = Money::fromMajorUnits(1234.56, 'USD');

    $formatted = $money->format('en_US');
    expect($formatted)->toContain('$1,234.56');
});

it('serializes to json correctly', function () {
    $money = Money::fromMajorUnits(99.99, 'USD');

    $json = json_encode($money);
    $data = json_decode($json, true);

    expect($data)->toHaveKey('amount', 9999);
    expect($data)->toHaveKey('currency', 'USD');
    expect($data)->toHaveKey('precision', 2);
    expect($data)->toHaveKey('formatted');
});

it('handles rounding correctly', function () {
    $money = Money::fromMajorUnits(10.555, 'USD'); // Should round to 10.56

    expect(CartMoney::fromMajorUnits(10.555, 'USD')->getMajorUnits())->toBe(10.56);
    expect($money->getMinorUnits())->toBe(1056);
});

it('converts string amounts correctly', function () {
    $money = Money::fromMajorUnits('$1,234.56', 'USD');

    expect(CartMoney::fromMajorUnits('$1,234.56', 'USD')->getMajorUnits())->toBe(1234.56);
    expect($money->getMinorUnits())->toBe(123456);
});
