<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;

class PipelineController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:director,gm,manager,sales');
    }

    public function index()
    {
        $user = auth()->user();

        $stages = ['prospecting', 'proposal', 'negotiation', 'won', 'lost'];

        // Build base query scoped by role
        $baseQuery = Opportunity::with(['client', 'sales', 'product'])
            ->when($user->isSales(), fn ($q) => $q->where('sales_id', $user->id));

        $allOpps = $baseQuery->get();

        // Group by stage, build kanban structure
        $kanban = [];
        foreach ($stages as $stage) {
            $stageOpps = $allOpps->where('stage', $stage)->values();
            $kanban[$stage] = [
                'opportunities' => $stageOpps,
                'count'         => $stageOpps->count(),
                'total_value'   => $stageOpps->sum(fn ($o) => (float) ($o->estimated_value ?? 0)),
            ];
        }

        return view('pipeline.index', compact('kanban', 'stages'));
    }
}
