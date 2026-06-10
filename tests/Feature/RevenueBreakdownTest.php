<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Opportunity;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class RevenueBreakdownTest extends TestCase
{
    use RefreshDatabase;

    protected function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    /** @test */
    public function guest_cannot_access_revenue_breakdown(): void
    {
        $this->getJson('/api/revenue/breakdown')
            ->assertStatus(401);
    }

    /** @test */
    public function sales_officer_can_access_own_revenue_breakdown(): void
    {
        $sales = $this->user('sales');
        
        $this->actingAs($sales)
            ->getJson('/api/revenue/breakdown?period=today')
            ->assertStatus(200);
    }

    /** @test */
    public function revenue_breakdown_filters_correctly_by_period(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 12:00:00')); // Monday

        $sales = $this->user('sales');
        $client = Client::factory()->create();

        // 1. Deal hari ini (Today) - June 15
        $todayOpp = Opportunity::factory()->create([
            'sales_id' => $sales->id,
            'client_id' => $client->id,
            'stage' => 'won',
            'actual_close_date' => Carbon::parse('2026-06-15'),
            'final_value' => 5000000,
        ]);

        // 2. Deal minggu ini tapi bukan hari ini (Week) - June 16 (Tuesday)
        $weekOpp = Opportunity::factory()->create([
            'sales_id' => $sales->id,
            'client_id' => $client->id,
            'stage' => 'won',
            'actual_close_date' => Carbon::parse('2026-06-16'),
            'final_value' => 10000000,
        ]);

        // 3. Deal bulan ini tapi bukan minggu ini (Month) - June 1 (First of June)
        $monthOpp = Opportunity::factory()->create([
            'sales_id' => $sales->id,
            'client_id' => $client->id,
            'stage' => 'won',
            'actual_close_date' => Carbon::parse('2026-06-01'),
            'final_value' => 15000000,
        ]);

        // 4. Deal tahun ini tapi bukan bulan ini (Year) - Jan 15
        $yearOpp = Opportunity::factory()->create([
            'sales_id' => $sales->id,
            'client_id' => $client->id,
            'stage' => 'won',
            'actual_close_date' => Carbon::parse('2026-01-15'),
            'final_value' => 20000000,
        ]);

        // 5. Deal tahun lalu
        $lastYearOpp = Opportunity::factory()->create([
            'sales_id' => $sales->id,
            'client_id' => $client->id,
            'stage' => 'won',
            'actual_close_date' => Carbon::parse('2025-06-15'),
            'final_value' => 30000000,
        ]);

        // Uji filter 'today'
        $response = $this->actingAs($sales)->getJson('/api/revenue/breakdown?period=today');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['id' => $todayOpp->id]);

        // Uji filter 'week' (Jun 15 - Jun 21)
        $response = $this->actingAs($sales)->getJson('/api/revenue/breakdown?period=week');
        $response->assertStatus(200);
        // Harusnya ada todayOpp + weekOpp
        $response->assertJsonCount(2);

        // Uji filter 'month' (Jun 1 - Jun 30)
        $response = $this->actingAs($sales)->getJson('/api/revenue/breakdown?period=month');
        $response->assertStatus(200);
        // Harusnya ada todayOpp + weekOpp + monthOpp
        $response->assertJsonCount(3);

        // Uji filter 'year' (Jan 1 - Dec 31, 2026)
        $response = $this->actingAs($sales)->getJson('/api/revenue/breakdown?period=year');
        $response->assertStatus(200);
        // Harusnya ada todayOpp + weekOpp + monthOpp + yearOpp (total 4)
        $response->assertJsonCount(4);

        Carbon::setTestNow(); // Reset time mock
    }
}
