<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MasyukAI\FilamentCart\Models\Condition;

/**
 * @extends Factory<Condition>
 */
final class ConditionFactory extends Factory
{
    protected $model = Condition::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->lexify('condition_????'),
            'display_name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['discount', 'tax', 'fee', 'shipping', 'surcharge']),
            'target' => $this->faker->randomElement(['subtotal', 'total', 'item']),
            'value' => $this->generateValue(),
            'order' => $this->faker->numberBetween(0, 10),
            'attributes' => [],
            'is_active' => $this->faker->boolean(80),
            'is_global' => false,
        ];
    }

    public function discount(): static
    {
        return $this->state(fn () => [
            'type' => 'discount',
            'value' => '-'.$this->faker->numberBetween(5, 50).'%',
            'target' => $this->faker->randomElement(['subtotal', 'item']),
        ]);
    }

    public function tax(): static
    {
        return $this->state(fn () => [
            'type' => 'tax',
            'value' => $this->faker->numberBetween(5, 15).'%',
            'target' => 'subtotal',
        ]);
    }

    public function fee(): static
    {
        return $this->state(fn () => [
            'type' => 'fee',
            'value' => '+'.$this->faker->numberBetween(200, 5000),
            'target' => 'subtotal',
        ]);
    }

    public function shipping(): static
    {
        return $this->state(fn () => [
            'type' => 'shipping',
            'value' => '+'.$this->faker->numberBetween(500, 8000),
            'target' => 'subtotal',
            'attributes' => [
                'method' => $this->faker->randomElement(['standard', 'express', 'overnight']),
                'carrier' => $this->faker->randomElement(['UPS', 'FedEx', 'DHL', 'USPS']),
            ],
        ]);
    }

    public function active(): static
    {
        return $this->state(fn () => ['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function forItems(): static
    {
        return $this->state(fn () => ['target' => 'item']);
    }

    public function withAttributes(array $attributes): static
    {
        return $this->state(fn () => ['attributes' => $attributes]);
    }

    private function generateValue(): string
    {
        return match ($this->faker->randomElement(['percentage', 'fixed_positive', 'fixed_negative'])) {
            'percentage' => $this->faker->numberBetween(1, 50).'%',
            'fixed_positive' => '+'.$this->faker->numberBetween(100, 10000),
            'fixed_negative' => '-'.$this->faker->numberBetween(100, 10000),
        };
    }
}
