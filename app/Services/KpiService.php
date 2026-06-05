<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Opportunity;
use App\Models\SalesTarget;
use Illuminate\Support\Facades\DB;

class KpiService
{
    /**
     * Increment the appropriate activity counter on SalesTarget based on activity type.
     * follow_up, email, demo are counted as generic calls.
     */
    public static function incrementActivityCount(ActivityLog $activityLog): void
    {
        DB::transaction(function () use ($activityLog) {
            $target = SalesTarget::getOrCreate(
                $activityLog->sales_id,
                (int) $activityLog->activity_date->format('Y'),
                (int) $activityLog->activity_date->format('n')
            );

            $column = match ($activityLog->type) {
                'meeting'                       => 'actual_meetings',
                'call'                          => 'actual_calls',
                'visit'                         => 'actual_visits',
                'follow_up', 'email', 'demo'    => 'actual_calls',
                default                         => null,
            };

            if ($column !== null) {
                $target->increment($column);
            }
        });
    }

    /**
     * Increment actual_opportunities count for the sales user.
     */
    public static function incrementOpportunityCount(int $salesId): void
    {
        DB::transaction(function () use ($salesId) {
            $target = SalesTarget::getOrCreate(
                $salesId,
                (int) now()->format('Y'),
                (int) now()->format('n')
            );

            $target->increment('actual_opportunities');
        });
    }

    /**
     * Record a won opportunity: increment actual_won and actual_revenue.
     */
    public static function recordWon(Opportunity $opp): void
    {
        DB::transaction(function () use ($opp) {
            $target = SalesTarget::getOrCreate(
                $opp->sales_id,
                (int) now()->format('Y'),
                (int) now()->format('n')
            );

            $value = (float) ($opp->final_value ?? $opp->estimated_value ?? 0);
            $target->increment('actual_won');
            $target->increment('actual_revenue', $value);
        });
    }
}
