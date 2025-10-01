<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MasyukAI\FilamentCart\Models\Cart;
use MasyukAI\FilamentCart\Models\CartCondition;
use MasyukAI\FilamentCart\Models\CartItem;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\MasyukAI\FilamentCart\Models\CartCondition>
 */
class CartConditionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = CartCondition::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['discount', 'tax', 'fee', 'shipping']);

        return [
            'cart_id' => Cart::factory(),
            'cart_item_id' => null, // Cart-level by default
            'name' => fake()->words(2, true).'_'.$type,
            'type' => $type,
            'target' => fake()->randomElement(['subtotal', 'total', 'price']),
            'value' => $this->generateValue($type),
            'order' => fake()->numberBetween(1, 10),
            'attributes' => $this->generateAttributes($type),
            'item_id' => null,
        ];
    }

    /**
     * State for discount conditions.
     */
    public function discount(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'discount',
            'value' => '-'.fake()->numberBetween(5, 50).'%',
            'attributes' => [
                'description' => 'Promotional discount',
                'promo_code' => fake()->regexify('[A-Z]{4}[0-9]{2}'),
                'valid_until' => now()->addDays(30)->toDateString(),
            ],
        ]);
    }

    /**
     * State for tax conditions.
     */
    public function tax(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'tax',
            'value' => fake()->randomFloat(2, 5, 15).'%',
            'attributes' => [
                'tax_type' => fake()->randomElement(['VAT', 'Sales Tax', 'GST']),
                'tax_region' => fake()->state(),
            ],
        ]);
    }

    /**
     * State for fee conditions.
     */
    public function fee(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'fee',
            'value' => fake()->randomFloat(2, 1, 25),
            'attributes' => [
                'fee_type' => fake()->randomElement(['Processing Fee', 'Service Fee', 'Handling Fee']),
                'description' => 'Additional processing fee',
            ],
        ]);
    }

    /**
     * State for shipping conditions.
     */
    public function shipping(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'shipping',
            'value' => fake()->randomFloat(2, 5, 50),
            'attributes' => [
                'shipping_method' => fake()->randomElement(['Standard', 'Express', 'Overnight']),
                'carrier' => fake()->randomElement(['UPS', 'FedEx', 'USPS', 'DHL']),
                'estimated_days' => fake()->numberBetween(1, 14),
            ],
        ]);
    }

    /**
     * State for item-level conditions.
     */
    public function itemLevel(): static
    {
        return $this->state(fn (array $attributes) => [
            'cart_item_id' => CartItem::factory(),
            'item_id' => fake()->uuid(),
        ]);
    }

    /**
     * State for cart-level conditions.
     */
    public function cartLevel(): static
    {
        return $this->state(fn (array $attributes) => [
            'cart_item_id' => null,
            'item_id' => null,
        ]);
    }

    /**
     * State for percentage-based conditions.
     */
    public function percentage(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => fake()->randomElement(['-', '+']).fake()->numberBetween(5, 50).'%',
        ]);
    }

    /**
     * State for fixed amount conditions.
     */
    public function fixedAmount(): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => fake()->randomElement(['-', '+']).fake()->randomFloat(2, 1, 100),
        ]);
    }

    /**
     * Generate value based on condition type.
     */
    private function generateValue(string $type): string
    {
        return match ($type) {
            'discount' => '-'.fake()->numberBetween(5, 50).'%',
            'tax' => fake()->randomFloat(2, 5, 15).'%',
            'fee' => (string) fake()->randomFloat(2, 1, 25),
            'shipping' => (string) fake()->randomFloat(2, 5, 50),
            default => (string) fake()->randomFloat(2, 1, 100),
        };
    }

    /**
     * Generate attributes based on condition type.
     */
    private function generateAttributes(string $type): array
    {
        return match ($type) {
            'discount' => [
                'description' => 'Promotional discount',
                'promo_code' => fake()->regexify('[A-Z]{4}[0-9]{2}'),
            ],
            'tax' => [
                'tax_type' => fake()->randomElement(['VAT', 'Sales Tax', 'GST']),
                'tax_region' => fake()->state(),
            ],
            'fee' => [
                'fee_type' => fake()->randomElement(['Processing Fee', 'Service Fee']),
                'description' => 'Additional fee',
            ],
            'shipping' => [
                'shipping_method' => fake()->randomElement(['Standard', 'Express']),
                'carrier' => fake()->randomElement(['UPS', 'FedEx', 'USPS']),
            ],
            default => [],
        };
    }
}
