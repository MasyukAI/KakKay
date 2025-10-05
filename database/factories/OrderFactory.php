<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
final class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => Order::generateOrderNumber(),
            'user_id' => User::factory(),
            'address_id' => null,
            'cart_items' => [], // Legacy field - will be replaced by OrderItems
            'delivery_method' => fake()->randomElement(['standard', 'express', 'pickup']),
            'checkout_form_data' => [
                'billing_address' => fake()->address,
                'phone' => fake()->phoneNumber,
            ],
            'status' => fake()->randomElement(['pending', 'processing', 'shipped', 'delivered', 'cancelled']),
            'total' => fake()->numberBetween(1000, 50000), // Total in cents
        ];
    }

    /**
     * Create an order with a specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a pending order
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Create a completed order
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
        ]);
    }
}
