<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MasyukAI\FilamentCart\Models\Cart;
use MasyukAI\FilamentCart\Models\CartItem;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\MasyukAI\FilamentCart\Models\CartItem>
 */
class CartItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = CartItem::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $price = fake()->randomFloat(2, 10, 999);

        return [
            'cart_id' => Cart::factory(),
            'item_id' => fake()->unique()->uuid(),
            'name' => fake()->words(3, true),
            'price' => $price,
            'quantity' => $quantity,
            'subtotal' => $price * $quantity,
            'attributes' => [
                'color' => fake()->colorName(),
                'size' => fake()->randomElement(['S', 'M', 'L', 'XL']),
                'material' => fake()->randomElement(['Cotton', 'Polyester', 'Silk', 'Wool']),
            ],
            'conditions' => [],
            'associated_model' => fake()->randomElement([null, 'App\Models\Product']),
        ];
    }

    /**
     * State for items with conditions.
     */
    public function withConditions(): static
    {
        return $this->state(fn (array $attributes) => [
            'conditions' => [
                [
                    'name' => 'bulk_discount',
                    'type' => 'discount',
                    'target' => 'price',
                    'value' => '-10%',
                    'order' => 1,
                    'attributes' => [],
                ],
            ],
        ]);
    }

    /**
     * State for wishlist items.
     */
    public function wishlist(): static
    {
        return $this->state(fn (array $attributes) => [
            'instance' => 'wishlist',
        ]);
    }

    /**
     * State for comparison items.
     */
    public function comparison(): static
    {
        return $this->state(fn (array $attributes) => [
            'instance' => 'comparison',
        ]);
    }

    /**
     * State for high-value items.
     */
    public function highValue(): static
    {
        return $this->state(function (array $attributes) {
            $price = fake()->randomFloat(2, 500, 2000);
            $quantity = $attributes['quantity'] ?? 1;

            return [
                'price' => $price,
                'subtotal' => $price * $quantity,
            ];
        });
    }

    /**
     * State for bulk quantity items.
     */
    public function bulk(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = fake()->numberBetween(10, 50);
            $price = $attributes['price'] ?? fake()->randomFloat(2, 10, 100);

            return [
                'quantity' => $quantity,
                'subtotal' => $price * $quantity,
                'instance' => 'bulk',
            ];
        });
    }
}
