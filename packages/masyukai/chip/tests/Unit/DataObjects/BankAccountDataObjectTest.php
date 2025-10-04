<?php

declare(strict_types=1);

use MasyukAI\Chip\DataObjects\BankAccount;

describe('BankAccount data object', function () {
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
