<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesTargetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'              => User::factory()->sales(),
            'period_year'          => (int) now()->format('Y'),
            'period_month'         => (int) now()->format('n'),
            'target_meetings'      => 10,
            'target_calls'         => 20,
            'target_visits'        => 5,
            'target_opportunities' => 8,
            'target_won'           => 3,
            'target_revenue'       => 50_000_000,
            'actual_meetings'      => 0,
            'actual_calls'         => 0,
            'actual_visits'        => 0,
            'actual_opportunities' => 0,
            'actual_won'           => 0,
            'actual_revenue'       => 0,
        ];
    }
}
