<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $name = fake()->words(3, true);
        $requiresShipping = fake()->boolean(80); // 80% chance of physical product

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->randomNumber(5),
            'description' => fake()->paragraph(),
            'short_description' => fake()->sentence(),
            'sku' => mb_strtoupper(fake()->unique()->bothify('???-#####')),
            'type' => 'simple',
            'status' => 'active',
            'visibility' => 'visible',
            'price' => fake()->numberBetween(1000, 10000),
            'compare_price' => fake()->optional(0.3)->numberBetween(10000, 15000),
            'cost' => fake()->numberBetween(500, 8000),
            'currency' => 'MYR',
            'weight' => $requiresShipping ? fake()->numberBetween(100, 5000) : null,
            'length' => $requiresShipping ? fake()->numberBetween(100, 500) : null,
            'width' => $requiresShipping ? fake()->numberBetween(100, 300) : null,
            'height' => $requiresShipping ? fake()->numberBetween(20, 200) : null,
            'weight_unit' => 'g',
            'dimension_unit' => 'mm',
            'is_featured' => fake()->boolean(20),
            'is_taxable' => fake()->boolean(80),
            'requires_shipping' => $requiresShipping,
            'published_at' => now(),
        ];
    }

    /**
     * Indicate that the product is inactive.
     */
    /** @phpstan-ignore-next-line */
    public function inactive(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the product is digital (no shipping required).
     */
    /** @phpstan-ignore-next-line */
    public function digital(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'weight' => null,
            'length' => null,
            'width' => null,
            'height' => null,
            'requires_shipping' => false,
        ]);
    }

    /**
     * Indicate that the product has free shipping.
     */
    /** @phpstan-ignore-next-line */
    public function freeShipping(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'requires_shipping' => true,
            'metadata' => ['free_shipping' => true],
        ]);
    }

    /**
     * Indicate that the product is heavy (for testing shipping calculations).
     */
    /** @phpstan-ignore-next-line */
    public function heavy(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'weight' => fake()->numberBetween(10000, 50000),
            'length' => fake()->numberBetween(500, 1000),
            'width' => fake()->numberBetween(400, 800),
            'height' => fake()->numberBetween(300, 600),
            'requires_shipping' => true,
        ]);
    }
}
