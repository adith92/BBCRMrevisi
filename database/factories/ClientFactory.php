<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_name'      => fake()->company(),
            'pic_name'          => fake()->name(),
            'phone'             => fake()->phoneNumber(),
            'email'             => fake()->unique()->companyEmail(),
            'address'           => fake()->address(),
            'industry'          => fake()->randomElement(['logistics', 'manufacturing', 'retail', 'mining', 'construction']),
            'status'            => 'active',
            'assigned_sales_id' => null,
            'notes'             => null,
            'tier'              => 'bronze',
            'first_contact_date'=> null,
            'company_size'      => null,
        ];
    }
}
