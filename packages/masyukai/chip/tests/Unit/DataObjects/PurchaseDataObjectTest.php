<?php

declare(strict_types=1);

use MasyukAI\Chip\DataObjects\Purchase;

describe('Purchase data object', function () {
    it('creates a purchase from array data', function () {
        $data = [
            'id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
            'reference' => 'ORDER_001',
            'checkout_url' => 'https://gate-sandbox.chip-in.asia/checkout/purchase_123',
            'status' => 'created',
            'is_recurring' => false,
            'metadata' => ['order_id' => '123'],
            'created_at' => '2024-01-01T12:00:00Z',
            'updated_at' => '2024-01-01T12:00:00Z',
        ];

        $purchase = Purchase::fromArray($data);

        expect($purchase->id)->toBe('purchase_123');
        expect($purchase->amountInCents)->toBe(10000);
        expect($purchase->currency)->toBe('MYR');
        expect($purchase->reference)->toBe('ORDER_001');
        expect($purchase->checkoutUrl)->toBe('https://gate-sandbox.chip-in.asia/checkout/purchase_123');
        expect($purchase->status)->toBe('created');
        expect($purchase->isRecurring)->toBeFalse();
        expect($purchase->metadata)->toBe(['order_id' => '123']);
    });

    it('handles nullable fields correctly', function () {
        $data = [
            'id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
            'status' => 'created',
        ];

        $purchase = Purchase::fromArray($data);

        expect($purchase->reference)->toBeNull();
        expect($purchase->checkoutUrl)->toBeNull();
        expect($purchase->metadata)->toBeNull();
        expect($purchase->clientId)->toBeNull();
    });

    it('calculates amount in major currency units', function () {
        $purchase = Purchase::fromArray([
            'id' => 'purchase_123',
            'amount_in_cents' => 12345,
            'currency' => 'MYR',
            'status' => 'created',
        ]);

        expect($purchase->getAmountInMajorUnits())->toBe(123.45);
    });
});
