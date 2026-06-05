<?php

namespace Database\Factories;

use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApprovalRequestFactory extends Factory
{
    public function definition(): array
    {
        $originalPrice = fake()->randomFloat(2, 1_000_000, 100_000_000);
        $discountPct   = fake()->randomFloat(2, 1, 20);

        return [
            'opportunity_id'      => Opportunity::factory(),
            'requested_by'        => User::factory()->sales(),
            'current_approver_id' => User::factory()->manager(),
            'type'                => 'discount',
            'discount_percent'    => $discountPct,
            'original_price'      => $originalPrice,
            'final_price'         => $originalPrice * (1 - $discountPct / 100),
            'level'               => 1,
            'status'              => 'pending',
            'notes'               => null,
            'rejection_reason'    => null,
            'approved_at'         => null,
            'rejected_at'         => null,
        ];
    }

    public function approved(): static
    {
        return $this->state([
            'status'      => 'approved',
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status'           => 'rejected',
            'rejection_reason' => 'Tidak sesuai kebijakan.',
            'rejected_at'      => now(),
        ]);
    }
}
