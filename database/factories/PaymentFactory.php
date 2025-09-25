<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
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
            'gateway_transaction_id' => fake()->uuid(),
            'gateway_payment_id' => fake()->uuid(),
            'gateway_response' => [],
            'amount' => fake()->numberBetween(1000, 50000), // in cents
            'status' => fake()->randomElement(['pending', 'completed', 'failed', 'cancelled']),
            'method' => fake()->randomElement(['credit_card', 'paypal', 'stripe']),
            'currency' => 'USD',
            'paid_at' => fake()->optional()->dateTime(),
            'failed_at' => null,
            'refunded_at' => null,
            'note' => $this->faker->optional()->sentence(),
            'reference' => $this->faker->unique()->regexify('[A-Z0-9]{10}'),
        ];
    }
}
