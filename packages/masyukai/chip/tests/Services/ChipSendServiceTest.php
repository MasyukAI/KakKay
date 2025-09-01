<?php

use Illuminate\Support\Facades\Http;
use Masyukai\Chip\DataObjects\SendInstruction;
use Masyukai\Chip\DataObjects\BankAccount;
use Masyukai\Chip\Services\ChipSendService;
use Masyukai\Chip\Clients\ChipSendClient;

beforeEach(function () {
    $this->client = Mockery::mock(ChipSendClient::class);
    $this->service = new ChipSendService($this->client);
});

describe('ChipSendService Send Instructions', function () {
    it('can create a send instruction', function () {
        $instructionData = [
            'id' => 'send_123',
            'reference' => 'TRANSFER_001',
            'amount_in_cents' => 50000,
            'currency' => 'MYR',
            'recipient_bank_account_id' => 'bank_account_456',
            'description' => 'Payment for services',
            'status' => 'pending'
        ];

        $this->client->shouldReceive('post')
            ->with('/send_instructions/', [
                'amount_in_cents' => 50000,
                'currency' => 'MYR',
                'recipient_bank_account_id' => 'bank_account_456',
                'description' => 'Payment for services',
                'reference' => 'TRANSFER_001'
            ])
            ->andReturn(['data' => $instructionData]);

        $instruction = $this->service->createSendInstruction(
            amountInCents: 50000,
            currency: 'MYR',
            recipientBankAccountId: 'bank_account_456',
            description: 'Payment for services',
            reference: 'TRANSFER_001'
        );

        expect($instruction)->toBeInstanceOf(SendInstruction::class);
        expect($instruction->id)->toBe('send_123');
        expect($instruction->amountInCents)->toBe(50000);
        expect($instruction->currency)->toBe('MYR');
        expect($instruction->status)->toBe('pending');
    });

    it('can retrieve a send instruction', function () {
        $instructionData = [
            'id' => 'send_123',
            'reference' => 'TRANSFER_001',
            'amount_in_cents' => 50000,
            'currency' => 'MYR',
            'recipient_bank_account_id' => 'bank_account_456',
            'status' => 'completed',
            'completed_at' => '2024-01-01T15:30:00Z'
        ];

        $this->client->shouldReceive('get')
            ->with('/send_instructions/send_123/')
            ->andReturn(['data' => $instructionData]);

        $instruction = $this->service->getSendInstruction('send_123');

        expect($instruction)->toBeInstanceOf(SendInstruction::class);
        expect($instruction->id)->toBe('send_123');
        expect($instruction->status)->toBe('completed');
    });

    it('can list send instructions', function () {
        $instructionsData = [
            [
                'id' => 'send_123',
                'amount_in_cents' => 50000,
                'currency' => 'MYR',
                'status' => 'completed'
            ],
            [
                'id' => 'send_456',
                'amount_in_cents' => 25000,
                'currency' => 'MYR',
                'status' => 'pending'
            ]
        ];

        $this->client->shouldReceive('get')
            ->with('/send_instructions/', ['limit' => 20, 'offset' => 0])
            ->andReturn(['data' => $instructionsData]);

        $instructions = $this->service->listSendInstructions();

        expect($instructions)->toHaveCount(2);
        expect($instructions[0])->toBeInstanceOf(SendInstruction::class);
        expect($instructions[0]->id)->toBe('send_123');
        expect($instructions[1]->id)->toBe('send_456');
    });

    it('can cancel a send instruction', function () {
        $cancelledData = [
            'id' => 'send_123',
            'status' => 'cancelled',
            'cancelled_at' => '2024-01-01T16:00:00Z'
        ];

        $this->client->shouldReceive('post')
            ->with('/send_instructions/send_123/cancel/')
            ->andReturn(['data' => $cancelledData]);

        $instruction = $this->service->cancelSendInstruction('send_123');

        expect($instruction)->toBeInstanceOf(SendInstruction::class);
        expect($instruction->status)->toBe('cancelled');
    });
});

