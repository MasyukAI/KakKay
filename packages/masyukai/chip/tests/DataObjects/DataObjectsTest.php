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
            'id' => 'payment_123',
            'purchase_id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
            'status' => 'successful',
            'method' => 'fpx',
            'paid_at' => '2024-01-01T15:30:00Z',
            'transaction_fee_in_cents' => 50,
            'metadata' => ['gateway_reference' => 'TXN_456']
        ];

        $payment = Payment::fromArray($data);

        expect($payment->id)->toBe('payment_123');
        expect($payment->purchaseId)->toBe('purchase_123');
        expect($payment->amountInCents)->toBe(10000);
        expect($payment->status)->toBe('successful');
        expect($payment->method)->toBe('fpx');
        expect($payment->transactionFeeInCents)->toBe(50);
    });

    it('handles null payment method', function () {
        $payment = Payment::fromArray([
            'id' => 'payment_123',
            'purchase_id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
            'status' => 'pending'
        ]);

        expect($payment->method)->toBeNull();
        expect($payment->paidAt)->toBeNull();
    });

    it('calculates net amount after fees', function () {
        $payment = Payment::fromArray([
            'id' => 'payment_123',
            'purchase_id' => 'purchase_123',
            'amount_in_cents' => 10000,
            'currency' => 'MYR',
            'status' => 'successful',
            'transaction_fee_in_cents' => 50
        ]);

        expect($payment->getNetAmountInCents())->toBe(9950);
        expect($payment->getNetAmountInMajorUnits())->toBe(99.50);
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
            'id' => 'send_123',
            'reference' => 'TRANSFER_001',
            'amount_in_cents' => 50000,
            'currency' => 'MYR',
            'recipient_bank_account_id' => 'bank_account_456',
            'recipient_details' => [
                'name' => 'Jane Smith',
                'bank' => 'Maybank'
            ],
            'description' => 'Payment for services',
            'status' => 'pending',
            'metadata' => ['invoice_id' => 'INV_789'],
            'sent_at' => '2024-01-01T14:00:00Z',
            'completed_at' => null,
            'failure_reason' => null
        ];

        $instruction = SendInstruction::fromArray($data);

        expect($instruction->id)->toBe('send_123');
        expect($instruction->reference)->toBe('TRANSFER_001');
        expect($instruction->amountInCents)->toBe(50000);
        expect($instruction->currency)->toBe('MYR');
        expect($instruction->recipientBankAccountId)->toBe('bank_account_456');
        expect($instruction->description)->toBe('Payment for services');
        expect($instruction->status)->toBe('pending');
        expect($instruction->recipientDetails)->toBe([
            'name' => 'Jane Smith',
            'bank' => 'Maybank'
        ]);
    });

    it('handles failed send instruction', function () {
        $instruction = SendInstruction::fromArray([
            'id' => 'send_123',
            'amount_in_cents' => 50000,
            'currency' => 'MYR',
            'recipient_bank_account_id' => 'bank_account_456',
            'status' => 'failed',
            'failure_reason' => 'Insufficient funds'
        ]);

        expect($instruction->status)->toBe('failed');
        expect($instruction->failureReason)->toBe('Insufficient funds');
        expect($instruction->completedAt)->toBeNull();
    });
});

describe('BankAccount DataObject', function () {
    it('creates a bank account from array data', function () {
        $data = [
            'id' => 'bank_account_123',
            'bank_code' => 'MBBEMYKL',
            'account_number' => '1234567890123456',
            'account_holder_name' => 'John Doe',
            'account_type' => 'savings',
            'is_active' => true,
            'is_verified' => true,
            'verified_at' => '2024-01-01T10:00:00Z',
            'verification_details' => [
                'method' => 'micro_deposit',
                'verified_by' => 'system'
            ]
        ];

        $account = BankAccount::fromArray($data);

        expect($account->id)->toBe('bank_account_123');
        expect($account->bankCode)->toBe('MBBEMYKL');
        expect($account->accountNumber)->toBe('1234567890123456');
        expect($account->accountHolderName)->toBe('John Doe');
        expect($account->accountType)->toBe('savings');
        expect($account->isActive)->toBeTrue();
        expect($account->isVerified)->toBeTrue();
    });

    it('handles unverified bank account', function () {
        $account = BankAccount::fromArray([
            'id' => 'bank_account_123',
            'bank_code' => 'MBBEMYKL',
            'account_number' => '1234567890123456',
            'account_holder_name' => 'John Doe',
            'is_active' => true,
            'is_verified' => false
        ]);

        expect($account->isVerified)->toBeFalse();
        expect($account->verifiedAt)->toBeNull();
        expect($account->verificationDetails)->toBeNull();
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
