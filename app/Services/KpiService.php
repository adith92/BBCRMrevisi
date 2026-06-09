<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Opportunity;
use App\Models\SalesTarget;
use Illuminate\Support\Facades\DB;

class KpiService
{
    public static function incrementActivityCount(ActivityLog $activityLog): void
    {
        // No-op: Calculated dynamically.
    }

    public static function incrementOpportunityCount(int $salesId): void
    {
        // No-op: Calculated dynamically.
    }

    public static function recordWon(Opportunity $opp): void
    {
        // No-op: Calculated dynamically.
    }
}
