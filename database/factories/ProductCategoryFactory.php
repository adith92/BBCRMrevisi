<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'        => fake()->words(2, true),
            'type'        => fake()->randomElement(['short_term', 'long_term', 'evoucher']),
            'description' => fake()->sentence(),
            'is_active'   => true,
        ];
    }

    public function shortTerm(): static
    {
        return $this->state(['type' => 'short_term']);
    }

    public function longTerm(): static
    {
        return $this->state(['type' => 'long_term']);
    }
}
