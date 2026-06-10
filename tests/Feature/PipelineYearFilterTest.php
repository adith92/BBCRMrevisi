<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Opportunity;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class PipelineYearFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    /** @test */
    public function pipeline_kanban_filters_data_by_year_correctly(): void
    {
        $sales = $this->user('sales');
        $client = Client::factory()->create();

        // 1. Deal tahun 2026 (expected close date)
        $opp2026 = Opportunity::factory()->create([
            'sales_id' => $sales->id,
            'client_id' => $client->id,
            'stage' => 'prospecting',
            'expected_close_date' => Carbon::parse('2026-05-10'),
            'actual_close_date' => null,
        ]);

        // 2. Deal tahun 2025 (actual close date / expected close date)
        $opp2025 = Opportunity::factory()->create([
            'sales_id' => $sales->id,
            'client_id' => $client->id,
            'stage' => 'won',
            'expected_close_date' => Carbon::parse('2025-04-10'),
            'actual_close_date' => Carbon::parse('2025-05-12'),
        ]);

        // Akses route pipeline tanpa filter (default ke tahun ini, yaitu 2026 di context system sekarang, tapi mari kita pastikan dengan filter spesifik)
        $response = $this->actingAs($sales)
            ->get('/pipeline?year=2026');
        
        $response->assertStatus(200);
        $opportunities = $response->viewData('opportunities');
        
        // Pastikan $opp2026 masuk, tapi $opp2025 tidak
        $this->assertTrue($opportunities->contains('id', $opp2026->id));
        $this->assertFalse($opportunities->contains('id', $opp2025->id));

        // Akses dengan filter year=2025
        $response = $this->actingAs($sales)
            ->get('/pipeline?year=2025');
        
        $response->assertStatus(200);
        $opportunities = $response->viewData('opportunities');
        
        // Pastikan $opp2025 masuk, tapi $opp2026 tidak
        $this->assertTrue($opportunities->contains('id', $opp2025->id));
        $this->assertFalse($opportunities->contains('id', $opp2026->id));
    }
}
