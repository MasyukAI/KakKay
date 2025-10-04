<?php

declare(strict_types=1);

use MasyukAI\Chip\Enums\BankAccountStatus;

describe('BankAccountStatus Enum', function () {
    it('has all 3 official CHIP bank account statuses', function () {
        $expectedStatuses = ['pending', 'verified', 'rejected'];

        $actualStatuses = array_map(fn ($case) => $case->value, BankAccountStatus::cases());

        expect($actualStatuses)->toHaveCount(3)
            ->and($actualStatuses)->toBe($expectedStatuses);
    });

    it('can be created from string value', function () {
        $status = BankAccountStatus::from('verified');

        expect($status)->toBeInstanceOf(BankAccountStatus::class)
            ->and($status->value)->toBe('verified');
    });

    it('provides human-readable labels', function () {
        expect(BankAccountStatus::VERIFIED->label())->toBe('Verified')
            ->and(BankAccountStatus::PENDING->label())->toBe('Pending Verification')
            ->and(BankAccountStatus::REJECTED->label())->toBe('Rejected');
    });

    it('correctly identifies verified status', function () {
        expect(BankAccountStatus::VERIFIED->isVerified())->toBeTrue()
            ->and(BankAccountStatus::PENDING->isVerified())->toBeFalse()
            ->and(BankAccountStatus::REJECTED->isVerified())->toBeFalse();
    });

    it('correctly identifies pending status', function () {
        expect(BankAccountStatus::PENDING->isPending())->toBeTrue()
            ->and(BankAccountStatus::VERIFIED->isPending())->toBeFalse()
            ->and(BankAccountStatus::REJECTED->isPending())->toBeFalse();
    });

    it('correctly identifies rejected status', function () {
        expect(BankAccountStatus::REJECTED->isRejected())->toBeTrue()
            ->and(BankAccountStatus::VERIFIED->isRejected())->toBeFalse()
            ->and(BankAccountStatus::PENDING->isRejected())->toBeFalse();
    });
});
