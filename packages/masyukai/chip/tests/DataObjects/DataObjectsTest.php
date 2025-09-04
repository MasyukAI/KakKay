<?php

use Masyukai\Chip\DataObjects\Purchase;
use Masyukai\Chip\DataObjects\Payment;
use Masyukai\Chip\DataObjects\Client;
use Masyukai\Chip\DataObjects\SendInstruction;
use Masyukai\Chip\DataObjects\BankAccount;
use Masyukai\Chip\DataObjects\Webhook;

describe('Purchase DataObject', function () {
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
            'updated_at' => '2024-01-01T12:00:00Z'
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
            'status' => 'created'
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
            'status' => 'created'
        ]);

        expect($purchase->getAmountInMajorUnits())->toBe(123.45);
    });
});

describe('Payment DataObject', function () {
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
            'paid_on' => 1640995800, // timestamp
            'remote_paid_on' => 1640995800
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
            'remote_paid_on' => null
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
            'remote_paid_on' => 1640995800
        ]);

        expect($payment->getNetAmountInMajorUnits())->toBe(99.50);
        expect($payment->getFeeAmountInMajorUnits())->toBe(0.50);
    });
});

describe('Client DataObject', function () {
    it('creates a client from array data', function () {
        $data = [
            'id' => 'client_123',
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+60123456789',
            'address' => [
                'line1' => '123 Main Street',
                'city' => 'Kuala Lumpur',
                'state' => 'Selangor',
                'country' => 'MY'
            ],
            'identity_type' => 'nric',
            'identity_number' => '123456-78-9012',
            'date_of_birth' => '1990-01-01',
            'nationality' => 'MY'
        ];

        $client = Client::fromArray($data);

        expect($client->id)->toBe('client_123');
        expect($client->fullName)->toBe('John Doe');
        expect($client->email)->toBe('john@example.com');
        expect($client->phone)->toBe('+60123456789');
        expect($client->address)->toBe([
            'line1' => '123 Main Street',
            'city' => 'Kuala Lumpur',
            'state' => 'Selangor',
            'country' => 'MY'
        ]);
        expect($client->identityType)->toBe('nric');
        expect($client->identityNumber)->toBe('123456-78-9012');
    });

    it('handles minimal client data', function () {
        $client = Client::fromArray([
            'id' => 'client_123',
            'full_name' => 'John Doe'
        ]);

        expect($client->fullName)->toBe('John Doe');
        expect($client->email)->toBeNull();
        expect($client->phone)->toBeNull();
        expect($client->address)->toBeNull();
    });
});

describe('SendInstruction DataObject', function () {
    it('creates a send instruction from array data', function () {
        $data = [
            'id' => 50,
            'bank_account_id' => 1,
            'amount' => '500.00',
            'state' => 'completed',
            'email' => 'test@example.com',
            'description' => 'Payment for services',
            'reference' => 'TRANSFER_001',
            'created_at' => '2023-07-20T10:41:25.190Z',
            'updated_at' => '2023-07-20T10:41:25.302Z',
        ];

        $instruction = SendInstruction::fromArray($data);

        expect($instruction->id)->toBe(50);
        expect($instruction->bank_account_id)->toBe(1);
        expect($instruction->amount)->toBe('500.00');
        expect($instruction->state)->toBe('completed');
        expect($instruction->email)->toBe('test@example.com');
        expect($instruction->description)->toBe('Payment for services');
        expect($instruction->reference)->toBe('TRANSFER_001');
        expect($instruction->created_at)->toBe('2023-07-20T10:41:25.190Z');
    });

    it('handles failed send instruction', function () {
        $instruction = SendInstruction::fromArray([
            'id' => 51,
            'bank_account_id' => 2,
            'amount' => '250.00',
            'state' => 'failed',
            'email' => 'test2@example.com',
            'description' => 'Another payment',
            'reference' => 'TRANSFER_002',
            'created_at' => '2023-07-20T11:41:25.190Z',
            'updated_at' => '2023-07-20T11:41:25.302Z',
        ]);

        expect($instruction->state)->toBe('failed');
        expect($instruction->isFailed())->toBeTrue();
        expect($instruction->isCompleted())->toBeFalse();
    });
});

describe('BankAccount DataObject', function () {
    it('creates a bank account from array data', function () {
        $data = [
            'id' => 84,
            'status' => 'verified',
            'account_number' => '157380111111',
            'bank_code' => 'MBBEMYKL',
            'group_id' => null,
            'name' => 'Ahmad Pintu',
            'reference' => null,
            'created_at' => '2023-07-20T08:59:10.766Z',
            'is_debiting_account' => false,
            'is_crediting_account' => false,
            'updated_at' => '2023-07-20T08:59:10.766Z',
            'deleted_at' => null,
            'rejection_reason' => null,
        ];

        $account = BankAccount::fromArray($data);

        expect($account->id)->toBe(84);
        expect($account->status)->toBe('verified');
        expect($account->account_number)->toBe('157380111111');
        expect($account->bank_code)->toBe('MBBEMYKL');
        expect($account->name)->toBe('Ahmad Pintu');
        expect($account->isVerified())->toBeTrue();
        expect($account->isPending())->toBeFalse();
    });

    it('handles unverified bank account', function () {
        $account = BankAccount::fromArray([
            'id' => 85,
            'status' => 'pending',
            'account_number' => '157380222222',
            'bank_code' => 'MBBEMYKL',
            'group_id' => null,
            'name' => 'Siti Aminah',
            'reference' => null,
            'created_at' => '2023-07-20T09:59:10.766Z',
            'is_debiting_account' => false,
            'is_crediting_account' => false,
            'updated_at' => '2023-07-20T09:59:10.766Z',
            'deleted_at' => null,
            'rejection_reason' => null,
        ]);

        expect($account->isVerified())->toBeFalse();
        expect($account->isPending())->toBeTrue();
        expect($account->deleted_at)->toBeNull();
    });
});

describe('Webhook DataObject', function () {
    it('creates a webhook from array data', function () {
        $data = [
            'event' => 'purchase.paid',
            'data' => [
                'id' => 'purchase_123',
                'amount_in_cents' => 10000,
                'currency' => 'MYR',
                'status' => 'paid'
            ],
            'timestamp' => '2024-01-01T16:00:00Z'
        ];

        $webhook = Webhook::fromArray($data);

        expect($webhook->event)->toBe('purchase.paid');
        expect($webhook->data)->toBe([
            'id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
            'status' => 'paid'
        ]);
        expect($webhook->timestamp)->toBe('2024-01-01T16:00:00Z');
    });

    it('extracts purchase from webhook data', function () {
        $webhook = Webhook::fromArray([
            'event' => 'purchase.created',
            'data' => [
                'id' => 'purchase_123',
                'amount_in_cents' => 10000,
                'currency' => 'MYR',
                'status' => 'created'
            ]
        ]);

        $purchase = $webhook->getPurchase();

        expect($purchase)->toBeInstanceOf(Purchase::class);
        expect($purchase->id)->toBe('purchase_123');
        expect($purchase->amountInCents)->toBe(10000);
    });

    it('returns null for non-purchase webhook events', function () {
        $webhook = Webhook::fromArray([
            'event' => 'send_instruction.completed',
            'data' => [
                'id' => 'send_123',
                'status' => 'completed'
            ]
        ]);

        $purchase = $webhook->getPurchase();

        expect($purchase)->toBeNull();
    });
});
