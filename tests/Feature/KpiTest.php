<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Opportunity;
use App\Models\SalesTarget;
use App\Models\User;
use App\Services\KpiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpiTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function makeSalesUser(): User
    {
        return User::factory()->create(['role' => 'sales']);
    }

    protected function makeClient(User $sales): Client
    {
        return Client::factory()->create(['assigned_sales_id' => $sales->id]);
    }

    /**
     * Create an ActivityLog record for the given sales user and type.
     * The Observer on ActivityLog will auto-call KpiService::incrementActivityCount.
     */
    protected function logActivity(User $sales, string $type, ?Client $client = null): ActivityLog
    {
        return ActivityLog::create([
            'sales_id'      => $sales->id,
            'client_id'     => $client?->id,
            'type'          => $type,
            'subject'       => ucfirst($type) . ' activity',
            'activity_date' => now(),
        ]);
    }

    protected function ensureTarget(User $sales): SalesTarget
    {
        return SalesTarget::getOrCreate(
            $sales->id,
            (int) now()->format('Y'),
            (int) now()->format('n')
        );
    }

    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    /**
     * Creating an ActivityLog with type=meeting should increment
     * SalesTarget.actual_meetings for that sales user in the current period.
     */
    public function test_meeting_activity_increments_kpi(): void
    {
        $sales  = $this->makeSalesUser();
        $target = $this->ensureTarget($sales);
        $before = $target->actual_meetings;

        $this->logActivity($sales, 'meeting');

        $this->assertEquals($before + 1, $target->fresh()->actual_meetings);
    }

    /**
     * Creating an ActivityLog with type=call should increment
     * SalesTarget.actual_calls.
     */
    public function test_call_activity_increments_kpi(): void
    {
        $sales  = $this->makeSalesUser();
        $target = $this->ensureTarget($sales);
        $before = $target->actual_calls;

        $this->logActivity($sales, 'call');

        $this->assertEquals($before + 1, $target->fresh()->actual_calls);
    }

    /**
     * When an opportunity is advanced to 'won', KpiService::recordWon should
     * increment actual_won by 1 and add the deal value to actual_revenue.
     */
    public function test_won_opportunity_updates_revenue_kpi(): void
    {
        $sales  = $this->makeSalesUser();
        $client = $this->makeClient($sales);
        $target = $this->ensureTarget($sales);

        $wonBefore     = $target->actual_won;
        $revenueBefore = (float) $target->actual_revenue;

        $opp = Opportunity::factory()->create([
            'sales_id'    => $sales->id,
            'client_id'   => $client->id,
            'stage'       => 'negotiation',
            'final_value' => 15_000_000,
        ]);

        KpiService::recordWon($opp);

        $refreshed = $target->fresh();
        $this->assertEquals($wonBefore + 1, $refreshed->actual_won);
        $this->assertEquals($revenueBefore + 15_000_000, (float) $refreshed->actual_revenue);
    }

    /**
     * If no SalesTarget exists for the current period when an ActivityLog is
     * created, a new SalesTarget record should be auto-created by KpiService.
     */
    public function test_kpi_creates_target_if_not_exists(): void
    {
        $sales = $this->makeSalesUser();

        // Verify no target exists yet
        $this->assertDatabaseMissing('sales_targets', ['user_id' => $sales->id]);

        $this->logActivity($sales, 'visit');

        // Target should have been auto-created
        $this->assertDatabaseHas('sales_targets', [
            'user_id'      => $sales->id,
            'period_year'  => (int) now()->format('Y'),
            'period_month' => (int) now()->format('n'),
        ]);

        // And the visit count should be 1
        $target = SalesTarget::where('user_id', $sales->id)->first();
        $this->assertEquals(1, $target->actual_visits);
    }
}
