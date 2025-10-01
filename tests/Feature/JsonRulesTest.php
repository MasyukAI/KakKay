<?php

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Storage\SessionStorage;
use MasyukAI\FilamentCart\Models\Condition;
use MasyukAI\FilamentCart\Services\RuleConverter;

beforeEach(function () {
    // Run the conditions table migration
    $migration = include __DIR__.'/../../packages/masyukai/filament-cart/database/migrations/2025_09_29_184331_create_conditions_table.php';
    $migration->up();

    $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
    $this->storage = new SessionStorage($sessionStore);
    $this->cart = new Cart($this->storage, 'json_rules_test');
});

it('converts JSON rules to callable functions', function () {
    $rules = [
        'min_total' => 100,
        'min_items' => 3,
    ];

    $callables = RuleConverter::convertRules($rules);

    expect($callables)->toHaveCount(2);
    expect($callables[0])->toBeCallable();
    expect($callables[1])->toBeCallable();
});

it('min_total rule works correctly', function () {
    // Add item worth $50
    $this->cart->add('item1', 'Test Item', 50.00, 1);

    $rules = ['min_total' => 100];
    $callables = RuleConverter::convertRules($rules);

    // Cart total is $50, should not meet $100 minimum
    expect($callables[0]($this->cart))->toBeFalse();

    // Add another item to make total $150
    $this->cart->add('item2', 'Test Item 2', 100.00, 1);

    // Now cart total is $150, should meet $100 minimum
    expect($callables[0]($this->cart))->toBeTrue();
});

it('min_items rule works correctly', function () {
    // Add 2 items
    $this->cart->add('item1', 'Test Item 1', 10.00, 1);
    $this->cart->add('item2', 'Test Item 2', 10.00, 1);

    $rules = ['min_items' => 3];
    $callables = RuleConverter::convertRules($rules);

    // Cart has 2 items, should not meet 3 item minimum
    expect($callables[0]($this->cart))->toBeFalse();

    // Add third item
    $this->cart->add('item3', 'Test Item 3', 10.00, 1);

    // Now cart has 3 items, should meet minimum
    expect($callables[0]($this->cart))->toBeTrue();
});

it('condition with JSON rules creates working CartCondition', function () {
    // Create a condition with JSON rules
    $condition = Condition::factory()->create([
        'name' => 'test_discount',
        'display_name' => 'Test Discount',
        'type' => 'discount',
        'target' => 'total',
        'value' => '-10%',
        'is_dynamic' => true,
        'rules' => [
            'min_total' => 100,
            'min_items' => 2,
        ],
    ]);

    // Create CartCondition from the model
    $cartCondition = $condition->createCondition();

    // Verify it's dynamic and has rules
    expect($cartCondition->isDynamic())->toBeTrue();
    expect($cartCondition->getRules())->toBeArray();
    expect($cartCondition->getRules())->toHaveCount(2);

    // Test with cart that doesn't meet rules
    $this->cart->add('item1', 'Test Item', 50.00, 1);
    expect($cartCondition->shouldApply($this->cart))->toBeFalse();

    // Test with cart that meets rules
    $this->cart->add('item2', 'Test Item 2', 60.00, 1);
    expect($cartCondition->shouldApply($this->cart))->toBeTrue();
});

it('handles unknown rule types', function () {
    $rules = ['unknown_rule' => 'value'];

    expect(fn () => RuleConverter::convertRules($rules))
        ->toThrow(\InvalidArgumentException::class, 'Unknown rule type: unknown_rule');
});

it('handles legacy rule format with type and threshold', function () {
    // Legacy format: {"type": "min_item_count", "threshold": 3}
    $legacyRules = [
        'type' => 'min_item_count',
        'threshold' => 3,
    ];

    $callables = RuleConverter::convertRules($legacyRules);

    expect($callables)->toHaveCount(1);
    expect($callables[0])->toBeCallable();

    // Test with cart that doesn't meet threshold
    $this->cart->add('item1', 'Test Item 1', 10.00, 1);
    $this->cart->add('item2', 'Test Item 2', 10.00, 1);
    expect($callables[0]($this->cart))->toBeFalse();

    // Test with cart that meets threshold
    $this->cart->add('item3', 'Test Item 3', 10.00, 1);
    expect($callables[0]($this->cart))->toBeTrue();
});

it('handles legacy min_total rule format', function () {
    $legacyRules = [
        'type' => 'min_total',
        'threshold' => 100,
    ];

    $callables = RuleConverter::convertRules($legacyRules);

    // Cart with $50
    $this->cart->add('item1', 'Test Item', 50.00, 1);
    expect($callables[0]($this->cart))->toBeFalse();

    // Cart with $150
    $this->cart->add('item2', 'Test Item 2', 100.00, 1);
    expect($callables[0]($this->cart))->toBeTrue();
});
