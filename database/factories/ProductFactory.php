<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_category_id' => ProductCategory::factory(),
            'name'                => fake()->words(3, true),
            'sku'                 => strtoupper(fake()->unique()->bothify('SKU-####-??')),
            'base_price'          => fake()->randomFloat(2, 100_000, 50_000_000),
            'unit'                => fake()->randomElement(['pax', 'unit', 'trip']),
            'min_pax'             => 1,
            'max_pax'             => null,
            'duration_days'       => null,
            'description'         => fake()->sentence(),
            'is_active'           => true,
        ];
    }

    /** Scope helper used in the controller: Product::active() */
    public function active(): static
    {
        return $this->state(['is_active' => true]);
    }
}
