<?php

declare(strict_types=1);

use MasyukAI\FilamentCartPlugin\Models\Cart;
use MasyukAI\FilamentCartPlugin\Models\CartCondition;

beforeEach(function () {
    // Clear any existing data
    Cart::query()->delete();
    CartCondition::query()->delete();
});

it('can filter carts by item count', function () {
    // Create carts with different item counts
    $cart1 = Cart::factory()->create([
        'items' => [
            ['id' => '1', 'name' => 'Product 1', 'price' => 10, 'quantity' => 1],
            ['id' => '2', 'name' => 'Product 2', 'price' => 20, 'quantity' => 1],
        ]
    ]);

    $cart2 = Cart::factory()->create([
        'items' => [
            ['id' => '1', 'name' => 'Product 1', 'price' => 10, 'quantity' => 1],
        ]
    ]);

    $cart3 = Cart::factory()->create([
        'items' => [
            ['id' => '1', 'name' => 'Product 1', 'price' => 10, 'quantity' => 1],
            ['id' => '2', 'name' => 'Product 2', 'price' => 20, 'quantity' => 1],
            ['id' => '3', 'name' => 'Product 3', 'price' => 30, 'quantity' => 1],
        ]
    ]);

    // Test filtering by exact item count
    $cartsWithTwoItems = Cart::withItemCount(2)->get();
    expect($cartsWithTwoItems)->toHaveCount(1);
    expect($cartsWithTwoItems->first()->id)->toBe($cart1->id);

    // Test filtering by greater than
    $cartsWithMoreThanOne = Cart::withItemCount(1, '>')->get();
    expect($cartsWithMoreThanOne)->toHaveCount(2);
});

it('can filter carts by product', function () {
    $cart1 = Cart::factory()->create([
        'items' => [
            ['id' => 'product-123', 'name' => 'Product 123', 'price' => 10, 'quantity' => 1],
            ['id' => 'product-456', 'name' => 'Product 456', 'price' => 20, 'quantity' => 1],
        ]
    ]);

    $cart2 = Cart::factory()->create([
        'items' => [
            ['id' => 'product-789', 'name' => 'Product 789', 'price' => 30, 'quantity' => 1],
        ]
    ]);

    // Test filtering by specific product
    $cartsWithProduct123 = Cart::withProduct('product-123')->get();
    expect($cartsWithProduct123)->toHaveCount(1);
    expect($cartsWithProduct123->first()->id)->toBe($cart1->id);

    // Test filtering by multiple products
    $cartsWithAnyProduct = Cart::withAnyProduct(['product-123', 'product-789'])->get();
    expect($cartsWithAnyProduct)->toHaveCount(2);
});

it('can filter carts by subtotal range', function () {
    $cart1 = Cart::factory()->create([
        'items' => [
            ['id' => '1', 'name' => 'Product 1', 'price' => 10, 'quantity' => 2], // $20 total
        ]
    ]);

    $cart2 = Cart::factory()->create([
        'items' => [
            ['id' => '1', 'name' => 'Product 1', 'price' => 25, 'quantity' => 2], // $50 total
        ]
    ]);

    $cart3 = Cart::factory()->create([
        'items' => [
            ['id' => '1', 'name' => 'Product 1', 'price' => 50, 'quantity' => 2], // $100 total
        ]
    ]);

    // Test filtering by subtotal range
    $cartsInRange = Cart::withSubtotalBetween(30, 80)->get();
    expect($cartsInRange)->toHaveCount(1);
    expect($cartsInRange->first()->id)->toBe($cart2->id);
});

it('can manage cart conditions', function () {
    $condition = CartCondition::factory()->active()->create([
        'name' => 'test-discount',
        'type' => 'static',
        'target' => 'subtotal',
        'value' => '-10%',
    ]);

    expect($condition->isDiscount())->toBeTrue();
    expect($condition->isPercentage())->toBeTrue();
    expect($condition->isCharge())->toBeFalse();

    $feeCondition = CartCondition::factory()->create([
        'value' => '+5',
    ]);

    expect($feeCondition->isCharge())->toBeTrue();
    expect($feeCondition->isDiscount())->toBeFalse();
});

it('can calculate cart computed properties', function () {
    $cart = Cart::factory()->create([
        'items' => [
            [
                'id' => '1', 
                'name' => 'Product 1', 
                'price' => 10, 
                'quantity' => 2,
                'attributes' => ['weight' => 5.5]
            ],
            [
                'id' => '2', 
                'name' => 'Product 2', 
                'price' => 25, 
                'quantity' => 1,
                'attributes' => ['weight' => 2.0]
            ],
        ]
    ]);

    expect($cart->items_count)->toBe(2);
    expect($cart->total_quantity)->toBe(3);
    expect($cart->subtotal)->toBe(45.0); // (10*2) + (25*1)
    expect($cart->total_weight)->toBe(13.0); // (5.5*2) + (2.0*1)
    expect($cart->product_ids)->toBe(['1', '2']);
});

it('can filter carts by conditions', function () {
    $cart1 = Cart::factory()->create([
        'conditions' => [
            ['name' => 'test-discount', 'type' => 'static', 'value' => '-10%'],
        ]
    ]);

    $cart2 = Cart::factory()->create([
        'conditions' => [
            ['name' => 'shipping-fee', 'type' => 'dynamic', 'value' => '+5'],
        ]
    ]);

    $cart3 = Cart::factory()->create([
        'conditions' => []
    ]);

    // Test filtering by condition name
    $cartsWithDiscount = Cart::withCondition('test-discount')->get();
    expect($cartsWithDiscount)->toHaveCount(1);
    expect($cartsWithDiscount->first()->id)->toBe($cart1->id);

    // Test filtering by condition type
    $cartsWithStatic = Cart::withConditionType('static')->get();
    expect($cartsWithStatic)->toHaveCount(1);

    $cartsWithDynamic = Cart::withDynamicConditions()->get();
    expect($cartsWithDynamic)->toHaveCount(1);
    expect($cartsWithDynamic->first()->id)->toBe($cart2->id);
});