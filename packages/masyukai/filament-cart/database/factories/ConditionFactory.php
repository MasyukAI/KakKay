<?php

namespace MasyukAI\FilamentCart\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MasyukAI\FilamentCart\Models\Condition;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\MasyukAI\FilamentCart\Models\Condition>
 */
class ConditionFactory extends Factory
{
    protected $model = Condition::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word().'_'.$this->faker->word().'_'.$this->faker->randomNumber(3),
            'display_name' => $this->faker->word().' '.$this->faker->word(),
            'description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['discount', 'tax', 'fee', 'shipping', 'surcharge']),
            'target' => $this->faker->randomElement(['subtotal', 'total', 'item']),
            'value' => $this->generateValue(),
            'order' => $this->faker->numberBetween(0, 10),
            'attributes' => [],
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
        ];
    }

    /**
     * Generate a random condition value.
     */
    private function generateValue(): string
    {
        return match ($this->faker->randomElement(['percentage', 'fixed_positive', 'fixed_negative'])) {
            'percentage' => $this->faker->randomFloat(1, 1, 50).'%',
            'fixed_positive' => '+'.$this->faker->randomFloat(2, 1, 100),
            'fixed_negative' => '-'.$this->faker->randomFloat(2, 1, 100),
        };
    }

    /**
     * Indicate that the condition is a discount.
     */
    public function discount(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'discount',
            'value' => '-'.$this->faker->randomFloat(2, 5, 50).($this->faker->boolean() ? '%' : ''),
            'target' => $this->faker->randomElement(['subtotal', 'item']),
        ]);
    }

    /**
     * Indicate that the condition is a tax.
     */
    public function tax(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'tax',
            'value' => $this->faker->randomFloat(1, 5, 15).'%',
            'target' => 'subtotal',
        ]);
    }

    /**
     * Indicate that the condition is a fee.
     */
    public function fee(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'fee',
            'value' => '+'.$this->faker->randomFloat(2, 5, 25),
            'target' => 'subtotal',
        ]);
    }

    /**
     * Indicate that the condition is for shipping.
     */
    public function shipping(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'shipping',
            'value' => '+'.$this->faker->randomFloat(2, 10, 50),
            'target' => 'subtotal',
            'attributes' => [
                'method' => $this->faker->randomElement(['standard', 'express', 'overnight']),
                'carrier' => $this->faker->randomElement(['UPS', 'FedEx', 'DHL', 'USPS']),
            ],
        ]);
    }

    /**
     * Indicate that the condition is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the condition is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the condition is for item-level conditions.
     */
    public function forItems(): static
    {
        return $this->state(fn (array $attributes) => [
            'target' => 'item',
        ]);
    }

    /**
     * Indicate that the condition has custom attributes.
     */
    public function withAttributes(array $attributes): static
    {
        return $this->state(fn (array $modelAttributes) => [
            'attributes' => $attributes,
        ]);
    }
}
