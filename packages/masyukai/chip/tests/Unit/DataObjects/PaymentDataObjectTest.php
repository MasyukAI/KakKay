<?php

declare(strict_types=1);

use MasyukAI\Chip\DataObjects\Payment;

describe('Payment data object', function () {
    it('creates a payment from array data', function () {
        $data = [
            'is_outgoing' => false,
            'payment_type' => 'purchase',
            'amount' => 10000,
            'currency' => 'MYR',
            'net_amount' => 9950,
            'fee_amount' => 50,
            'pending_amount' => 0,
            'pending_unfreeze_on' => null,
            'description' => 'Test payment',
            'paid_on' => 1640995800,
            'remote_paid_on' => 1640995800,
        ];

        $payment = Payment::fromArray($data);

        expect($payment->payment_type)->toBe('purchase');
        expect($payment->amount)->toBe(10000);
        expect($payment->net_amount)->toBe(9950);
        expect($payment->fee_amount)->toBe(50);
        expect($payment->currency)->toBe('MYR');
        expect($payment->description)->toBe('Test payment');
        expect($payment->paid_on)->toBe(1640995800);
    });

    it('handles null payment method', function () {
        $payment = Payment::fromArray([
            'is_outgoing' => false,
            'payment_type' => 'purchase',
            'amount' => 10000,
            'currency' => 'MYR',
            'net_amount' => 10000,
            'fee_amount' => 0,
            'pending_amount' => 0,
            'pending_unfreeze_on' => null,
            'description' => null,
            'paid_on' => null,
            'remote_paid_on' => null,
        ]);

        expect($payment->description)->toBeNull();
        expect($payment->paid_on)->toBeNull();
    });

    it('calculates net amount after fees', function () {
        $payment = Payment::fromArray([
            'is_outgoing' => false,
            'payment_type' => 'purchase',
            'amount' => 10000,
            'currency' => 'MYR',
            'net_amount' => 9950,
            'fee_amount' => 50,
            'pending_amount' => 0,
            'pending_unfreeze_on' => null,
            'description' => 'Test payment',
            'paid_on' => 1640995800,
            'remote_paid_on' => 1640995800,
        ]);

        expect($payment->getNetAmountInMajorUnits())->toBe(99.50);
        expect($payment->getFeeAmountInMajorUnits())->toBe(0.50);
    });
});
