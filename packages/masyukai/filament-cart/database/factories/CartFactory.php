<?php

namespace MasyukAI\FilamentCart\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MasyukAI\FilamentCart\Models\Cart;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\MasyukAI\FilamentCart\Models\Cart>
 */
class CartFactory extends Factory
{
    protected $model = Cart::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'identifier' => 'cart_'.$this->faker->uuid(),
            'instance' => $this->faker->randomElement(['default', 'wishlist', 'comparison', 'quote']),
            'items' => $this->generateRandomItems(),
            'conditions' => $this->generateRandomConditions(),
            'metadata' => $this->generateRandomMetadata(),
        ];
    }

    /**
     * Generate a cart with no items (empty cart).
     */
    public function empty(): static
    {
        return $this->state(fn (array $attributes) => [
            'items' => [],
            'conditions' => [],
            'metadata' => [],
        ]);
    }

    /**
     * Generate a cart for a specific instance.
     */
    public function instance(string $instance): static
    {
        return $this->state(fn (array $attributes) => [
            'instance' => $instance,
        ]);
    }

    /**
     * Generate a cart with a specific identifier.
     */
    public function withIdentifier(string $identifier): static
    {
        return $this->state(fn (array $attributes) => [
            'identifier' => $identifier,
        ]);
    }

    /**
     * Generate a cart with many items.
     */
    public function withManyItems(int $count = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'items' => $this->generateRandomItems($count),
        ]);
    }

    /**
     * Generate a cart with expensive items.
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'items' => $this->generateRandomItems(3, 500, 2000),
        ]);
    }

    /**
     * Generate random cart items.
     */
    private function generateRandomItems(?int $count = null, float $minPrice = 10, float $maxPrice = 500): array
    {
        $count = $count ?? $this->faker->numberBetween(1, 5);
        $items = [];

        for ($i = 0; $i < $count; $i++) {
            $items[] = [
                'id' => 'product_'.$this->faker->numberBetween(1, 1000),
                'name' => $this->faker->words(3, true),
                'price' => $this->faker->randomFloat(2, $minPrice, $maxPrice),
                'quantity' => $this->faker->numberBetween(1, 5),
                'attributes' => [
                    'color' => $this->faker->colorName(),
                    'size' => $this->faker->randomElement(['S', 'M', 'L', 'XL']),
                    'brand' => $this->faker->company(),
                ],
            ];
        }

        return $items;
    }

    /**
     * Generate random cart conditions.
     */
    private function generateRandomConditions(): array
    {
        $conditions = [];

        // Sometimes add a discount
        if ($this->faker->boolean(30)) {
            $conditions[] = [
                'name' => 'Holiday Discount',
                'type' => 'discount',
                'value' => $this->faker->randomFloat(2, 5, 50),
                'description' => 'Special holiday promotion',
            ];
        }

        // Sometimes add tax
        if ($this->faker->boolean(70)) {
            $conditions[] = [
                'name' => 'Sales Tax',
                'type' => 'tax',
                'value' => $this->faker->randomFloat(2, 5, 12),
                'description' => 'Local sales tax',
            ];
        }

        // Sometimes add shipping
        if ($this->faker->boolean(50)) {
            $conditions[] = [
                'name' => 'Shipping Fee',
                'type' => 'shipping',
                'value' => $this->faker->randomFloat(2, 5, 25),
                'description' => 'Standard shipping',
            ];
        }

        return $conditions;
    }

    /**
     * Generate random cart metadata.
     */
    private function generateRandomMetadata(): array
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 100),
            'session_id' => $this->faker->uuid(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP']),
            'created_from' => $this->faker->randomElement(['web', 'mobile', 'api']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
