<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sales_id'         => User::factory()->sales(),
            'client_id'        => null,
            'opportunity_id'   => null,
            'type'             => fake()->randomElement(['meeting', 'call', 'visit', 'follow_up', 'email', 'demo']),
            'subject'          => fake()->sentence(),
            'notes'            => null,
            'activity_date'    => now(),
            'duration_minutes' => fake()->optional()->numberBetween(15, 120),
            'outcome'          => null,
            'next_action'      => null,
            'next_action_date' => null,
        ];
    }

    public function meeting(): static
    {
        return $this->state(['type' => 'meeting']);
    }

    public function call(): static
    {
        return $this->state(['type' => 'call']);
    }

    public function visit(): static
    {
        return $this->state(['type' => 'visit']);
    }
}
