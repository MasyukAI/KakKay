<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Tests\Feature;

use MasyukAI\Cart\Facades\Cart as CartFacade;
use MasyukAI\FilamentCart\Models\CartItem;
use MasyukAI\FilamentCart\Resources\CartConditionResource;
use MasyukAI\FilamentCart\Resources\CartItemResource;
use MasyukAI\FilamentCart\Resources\ConditionResource;
use MasyukAI\FilamentCart\Tests\TestCase;

class FilamentResourcesTest extends TestCase
{
    protected $cartModel;

    protected $cart;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a cart for testing
        $this->cart = \MasyukAI\FilamentCart\Models\Cart::factory()->create();
    }

    public function test_cart_item_resource_can_list_items(): void
    {
        // Add items to cart
        CartFacade::add('test-1', 'Test Product 1', 5000, 2);
        CartFacade::add('test-2', 'Test Product 2', 7500, 1);

        // Test that we can see the items in the resource
        $this->assertDatabaseCount('cart_items', 2);

        $cartItems = CartItem::all();
        $this->assertCount(2, $cartItems);

        $firstItem = $cartItems->where('item_id', 'test-1')->first();
        $this->assertEquals('Test Product 1', $firstItem->name);
        $this->assertEquals(5000, $firstItem->price); // Price stored in cents
        $this->assertEquals(2, $firstItem->quantity);

        $secondItem = $cartItems->where('item_id', 'test-2')->first();
        $this->assertEquals('Test Product 2', $secondItem->name);
        $this->assertEquals(7500, $secondItem->price); // Price stored in cents
        $this->assertEquals(1, $secondItem->quantity);
    }

    public function test_condition_resources_exist(): void
    {
        // Create a cart first
        CartFacade::add('test-item', 'Test Item', 10000, 1);

        // Verify we have the base data
        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseCount('cart_conditions', 0);
        $this->assertDatabaseCount('conditions', 0);

        // The resources should be available even with no data
        $this->assertTrue(class_exists(ConditionResource::class));
        $this->assertTrue(class_exists(CartConditionResource::class));
        $this->assertTrue(class_exists(CartItemResource::class));
    }

    public function test_resources_have_proper_configuration(): void
    {
        // Test resource configuration
        $this->assertEquals('Cart Items', CartItemResource::getNavigationLabel());
        $this->assertEquals('Cart Conditions', CartConditionResource::getNavigationLabel());
        $this->assertEquals('Conditions', ConditionResource::getNavigationLabel());

        $this->assertEquals('E-commerce', CartItemResource::getNavigationGroup());
        $this->assertEquals('E-commerce', CartConditionResource::getNavigationGroup());
        $this->assertEquals('E-commerce', ConditionResource::getNavigationGroup());
    }
}
