<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
final class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isDigital = fake()->boolean(20); // 20% chance of being digital

        return [
            'name' => fake()->words(3, true),
            'category_id' => Category::factory(),
            'price' => fake()->numberBetween(1000, 10000), // RM 10.00 to RM 100.00 in cents
            'is_active' => true,

            // Physical properties
            'weight' => $isDigital ? 0 : fake()->numberBetween(100, 5000), // 100g to 5kg
            'length' => $isDigital ? null : fake()->numberBetween(100, 500), // 100mm to 500mm
            'width' => $isDigital ? null : fake()->numberBetween(100, 300), // 100mm to 300mm
            'height' => $isDigital ? null : fake()->numberBetween(20, 200), // 20mm to 200mm
            'is_digital' => $isDigital,
            'free_shipping' => fake()->boolean(15), // 15% chance of free shipping
        ];
    }

    /**
     * Indicate that the product is inactive.
     */
    /** @phpstan-ignore-next-line */
    public function inactive(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the product is digital (no shipping required).
     */
    /** @phpstan-ignore-next-line */
    public function digital(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'weight' => 0,
            'length' => null,
            'width' => null,
            'height' => null,
            'is_digital' => true,
            'free_shipping' => false,
        ]);
    }

    /**
     * Indicate that the product has free shipping.
     */
    /** @phpstan-ignore-next-line */
    public function freeShipping(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'free_shipping' => true,
        ]);
    }

    /**
     * Indicate that the product is heavy (for testing shipping calculations).
     */
    /** @phpstan-ignore-next-line */
    public function heavy(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'weight' => fake()->numberBetween(10000, 50000), // 10kg to 50kg
            'length' => fake()->numberBetween(500, 1000), // 500mm to 1000mm
            'width' => fake()->numberBetween(400, 800), // 400mm to 800mm
            'height' => fake()->numberBetween(300, 600), // 300mm to 600mm
            'is_digital' => false,
            'free_shipping' => false,
        ]);
    }
}
