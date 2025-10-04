<?php

declare(strict_types=1);

use MasyukAI\Chip\DataObjects\SendInstruction;

describe('SendInstruction data object', function () {
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

    it('handles rejected send instruction', function () {
        $instruction = SendInstruction::fromArray([
            'id' => 51,
            'bank_account_id' => 2,
            'amount' => '250.00',
            'state' => 'rejected',
            'email' => 'test2@example.com',
            'description' => 'Another payment',
            'reference' => 'TRANSFER_002',
            'created_at' => '2023-07-20T11:41:25.190Z',
            'updated_at' => '2023-07-20T11:41:25.302Z',
        ]);

        expect($instruction->state)->toBe('rejected');
        expect($instruction->isRejected())->toBeTrue();
        expect($instruction->isCompleted())->toBeFalse();
    });
});
