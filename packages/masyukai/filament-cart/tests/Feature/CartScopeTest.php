<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use MasyukAI\FilamentCart\Models\Cart;
use MasyukAI\FilamentCart\Tests\TestCase;

class CartScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_not_empty_scope_works_with_current_database(): void
    {
        // Create an empty cart (no items)
        $emptyCart = Cart::factory()->create([
            'items' => [],
        ]);

        // Create a cart with items
        $fullCart = Cart::factory()->create([
            'items' => [
                ['id' => 1, 'name' => 'Test Item', 'price' => 10.00, 'quantity' => 1],
            ],
        ]);

        // Test the notEmpty scope
        $nonEmptyCarts = Cart::notEmpty()->get();

        $this->assertCount(1, $nonEmptyCarts);
        $this->assertTrue($nonEmptyCarts->contains($fullCart));
        $this->assertFalse($nonEmptyCarts->contains($emptyCart));
    }

    public function test_not_empty_scope_excludes_null_items(): void
    {
        // Create a cart with null items
        $nullCart = Cart::factory()->create([
            'items' => null,
        ]);

        // Create a cart with empty array items
        $emptyCart = Cart::factory()->create([
            'items' => [],
        ]);

        // Create a cart with items
        $fullCart = Cart::factory()->create([
            'items' => [
                ['id' => 1, 'name' => 'Test Item', 'price' => 10.00, 'quantity' => 1],
            ],
        ]);

        // Test the notEmpty scope excludes both null and empty carts
        $nonEmptyCarts = Cart::notEmpty()->get();

        $this->assertCount(1, $nonEmptyCarts);
        $this->assertTrue($nonEmptyCarts->contains($fullCart));
        $this->assertFalse($nonEmptyCarts->contains($nullCart));
        $this->assertFalse($nonEmptyCarts->contains($emptyCart));
    }

    public function test_database_driver_detection(): void
    {
        $cart = new Cart;
        $query = $cart->newQuery();

        // Verify that the scope can be called without errors
        $query->notEmpty();

        // The fact that this doesn't throw an exception means the database-specific logic works
        $this->assertTrue(true);
    }
}
