<?php

declare(strict_types=1);

use MasyukAI\Cart\Exceptions\CartConflictException;

it('creates cart conflict exception with proper data', function () {
    $exception = new CartConflictException(
        'Cart was modified',
        1, // attempted version
        2  // current version
    );

    expect($exception->getAttemptedVersion())->toBe(1);
    expect($exception->getCurrentVersion())->toBe(2);
    expect($exception->getVersionDifference())->toBe(1);
    expect($exception->isMinorConflict())->toBeTrue();
});

it('identifies major conflicts correctly', function () {
    $exception = new CartConflictException(
        'Cart was modified',
        1, // attempted version
        5  // current version
    );

    expect($exception->getVersionDifference())->toBe(4);
    expect($exception->isMinorConflict())->toBeFalse();
});

it('provides resolution suggestions', function () {
    $minorConflict = new CartConflictException(
        'Cart was modified',
        1, // attempted version
        2  // current version
    );

    $suggestions = $minorConflict->getResolutionSuggestions();
    expect($suggestions)->toContain('retry_with_refresh');
    expect($suggestions)->toContain('merge_changes');

    $majorConflict = new CartConflictException(
        'Cart was modified',
        1, // attempted version
        5  // current version
    );

    $suggestions = $majorConflict->getResolutionSuggestions();
    expect($suggestions)->toContain('reload_cart');
    expect($suggestions)->toContain('manual_resolution_required');
});

it('converts to array for api responses', function () {
    $exception = new CartConflictException(
        'Cart was modified',
        1, // attempted version
        2  // current version
    );

    $array = $exception->toArray();

    expect($array)->toHaveKey('error', 'cart_conflict');
    expect($array)->toHaveKey('attempted_version', 1);
    expect($array)->toHaveKey('current_version', 2);
    expect($array)->toHaveKey('is_minor_conflict', true);
    expect($array)->toHaveKey('resolution_suggestions');
    expect($array)->toHaveKey('timestamp');
});
