<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 5),
            'unit_price' => fake()->numberBetween(1000, 10000), // RM 10.00 to RM 100.00 in cents
        ];
    }

    /**
     * Set a specific product for the order item
     */
    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'unit_price' => $product->price,
        ]);
    }

    /**
     * Set a specific order for the order item
     */
    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
        ]);
    }

    /**
     * Create a high-quantity order item
     */
    public function highQuantity(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => fake()->numberBetween(10, 50),
        ]);
    }

    /**
     * Create an expensive order item
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'unit_price' => fake()->numberBetween(50000, 200000), // RM 500 to RM 2000
        ]);
    }
}
