<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCartPlugin\Tests\Feature;

use MasyukAI\FilamentCartPlugin\Models\Cart;
use MasyukAI\FilamentCartPlugin\Tests\TestCase;

class CartCollectionIntegrationTest extends TestCase
{
    /** @test */
    public function it_can_get_cart_items_as_collection(): void
    {
        $cart = Cart::factory()->create([
            'items' => [
                [
                    'id' => 'item-1',
                    'name' => 'Test Item 1',
                    'price' => 100.0,
                    'quantity' => 2,
                    'attributes' => ['color' => 'red'],
                    'conditions' => [
                        'discount' => [
                            'name' => 'discount',
                            'type' => 'discount',
                            'target' => 'subtotal',
                            'value' => '-10%',
                            'attributes' => [],
                            'order' => 0,
                        ],
                    ],
                ],
                [
                    'id' => 'item-2', 
                    'name' => 'Test Item 2',
                    'price' => 50.0,
                    'quantity' => 1,
                    'attributes' => ['size' => 'large'],
                    'conditions' => [
                        'tax' => [
                            'name' => 'tax',
                            'type' => 'tax',
                            'target' => 'total',
                            'value' => '+5',
                            'attributes' => [],
                            'order' => 1,
                        ],
                    ],
                ],
            ],
        ]);

        $collection = $cart->getItemsCollection();

        $this->assertEquals(2, $collection->count());
        $this->assertTrue($collection->hasItem('item-1'));
        $this->assertTrue($collection->hasItem('item-2'));
    }

    /** @test */
    public function it_can_filter_items_by_condition_type_using_built_in_methods(): void
    {
        $cart = Cart::factory()->create([
            'items' => [
                [
                    'id' => 'item-1',
                    'name' => 'Test Item 1',
                    'price' => 100.0,
                    'quantity' => 2,
                    'attributes' => [],
                    'conditions' => [
                        'discount' => [
                            'name' => 'discount',
                            'type' => 'discount',
                            'target' => 'subtotal',
                            'value' => '-10%',
                            'attributes' => [],
                            'order' => 0,
                        ],
                    ],
                ],
                [
                    'id' => 'item-2',
                    'name' => 'Test Item 2',
                    'price' => 50.0,
                    'quantity' => 1,
                    'attributes' => [],
                    'conditions' => [
                        'tax' => [
                            'name' => 'tax',
                            'type' => 'tax',
                            'target' => 'total',
                            'value' => '+5',
                            'attributes' => [],
                            'order' => 1,
                        ],
                    ],
                ],
            ],
        ]);

        $discountItems = $cart->getItemsByConditionType('discount');
        $taxItems = $cart->getItemsByConditionType('tax');

        $this->assertEquals(1, $discountItems->count());
        $this->assertTrue($discountItems->hasItem('item-1'));
        
        $this->assertEquals(1, $taxItems->count());
        $this->assertTrue($taxItems->hasItem('item-2'));
    }

    /** @test */
    public function it_can_filter_items_by_condition_target_using_built_in_methods(): void
    {
        $cart = Cart::factory()->create([
            'items' => [
                [
                    'id' => 'item-1',
                    'name' => 'Test Item 1',
                    'price' => 100.0,
                    'quantity' => 2,
                    'attributes' => [],
                    'conditions' => [
                        'discount' => [
                            'name' => 'discount',
                            'type' => 'discount',
                            'target' => 'subtotal',
                            'value' => '-10%',
                            'attributes' => [],
                            'order' => 0,
                        ],
                    ],
                ],
                [
                    'id' => 'item-2',
                    'name' => 'Test Item 2',
                    'price' => 50.0,
                    'quantity' => 1,
                    'attributes' => [],
                    'conditions' => [
                        'tax' => [
                            'name' => 'tax',
                            'type' => 'tax',
                            'target' => 'total',
                            'value' => '+5',
                            'attributes' => [],
                            'order' => 1,
                        ],
                    ],
                ],
            ],
        ]);

        $subtotalItems = $cart->getItemsByConditionTarget('subtotal');
        $totalItems = $cart->getItemsByConditionTarget('total');

        $this->assertEquals(1, $subtotalItems->count());
        $this->assertTrue($subtotalItems->hasItem('item-1'));
        
        $this->assertEquals(1, $totalItems->count());
        $this->assertTrue($totalItems->hasItem('item-2'));
    }

    /** @test */
    public function it_can_filter_items_by_condition_value_using_built_in_methods(): void
    {
        $cart = Cart::factory()->create([
            'items' => [
                [
                    'id' => 'item-1',
                    'name' => 'Test Item 1',
                    'price' => 100.0,
                    'quantity' => 2,
                    'attributes' => [],
                    'conditions' => [
                        'discount' => [
                            'name' => 'discount',
                            'type' => 'discount',
                            'target' => 'subtotal',
                            'value' => '-10%',
                            'attributes' => [],
                            'order' => 0,
                        ],
                    ],
                ],
                [
                    'id' => 'item-2',
                    'name' => 'Test Item 2',
                    'price' => 50.0,
                    'quantity' => 1,
                    'attributes' => [],
                    'conditions' => [
                        'tax' => [
                            'name' => 'tax',
                            'type' => 'tax',
                            'target' => 'total',
                            'value' => '+5',
                            'attributes' => [],
                            'order' => 1,
                        ],
                    ],
                ],
            ],
        ]);

        $percentageItems = $cart->getItemsByConditionValue('-10%');
        $fixedItems = $cart->getItemsByConditionValue('+5');

        $this->assertEquals(1, $percentageItems->count());
        $this->assertTrue($percentageItems->hasItem('item-1'));
        
        $this->assertEquals(1, $fixedItems->count());
        $this->assertTrue($fixedItems->hasItem('item-2'));
    }
}