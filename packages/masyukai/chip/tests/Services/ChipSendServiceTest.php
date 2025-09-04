<?php

use Masyukai\Chip\DataObjects\SendInstruction;
use Masyukai\Chip\DataObjects\BankAccount;
use Masyukai\Chip\Services\ChipSendService;
use Masyukai\Chip\Clients\ChipSendClient;

describe('ChipSendService', function () {
    beforeEach(function () {
        $this->client = Mockery::mock(ChipSendClient::class);
        $this->service = new ChipSendService($this->client);
    });

    it('can create a send instruction', function () {
        $instructionData = [
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

        $this->client->shouldReceive('post')
            ->with('send/send_instructions', [
                'bank_account_id' => '1',
                'amount' => '500.00',
                'description' => 'Payment for services',
                'reference' => 'TRANSFER_001',
                'email' => 'test@example.com',
            ])
            ->andReturn($instructionData);

        $instruction = $this->service->createSendInstruction(
            50000, // amount in cents
            'MYR',
            '1', // recipient bank account id
            'Payment for services',
            'TRANSFER_001',
            'test@example.com'
        );

        expect($instruction)->toBeInstanceOf(SendInstruction::class);
        expect($instruction->id)->toBe(50);
        expect($instruction->amount)->toBe('500.00');
        expect($instruction->state)->toBe('completed');
        expect($instruction->description)->toBe('Payment for services');
        expect($instruction->reference)->toBe('TRANSFER_001');
        expect($instruction->email)->toBe('test@example.com');
    });

    it('can retrieve a send instruction', function () {
        $instructionData = [
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

        $this->client->shouldReceive('get')
            ->with("send/send_instructions/50")
            ->andReturn($instructionData);

        $instruction = $this->service->getSendInstruction('50');

        expect($instruction)->toBeInstanceOf(SendInstruction::class);
        expect($instruction->id)->toBe(50);
        expect($instruction->state)->toBe('completed');
    });

    it('can create a bank account', function () {
        $accountData = [
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

        $this->client->shouldReceive('post')
            ->with('send/bank_accounts', [
                'bank_code' => 'MBBEMYKL',
                'account_number' => '157380111111',
                'name' => 'Ahmad Pintu',
            ])
            ->andReturn($accountData);

        $account = $this->service->createBankAccount(
            'MBBEMYKL',
            '157380111111',
            'Ahmad Pintu',
            'savings'
        );

        expect($account)->toBeInstanceOf(BankAccount::class);
        expect($account->id)->toBe(84);
        expect($account->status)->toBe('verified');
        expect($account->account_number)->toBe('157380111111');
        expect($account->bank_code)->toBe('MBBEMYKL');
        expect($account->name)->toBe('Ahmad Pintu');
    });
});
