<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedDemoDataTest extends TestCase
{
    use RefreshDatabase;

    protected function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    /** @test */
    public function guest_cannot_access_seed_route(): void
    {
        $this->postJson('/system/seed-demo', ['amount' => 100])
            ->assertStatus(401);
    }

    /** @test */
    public function non_gm_users_cannot_access_seed_route(): void
    {
        $roles = ['manager', 'sales', 'operational', 'finance'];

        foreach ($roles as $role) {
            $this->actingAs($this->user($role))
                ->postJson('/system/seed-demo', ['amount' => 100])
                ->assertStatus(403);
        }
    }

    /** @test */
    public function gm_user_can_access_seed_route(): void
    {
        $this->actingAs($this->user('gm'))
            ->postJson('/system/seed-demo', ['amount' => 100])
            ->assertStatus(200);
    }

    /** @test */
    public function seeder_validates_amount_input(): void
    {
        $gm = $this->user('gm');

        // Test amount missing
        $this->actingAs($gm)
            ->postJson('/system/seed-demo', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);

        // Test amount not an integer
        $this->actingAs($gm)
            ->postJson('/system/seed-demo', ['amount' => 'not-an-integer'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);

        // Test amount too small
        $this->actingAs($gm)
            ->postJson('/system/seed-demo', ['amount' => 0])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);

        // Test amount too large
        $this->actingAs($gm)
            ->postJson('/system/seed-demo', ['amount' => 100001])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    /** @test */
    public function seeder_is_locked_during_concurrent_requests(): void
    {
        $gm = $this->user('gm');

        // Manually acquire the lock
        $lock = \Illuminate\Support\Facades\Cache::lock('seed_demo_data', 60);
        $this->assertTrue($lock->get());

        try {
            // Attempt to seed while locked
            $this->actingAs($gm)
                ->postJson('/system/seed-demo', ['amount' => 100])
                ->assertStatus(423)
                ->assertJson(['status' => 'error', 'message' => 'Seeding is currently in progress. Please wait.']);
        } finally {
            $lock->release();
        }
    }

    /** @test */
    public function gm_can_seed_data_successfully(): void
    {
        $gm = $this->user('gm');

        // Create a sales user and products so seeder has required prerequisites
        User::factory()->create(['role' => 'sales']);
        
        $initialOppCount = \App\Models\Opportunity::count();
        $initialClientCount = \App\Models\Client::count();

        $this->actingAs($gm)
            ->postJson('/system/seed-demo', ['amount' => 15])
            ->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertEquals($initialOppCount + 15, \App\Models\Opportunity::count());
        $this->assertEquals($initialClientCount + 3, \App\Models\Client::count()); // 15 / 5 = 3 clients
    }
}
