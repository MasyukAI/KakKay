<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Tests\TestCase;

uses(TestCase::class);

it('debugs cart manager behavior', function () {
    // Test 1: Basic add and count
    Cart::setInstance('test1')->add('product-1', 'Product 1', 10.0, 1);
    $count1 = Cart::setInstance('test1')->count();
    expect($count1)->toBe(1);

    // Test 2: Different instances
    Cart::setInstance('test2')->add('product-2', 'Product 2', 20.0, 2);
    $count2 = Cart::setInstance('test2')->count();
    expect($count2)->toBe(2);

    // Test 3: Check isolation
    $test1Count = Cart::setInstance('test1')->count();
    expect($test1Count)->toBe(1);

    // Test 4: Check current instance
    $currentInstance = Cart::instance();
    expect($currentInstance)->toBeString();

    // Test 5: Check global state
    Cart::setInstance('global');
    $globalInstance = Cart::instance();
    expect($globalInstance)->toBe('global');

    // Test 6: Check if calling setInstance twice in a row works
    $test1CountAgain = Cart::setInstance('test1')->count();
    expect($test1CountAgain)->toBe(1);

    // Test 7: Check what happens if we call count() without chaining
    Cart::setInstance('test1');
    $directCount = Cart::count();
    expect($directCount)->toBe(1);
});
