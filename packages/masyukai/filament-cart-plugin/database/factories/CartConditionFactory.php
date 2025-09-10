<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCartPlugin\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MasyukAI\FilamentCartPlugin\Models\CartCondition;

/**
 * @extends Factory<CartCondition>
 */
class CartConditionFactory extends Factory
{
    protected $model = CartCondition::class;

    public function definition(): array
    {
        $types = ['static', 'dynamic'];
        $targets = ['item', 'subtotal', 'total'];
        $operators = ['+', '-', '*', '/'];
        $percentageValues = ['-10%', '-15%', '-20%', '+5%', '+10%'];
        $fixedValues = ['+10', '+25', '+50', '-5', '-15', '-30'];
        
        $usePercentage = $this->faker->boolean(40);
        $value = $usePercentage 
            ? $this->faker->randomElement($percentageValues)
            : $this->faker->randomElement($fixedValues);

        return [
            'name' => $this->faker->unique()->words(2, true) . '_' . $this->faker->randomNumber(3),
            'type' => $this->faker->randomElement($types),
            'target' => $this->faker->randomElement($targets),
            'value' => $value,
            'attributes' => [],
            'order' => $this->faker->numberBetween(0, 100),
            'is_global' => $this->faker->boolean(20),
            'is_active' => $this->faker->boolean(80),
            'description' => $this->faker->optional()->sentence(),
        ];
    }

    public function static(): static
    {
        return $this->state(['type' => 'static']);
    }

    public function dynamic(): static
    {
        return $this->state(['type' => 'dynamic']);
    }

    public function global(): static
    {
        return $this->state(['is_global' => true]);
    }

    public function active(): static
    {
        return $this->state(['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function discount(): static
    {
        return $this->state([
            'value' => $this->faker->randomElement(['-10%', '-15%', '-20%', '-5', '-10', '-25'])
        ]);
    }

    public function fee(): static
    {
        return $this->state([
            'value' => $this->faker->randomElement(['+5%', '+10%', '+15%', '+5', '+10', '+25'])
        ]);
    }
}