describe('ChipSendService Bank Account Management', function () {
    it('can create a bank account', function () {
        $accountData = [
            'id' => 'bank_account_123',
            'bank_code' => 'MBBEMYKL',
            'account_number' => '1234567890123456',
            'account_holder_name' => 'John Doe',
            'account_type' => 'savings'
        ];

        $this->client->shouldReceive('post')
            ->with('/bank_accounts/', [
                'bank_code' => 'MBBEMYKL',
                'account_number' => '1234567890123456',
                'account_holder_name' => 'John Doe',
                'account_type' => 'savings'
            ])
            ->andReturn(['data' => $accountData]);

        $account = $this->service->createBankAccount(
            bankCode: 'MBBEMYKL',
            accountNumber: '1234567890123456',
            accountHolderName: 'John Doe',
            accountType: 'savings'
        );

        expect($account)->toBeInstanceOf(BankAccount::class);
        expect($account->id)->toBe('bank_account_123');
        expect($account->bankCode)->toBe('MBBEMYKL');
        expect($account->accountNumber)->toBe('1234567890123456');
        expect($account->accountHolderName)->toBe('John Doe');
    });

    it('can retrieve a bank account', function () {
        $accountData = [
            'id' => 'bank_account_123',
            'bank_code' => 'MBBEMYKL',
            'account_number' => '1234567890123456',
            'account_holder_name' => 'John Doe',
            'account_type' => 'savings',
            'is_verified' => true
        ];

        $this->client->shouldReceive('get')
            ->with('/bank_accounts/bank_account_123/')
            ->andReturn(['data' => $accountData]);

        $account = $this->service->getBankAccount('bank_account_123');

        expect($account)->toBeInstanceOf(BankAccount::class);
        expect($account->id)->toBe('bank_account_123');
        expect($account->isVerified)->toBeTrue();
    });

    it('can list bank accounts', function () {
        $accountsData = [
            [
                'id' => 'bank_account_123',
                'bank_code' => 'MBBEMYKL',
                'account_holder_name' => 'John Doe'
            ],
            [
                'id' => 'bank_account_456',
                'bank_code' => 'HLBBMYKL',
                'account_holder_name' => 'Jane Smith'
            ]
        ];

        $this->client->shouldReceive('get')
            ->with('/bank_accounts/', ['limit' => 20, 'offset' => 0])
            ->andReturn(['data' => $accountsData]);

        $accounts = $this->service->listBankAccounts();

        expect($accounts)->toHaveCount(2);
        expect($accounts[0])->toBeInstanceOf(BankAccount::class);
        expect($accounts[0]->accountHolderName)->toBe('John Doe');
        expect($accounts[1]->accountHolderName)->toBe('Jane Smith');
    });

    it('can verify a bank account', function () {
        $verifiedData = [
            'id' => 'bank_account_123',
            'bank_code' => 'MBBEMYKL',
            'account_number' => '1234567890123456',
            'account_holder_name' => 'John Doe',
            'is_verified' => true,
            'verified_at' => '2024-01-01T12:00:00Z'
        ];

        $this->client->shouldReceive('post')
            ->with('/bank_accounts/bank_account_123/verify/')
            ->andReturn(['data' => $verifiedData]);

        $account = $this->service->verifyBankAccount('bank_account_123');

        expect($account)->toBeInstanceOf(BankAccount::class);
        expect($account->isVerified)->toBeTrue();
    });

    it('can delete a bank account', function () {
        $this->client->shouldReceive('delete')
            ->with('/bank_accounts/bank_account_123/')
            ->andReturn([]);

        $result = $this->service->deleteBankAccount('bank_account_123');

        expect($result)->toBeTrue();
    });
});

describe('ChipSendService Balance and Limits', function () {
    it('can get account balance', function () {
        $balanceData = [
            'available_balance_in_cents' => 100000,
            'pending_balance_in_cents' => 5000,
            'currency' => 'MYR'
        ];

        $this->client->shouldReceive('get')
            ->with('/balance/')
            ->andReturn(['data' => $balanceData]);

        $balance = $this->service->getBalance();

        expect($balance['available_balance_in_cents'])->toBe(100000);
        expect($balance['pending_balance_in_cents'])->toBe(5000);
        expect($balance['currency'])->toBe('MYR');
    });

    it('can get send limits', function () {
        $limitsData = [
            'daily_limit_in_cents' => 1000000,
            'monthly_limit_in_cents' => 10000000,
            'remaining_daily_in_cents' => 500000,
            'remaining_monthly_in_cents' => 7500000
        ];

        $this->client->shouldReceive('get')
            ->with('/send_limits/')
            ->andReturn(['data' => $limitsData]);

        $limits = $this->service->getSendLimits();

        expect($limits['daily_limit_in_cents'])->toBe(1000000);
        expect($limits['remaining_daily_in_cents'])->toBe(500000);
    });

    it('can request send limit increase', function () {
        $requestData = [
            'requested_daily_limit_in_cents' => 2000000,
            'requested_monthly_limit_in_cents' => 20000000,
            'business_justification' => 'Increased business volume'
        ];

        $responseData = [
            'request_id' => 'limit_request_123',
            'status' => 'pending_review',
            'submitted_at' => '2024-01-01T12:00:00Z'
        ];

        $this->client->shouldReceive('post')
            ->with('/send_limits/increase/', $requestData)
            ->andReturn(['data' => $responseData]);

        $response = $this->service->requestSendLimitIncrease(
            requestedDailyLimitInCents: 2000000,
            requestedMonthlyLimitInCents: 20000000,
            businessJustification: 'Increased business volume'
        );

        expect($response['request_id'])->toBe('limit_request_123');
        expect($response['status'])->toBe('pending_review');
    });
});
