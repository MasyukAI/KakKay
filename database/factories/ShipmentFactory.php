<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shipment>
 */
final class ShipmentFactory extends Factory
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
            'carrier' => $this->faker->randomElement(['UPS', 'FedEx', 'DHL', 'USPS']),
            'service' => $this->faker->randomElement(['Standard', 'Express', 'Overnight']),
            'tracking_number' => $this->faker->unique()->regexify('[A-Z0-9]{12}'),
            'status' => $this->faker->randomElement(['pending', 'shipped', 'in_transit', 'delivered']),
            'shipped_at' => $this->faker->optional()->dateTime(),
            'delivered_at' => null,
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
