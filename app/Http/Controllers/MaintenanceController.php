<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceLog;
use App\Models\Vehicle;
use App\Models\PurchaseOrder;

class MaintenanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->isSales() || $user->isFinance()) {
            abort(403, 'Unauthorized');
        }

        $logs = MaintenanceLog::with('vehicle')
            ->when(request('status'), fn($q, $s) => $q->where('status', $s))
            ->orderBy('scheduled_date', 'desc')
            ->paginate(25);

        $stats = [
            'scheduled'   => MaintenanceLog::where('status', 'scheduled')->count(),
            'in_progress' => MaintenanceLog::where('status', 'in_progress')->count(),
            'completed'   => MaintenanceLog::where('status', 'completed')->count(),
            'total_cost'  => MaintenanceLog::where('status', 'completed')->sum('cost'),
        ];

        $upcomingPOs = PurchaseOrder::where('status', 'pending')->orderBy('created_at', 'desc')->limit(5)->get();

        return view('maintenance.index', compact('logs', 'stats', 'upcomingPOs'));
    }
}
