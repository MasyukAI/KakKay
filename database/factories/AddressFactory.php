<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
final class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'addressable_type' => User::class,
            'addressable_id' => User::factory(),
            'name' => $this->faker->name(),
            'company' => $this->faker->optional()->company(),
            'street1' => $this->faker->streetAddress(),
            'street2' => $this->faker->optional()->secondaryAddress() ?? '',
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'postcode' => $this->faker->postcode(),
            'country' => $this->faker->country(),
            'phone' => $this->faker->phoneNumber(),
            'type' => $this->faker->randomElement(['home', 'work', 'billing', 'shipping']),
            'is_primary' => $this->faker->boolean(30), // 30% chance of being primary
        ];
    }
}
