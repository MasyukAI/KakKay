<?php

declare(strict_types=1);

use MasyukAI\Chip\Enums\SendInstructionState;

describe('SendInstructionState Enum', function () {
    it('has all 8 official CHIP send instruction states', function () {
        $expectedStates = [
            'received', 'enquiring', 'executing', 'reviewing',
            'accepted', 'completed', 'rejected', 'deleted',
        ];

        $actualStates = array_map(fn ($case) => $case->value, SendInstructionState::cases());

        expect($actualStates)->toHaveCount(8)
            ->and($actualStates)->toBe($expectedStates);
    });

    it('can be created from string value', function () {
        $state = SendInstructionState::from('completed');

        expect($state)->toBeInstanceOf(SendInstructionState::class)
            ->and($state->value)->toBe('completed');
    });

    it('provides human-readable labels', function () {
        expect(SendInstructionState::RECEIVED->label())->toBe('Received')
            ->and(SendInstructionState::ENQUIRING->label())->toBe('Enquiring')
            ->and(SendInstructionState::COMPLETED->label())->toBe('Completed');
    });

    it('correctly identifies successful states', function () {
        expect(SendInstructionState::COMPLETED->isSuccessful())->toBeTrue()
            ->and(SendInstructionState::ACCEPTED->isSuccessful())->toBeFalse()
            ->and(SendInstructionState::RECEIVED->isSuccessful())->toBeFalse()
            ->and(SendInstructionState::REJECTED->isSuccessful())->toBeFalse();
    });

    it('correctly identifies pending states', function () {
        expect(SendInstructionState::RECEIVED->isPending())->toBeTrue()
            ->and(SendInstructionState::ENQUIRING->isPending())->toBeTrue()
            ->and(SendInstructionState::EXECUTING->isPending())->toBeTrue()
            ->and(SendInstructionState::REVIEWING->isPending())->toBeTrue()
            ->and(SendInstructionState::COMPLETED->isPending())->toBeFalse();
    });

    it('correctly identifies failed states', function () {
        expect(SendInstructionState::REJECTED->isFailed())->toBeTrue()
            ->and(SendInstructionState::DELETED->isFailed())->toBeTrue()
            ->and(SendInstructionState::COMPLETED->isFailed())->toBeFalse();
    });

    it('correctly identifies which instructions can be deleted', function () {
        expect(SendInstructionState::RECEIVED->canBeDeleted())->toBeTrue()
            ->and(SendInstructionState::ENQUIRING->canBeDeleted())->toBeTrue()
            ->and(SendInstructionState::COMPLETED->canBeDeleted())->toBeFalse()
            ->and(SendInstructionState::REJECTED->canBeDeleted())->toBeFalse();
    });
});
