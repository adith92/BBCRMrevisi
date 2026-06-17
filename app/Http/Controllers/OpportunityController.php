<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Opportunity;
use App\Models\Product;
use App\Models\User;
use App\Helpers\FormatHelper;
use App\Services\PipelineService;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    public function __construct(
        protected PipelineService $pipelineService,
    ) {
        $this->middleware('auth');
        $this->middleware('role:gm,manager,sales')->except(['index', 'show']);
        $this->middleware('role:gm,manager,sales,operational,pool')->only(['index', 'show']);
    }

    // ------------------------------------------------------------------
    // Index — list with role-scoped visibility
    // ------------------------------------------------------------------

    public function index(Request $request)
    {
        $user = auth()->user();
        $selectedManagerId = null;

        if ($user->isGM() && $request->filled('manager_id')) {
            $selectedManagerId = (int) $request->manager_id;
        } elseif ($user->isManager()) {
            $selectedManagerId = $user->id;
        }

        $query = Opportunity::with(['client', 'sales.manager', 'product'])
            ->when(
                $user->isSales(),
                fn ($q) => $q->where('sales_id', $user->id)
            )
            ->when(
                $request->filled('stage'),
                fn ($q) => $q->where('stage', $request->stage)
            )
            ->when(
                $request->filled('client_id'),
                fn ($q) => $q->where('client_id', $request->client_id)
            )
            ->when(
                $request->filled('sales_id') && !$user->isSales() && $selectedManagerId,
                fn ($q) => $q->where('sales_id', $request->sales_id)
            )
            ->when(
                $selectedManagerId && !$user->isSales(),
                fn ($q) => $q->whereHas('sales', fn ($salesQuery) => $salesQuery->where('manager_id', $selectedManagerId))
            )
            ->latest();

        $opportunities = $query->paginate(20)->withQueryString();

        $clients = Client::orderBy('company_name')->get(['id', 'company_name']);
        $managers = collect();
        $salesUsers = collect();
        
        $opportunityRows = $opportunities->getCollection()->map(function ($opportunity) {
            return [
                'id' => $opportunity->id,
                'show_url' => route('opportunities.show', $opportunity->id),
                'client_url' => $opportunity->client ? route('clients.show', $opportunity->client->id) : null,
                'sales_url' => $opportunity->sales ? route('sales.performance', $opportunity->sales->id) : null,
                'opp_number' => $opportunity->opp_number ?? 'OPP-' . str_pad((string) $opportunity->id, 4, '0', STR_PAD_LEFT),
                'title' => $opportunity->title ?? $opportunity->product?->name ?? 'Opportunity #' . $opportunity->id,
                'company_name' => $opportunity->client->company_name ?? '-',
                'sales_name' => $opportunity->sales->name ?? '-',
                'manager_name' => $opportunity->sales?->manager?->name ?? '-',
                'stage' => $opportunity->stage,
                'stage_label' => ucfirst(str_replace('_', ' ', $opportunity->stage)),
                'estimated_value' => (float) ($opportunity->estimated_value ?? 0),
                'estimated_value_fmt' => FormatHelper::formatIDR($opportunity->estimated_value ?? 0),
                'created_at' => $opportunity->created_at?->timestamp ?? 0,
                'created_at_fmt' => $opportunity->created_at?->format('d M Y') ?? '-',
            ];
        })->values();

        if ($user->isGM()) {
            $selectedManagerId = $request->filled('manager_id') ? (int) $request->manager_id : null;
            $managers = User::where('role', 'manager')->orderBy('name')->get(['id', 'name']);

            if ($selectedManagerId) {
                $salesUsers = User::where('manager_id', $selectedManagerId)
                    ->where('role', 'sales')
                    ->orderBy('name')
                    ->get(['id', 'name']);
            }
        } elseif ($user->isManager()) {
            $selectedManagerId = $user->id;
            $salesUsers = User::where('manager_id', $user->id)->where('role', 'sales')->orderBy('name')->get(['id', 'name']);
            $managers = User::where('id', $user->id)->get(['id', 'name']);
        }

        $viewData = compact('opportunities', 'clients', 'managers', 'selectedManagerId', 'opportunityRows');

        if (!$user->isSales()) {
            $viewData['salesUsers'] = $salesUsers;
        }

        return view('opportunities.index', $viewData);
    }

    // ------------------------------------------------------------------
    // Create
    // ------------------------------------------------------------------

    public function create()
    {
        $user = auth()->user();

        $clients = Client::when(
                $user->isSales(),
                fn ($q) => $q->where('assigned_sales_id', $user->id)
            )
            ->orderBy('company_name')
            ->get(['id', 'company_name']);

        $products   = Product::active()->with('category')->orderBy('name')->get();
        $salesUsers = User::where('role', 'sales')->orderBy('name')->get(['id', 'name']);

        return view('pipeline.create', compact('clients', 'products', 'salesUsers'));
    }

    // ------------------------------------------------------------------
    // Store
    // ------------------------------------------------------------------

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'client_id'           => 'required|exists:clients,id',
            'stage'               => 'in:call_meeting,prospecting,proposal,negotiation,won,lost',
            'products'            => 'nullable|array',
            'products.*.id'       => 'nullable|string',
            'products.*.category' => 'required|string',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.estimatedValue' => 'required|numeric|min:0',
            'products.*.details'  => 'nullable|string',
            'expected_close_date' => 'nullable|date',
            'notes'               => 'nullable|string',
            'subType'             => 'nullable|string',
            'estimated_value'     => 'nullable|numeric|min:0',
        ]);

        // Force sales role to own record; managers/GMs shouldn't be creating them based on spec
        abort_if(! $user->isSales(), 403, 'Hanya Sales yang dapat membuat Opportunity baru.');
        $validated['sales_id'] = $user->id;

        $validated['stage'] = 'call_meeting';

        $estimatedValue = 0;
        $products = [];
        if (!empty($validated['products'])) {
            $products = $validated['products'];
            foreach ($products as $p) {
                $estimatedValue += (float)$p['estimatedValue'] * (int)$p['quantity'];
            }
        } else {
            $estimatedValue = $request->input('estimated_value', 0);
        }

        $validated['estimated_value'] = $estimatedValue;
        $validated['products'] = $products;

        $historyEntry = [
            'id' => 'h' . time() . rand(1000, 9999),
            'stage' => 'call_meeting',
            'subType' => $request->subType,
            'timestamp' => now()->toIso8601String(),
            'note' => $request->notes,
            'products' => $products,
            'estimatedValue' => $estimatedValue,
        ];
        $validated['history_timeline'] = [$historyEntry];

        $opportunity = Opportunity::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'opportunity' => $opportunity]);
        }

        return redirect()->route('pipeline.index')
            ->with('success', "Opportunity {$opportunity->opp_number} berhasil dibuat.");
    }

    // ------------------------------------------------------------------
    // Show
    // ------------------------------------------------------------------

    public function show(Opportunity $opportunity)
    {
        $this->authorizeView($opportunity);

        $opportunity->load([
            'client',
            'sales',
            'product.category',
            'approver',
            'booking',
            'subscription',
            'assignedVehicles',
            'assignedDrivers',
        ]);

        $activityLogs = $opportunity->activityLogs()
            ->with('sales')
            ->latest('activity_date')
            ->take(5)
            ->get();

        $nextStages = $this->pipelineService->getNextStages($opportunity->stage);

        $approvalRequests = $opportunity->approvalRequests()
            ->with(['requester', 'currentApprover'])
            ->latest()
            ->get();

        return view('pipeline.show', compact(
            'opportunity',
            'activityLogs',
            'nextStages',
            'approvalRequests'
        ));
    }

    // ------------------------------------------------------------------
    // Update
    // ------------------------------------------------------------------

    public function update(Request $request, Opportunity $opportunity)
    {
        $this->authorizeEdit($opportunity);

        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'client_id'           => 'required|exists:clients,id',
            'stage'               => 'required|in:call_meeting,prospecting,proposal,negotiation,won,lost',
            'products'            => 'nullable|array',
            'products.*.id'       => 'nullable|string',
            'products.*.category' => 'required|string',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.estimatedValue' => 'required|numeric|min:0',
            'products.*.details'  => 'nullable|string',
            'estimated_value'           => 'nullable|numeric|min:0',
            'final_value'               => 'required_if:stage,won|nullable|numeric|min:0',
            'contract_duration_months'  => 'required_if:stage,won|nullable|integer|min:1',
            'pax'                       => 'nullable|integer|min:1',
            'expected_close_date'       => 'nullable|date',
            'actual_close_date'         => 'nullable|date',
            'lost_reason'               => 'nullable|string',
            'notes'                     => 'nullable|string',
            'subType'                   => 'nullable|string',
        ]);

        $estimatedValue = 0;
        if (!empty($validated['products'])) {
            foreach ($validated['products'] as $p) {
                $estimatedValue += (float)$p['estimatedValue'] * (int)$p['quantity'];
            }
        } else {
            $estimatedValue = $request->input('estimated_value', $opportunity->estimated_value ?? 0);
        }
        $validated['estimated_value'] = $estimatedValue;

        // Stage change validation
        $oldStage = $opportunity->stage;
        $isStageChanged = $validated['stage'] !== $oldStage;

        // Enforce that assignments are only allowed if the Opportunity stage is 'won', 'negotiation', or 'proposal'
        $targetStage = strtolower($validated['stage'] ?? $opportunity->stage);
        $hasFleet = !empty($request->input('fleet_ids'));
        $hasDrivers = !empty($request->input('driver_ids'));
        if (!in_array($targetStage, ['proposal', 'negotiation', 'won']) && ($hasFleet || $hasDrivers)) {
            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => 'Assignments are only allowed if the Opportunity stage is Proposal, Negotiation, or Won.'], 422);
            }
            return back()->withErrors([
                'stage' => 'Unit kendaraan dan supir hanya dapat diassign jika status oportunitas adalah Proposal, Negotiation, atau WON.',
            ]);
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            if ($isStageChanged) {
                if (!$this->pipelineService->canTransition($opportunity->stage, $validated['stage'])) {
                    if ($request->wantsJson()) {
                        return response()->json(['ok' => false, 'message' => "Tidak dapat berpindah ke {$validated['stage']}."], 422);
                    }
                    return back()->withErrors([
                        'stage' => "Tidak dapat berpindah dari {$opportunity->stage} ke {$validated['stage']}.",
                    ]);
                }

                // Validasi hak akses khusus untuk role 'sales' yang merupakan pemilik oportunitas
                if (auth()->user()->role !== 'sales') {
                    if ($request->wantsJson()) {
                        return response()->json(['ok' => false, 'message' => 'Akses ditolak: Hanya Sales yang dapat mengubah stage.'], 403);
                    }
                    return back()->withErrors(['stage' => 'Akses ditolak: Hanya Sales yang dapat mengubah stage.']);
                }

                if ($opportunity->sales_id !== auth()->id()) {
                    if ($request->wantsJson()) {
                        return response()->json(['ok' => false, 'message' => 'Akses ditolak: Hanya Sales pemilik oportunitas yang dapat mengubah stage.'], 403);
                    }
                    return back()->withErrors(['stage' => 'Akses ditolak: Hanya Sales pemilik oportunitas yang dapat mengubah stage.']);
                }

                // Database Transaction for Fleet & Driver
                // Check if opportunity has Mobil Long Term product
                $hasMobilLongTerm = false;
                if (is_array($opportunity->products)) {
                    foreach ($opportunity->products as $p) {
                        if (isset($p['category']) && $p['category'] === 'Mobil Long Term') {
                            $hasMobilLongTerm = true;
                            break;
                        }
                    }
                }

                // Database Transaction for Fleet & Driver
                $targetFleetStatus = $validated['stage'] === 'won' ? 'assigned' : (in_array($validated['stage'], ['proposal', 'negotiation']) ? 'reserved' : 'available');
                $targetDriverStatus = $validated['stage'] === 'won' ? 'assigned' : (in_array($validated['stage'], ['proposal', 'negotiation']) ? 'reserved' : 'available');

                $fleetIds = $request->has('fleet_ids') ? ($request->input('fleet_ids') ?: []) : null;
                $driverIds = $request->has('driver_ids') ? ($request->input('driver_ids') ?: []) : null;

                // Enforce short term restrictions at won stage
                if ($validated['stage'] === 'won' && !$hasMobilLongTerm) {
                    $fleetIds = [];
                    $driverIds = [];
                }

                if ($fleetIds !== null) {
                    $opportunity->assignedVehicles()->whereNotIn('id', $fleetIds)->update([
                        'assigned_opportunity_id' => null,
                        'status' => 'available'
                    ]);

                    if (count($fleetIds) > 0) {
                        $vehiclesToAssign = \App\Models\Vehicle::whereIn('id', $fleetIds)->lockForUpdate()->get();
                        foreach ($vehiclesToAssign as $vehicle) {
                            if ($vehicle->assigned_opportunity_id !== $opportunity->id && $vehicle->status !== 'available') {
                                throw new \Exception("Unit kendaraan {$vehicle->plate_number} sudah dibooking oleh Sales lain.");
                            }
                            $vehicle->update(['assigned_opportunity_id' => $opportunity->id, 'status' => $targetFleetStatus]);
                        }
                    }
                } else if (in_array($validated['stage'], ['lost', 'call_meeting', 'prospecting'])) {
                    $opportunity->assignedVehicles()->update(['assigned_opportunity_id' => null, 'status' => 'available']);
                }

                if ($driverIds !== null) {
                    $opportunity->assignedDrivers()->whereNotIn('id', $driverIds)->update([
                        'assigned_opportunity_id' => null,
                        'status' => 'available'
                    ]);

                    if (count($driverIds) > 0) {
                        $driversToAssign = \App\Models\Driver::whereIn('id', $driverIds)->lockForUpdate()->get();
                        foreach ($driversToAssign as $driver) {
                            if ($driver->assigned_opportunity_id !== $opportunity->id && $driver->status !== 'available') {
                                throw new \Exception("Supir {$driver->name} sudah dibooking oleh Sales lain.");
                            }
                            $driver->update(['assigned_opportunity_id' => $opportunity->id, 'status' => $targetDriverStatus]);
                        }
                    }
                } else if (in_array($validated['stage'], ['lost', 'call_meeting', 'prospecting'])) {
                    $opportunity->assignedDrivers()->update(['assigned_opportunity_id' => null, 'status' => 'available']);
                }

            // Log stage transition as activity
            ActivityLog::create([
                'sales_id'       => auth()->id(),
                'client_id'      => $opportunity->client_id,
                'opportunity_id' => $opportunity->id,
                'type'           => 'follow_up',
                'subject'        => "Stage berubah: {$opportunity->stage} → {$validated['stage']}",
                'activity_date'  => now(),
            ]);

            if ($validated['stage'] === 'won') {
                $validated['final_value'] = $validated['final_value'] ?? $estimatedValue;
                $validated['actual_close_date'] = $validated['actual_close_date'] ?? now()->toDateString();
            }
            $validated['stage_changed_at'] = now();

            $historyVehicles = [];
            if (isset($fleetIds) && is_array($fleetIds) && count($fleetIds) > 0) {
                $historyVehicles = \App\Models\Vehicle::whereIn('id', $fleetIds)->get(['id', 'plate_number', 'model'])->toArray();
            } else if (in_array($validated['stage'], ['won', 'negotiation', 'proposal'])) {
                $historyVehicles = $opportunity->assignedVehicles()->get(['id', 'plate_number', 'model'])->toArray();
            }

            $historyDrivers = [];
            if (isset($driverIds) && is_array($driverIds) && count($driverIds) > 0) {
                $historyDrivers = \App\Models\Driver::whereIn('id', $driverIds)->get(['id', 'name'])->toArray();
            } else if (in_array($validated['stage'], ['won', 'negotiation', 'proposal'])) {
                $historyDrivers = $opportunity->assignedDrivers()->get(['id', 'name'])->toArray();
            }

            $history = $opportunity->history_timeline ?? [];
            $history[] = [
                'id' => 'h' . time() . rand(1000, 9999),
                'stage' => $validated['stage'],
                'subType' => $request->subType,
                'timestamp' => now()->toIso8601String(),
                'note' => $request->notes,
                'products' => $validated['products'] ?? $opportunity->products,
                'estimatedValue' => $estimatedValue,
                'vehicles' => $historyVehicles,
                'drivers' => $historyDrivers,
            ];
            $validated['history_timeline'] = $history;
        } else {
            // Update latest history entry if products/estimatedValue changed
            $history = $opportunity->history_timeline ?? [];
            if (count($history) > 0) {
                $lastIdx = count($history) - 1;
                $history[$lastIdx]['products'] = $validated['products'] ?? $opportunity->products;
                $history[$lastIdx]['estimatedValue'] = $estimatedValue;
                if ($request->notes) {
                    $history[$lastIdx]['note'] = $request->notes;
                }
                $validated['history_timeline'] = $history;
            }
        }

        $opportunity->update($validated);
        
        if ($isStageChanged && $validated['stage'] === 'won') {
            $this->pipelineService->triggerWonActions($opportunity);
        }

        \Illuminate\Support\Facades\DB::commit();

        if ($request->wantsJson()) {
            $opportunity->refresh()->load([
                'client:id,company_name',
                'sales:id,name',
                'assignedDrivers:id,assigned_opportunity_id,name',
                'assignedVehicles:id,assigned_opportunity_id,plate_number,model'
            ]);
            return response()->json(['ok' => true, 'opportunity' => $opportunity]);
        }

        return back()->with('success', 'Opportunity berhasil diperbarui.');
        
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['booking' => $e->getMessage()]);
        }
    }

    // ------------------------------------------------------------------
    // Destroy
    // ------------------------------------------------------------------

    public function destroy(Opportunity $opportunity)
    {
        $this->authorizeEdit($opportunity);

        if (!in_array($opportunity->stage, ['prospecting', 'lost'])) {
            return back()->withErrors(['delete' => 'Hanya opportunity di stage Prospecting atau Lost yang dapat dihapus.']);
        }

        $opportunity->delete();

        return redirect()->route('pipeline.index')
            ->with('success', 'Opportunity berhasil dihapus.');
    }

    // ------------------------------------------------------------------
    // Advance Stage (POST)
    // ------------------------------------------------------------------

    public function advanceStage(Request $request, Opportunity $opportunity)
    {
        $user = auth()->user();
        if ($user->role !== 'sales' || $opportunity->sales_id !== $user->id) {
            abort(403, 'Akses ditolak: Hanya Sales pemilik yang dapat mengubah stage.');
        }

        $validated = $request->validate([
            'stage'                     => 'required|in:call_meeting,prospecting,proposal,negotiation,won,lost',
            'lost_reason'               => 'required_if:stage,lost|nullable|string',
            'final_value'               => 'required_if:stage,won|nullable|numeric|min:0',
            'contract_duration_months'  => 'required_if:stage,won|nullable|integer|min:1',
            'notes'                     => 'nullable|string',
            'fleet_ids'                 => 'nullable|array',
            'fleet_ids.*'               => 'exists:vehicles,id',
            'driver_ids'                => 'nullable|array',
            'driver_ids.*'              => 'exists:drivers,id',
        ]);

        if (!$this->pipelineService->canTransition($opportunity->stage, $validated['stage'])) {
            return back()->withErrors([
                'stage' => "Transisi dari {$opportunity->stage} ke {$validated['stage']} tidak diizinkan.",
            ]);
        }

        // Enforce that assignments are only allowed if the Opportunity stage is 'won', 'negotiation', or 'proposal'
        $targetStage = strtolower($validated['stage'] ?? $opportunity->stage);
        $hasFleet = !empty($request->input('fleet_ids'));
        $hasDrivers = !empty($request->input('driver_ids'));
        if (!in_array($targetStage, ['proposal', 'negotiation', 'won']) && ($hasFleet || $hasDrivers)) {
            return back()->withErrors([
                'stage' => 'Unit kendaraan dan supir hanya dapat diassign jika status oportunitas adalah Proposal, Negotiation, atau WON.',
            ]);
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $targetFleetStatus = $validated['stage'] === 'won' ? 'assigned' : (in_array($validated['stage'], ['proposal', 'negotiation']) ? 'reserved' : 'available');
            $targetDriverStatus = $validated['stage'] === 'won' ? 'assigned' : (in_array($validated['stage'], ['proposal', 'negotiation']) ? 'reserved' : 'available');

            // Check if opportunity has Mobil Long Term product
            $hasMobilLongTerm = false;
            if (is_array($opportunity->products)) {
                foreach ($opportunity->products as $p) {
                    if (isset($p['category']) && $p['category'] === 'Mobil Long Term') {
                        $hasMobilLongTerm = true;
                        break;
                    }
                }
            }

            $fleetIds = isset($validated['fleet_ids']) ? $validated['fleet_ids'] : null;
            $driverIds = isset($validated['driver_ids']) ? $validated['driver_ids'] : null;

            // Enforce short term restrictions at won stage
            if ($validated['stage'] === 'won' && !$hasMobilLongTerm) {
                $fleetIds = [];
                $driverIds = [];
            }

            // Handle Fleet Assignments
            if ($fleetIds !== null) {
                // Release old fleets
                $opportunity->assignedVehicles()->whereNotIn('id', $fleetIds)->update([
                    'assigned_opportunity_id' => null,
                    'status' => 'available'
                ]);

                // Assign new fleets
                if (count($fleetIds) > 0) {
                    $vehiclesToAssign = \App\Models\Vehicle::whereIn('id', $fleetIds)
                        ->lockForUpdate()
                        ->get();
                    
                    foreach ($vehiclesToAssign as $vehicle) {
                        if ($vehicle->assigned_opportunity_id !== $opportunity->id && $vehicle->status !== 'available') {
                            throw new \Exception("Unit kendaraan {$vehicle->plate_number} sudah dibooking oleh Sales lain.");
                        }
                        $vehicle->update([
                            'assigned_opportunity_id' => $opportunity->id,
                            'status' => $targetFleetStatus
                        ]);
                    }
                }
            } else if (in_array($validated['stage'], ['lost', 'call_meeting', 'prospecting'])) {
                 $opportunity->assignedVehicles()->update([
                    'assigned_opportunity_id' => null,
                    'status' => 'available'
                ]);
            }

            // Handle Driver Assignments
            if ($driverIds !== null) {
                // Release old drivers
                $opportunity->assignedDrivers()->whereNotIn('id', $driverIds)->update([
                    'assigned_opportunity_id' => null,
                    'status' => 'available'
                ]);

                // Assign new drivers
                if (count($driverIds) > 0) {
                    $driversToAssign = \App\Models\Driver::whereIn('id', $driverIds)
                        ->lockForUpdate()
                        ->get();
                    
                    foreach ($driversToAssign as $driver) {
                        if ($driver->assigned_opportunity_id !== $opportunity->id && $driver->status !== 'available') {
                            throw new \Exception("Supir {$driver->name} sudah dibooking oleh Sales lain.");
                        }
                        $driver->update([
                            'assigned_opportunity_id' => $opportunity->id,
                            'status' => $targetDriverStatus
                        ]);
                    }
                }
            } else if (in_array($validated['stage'], ['lost', 'call_meeting', 'prospecting'])) {
                 $opportunity->assignedDrivers()->update([
                    'assigned_opportunity_id' => null,
                    'status' => 'available'
                ]);
            }

            // Log the stage advance as an activity
            ActivityLog::create([
                'sales_id'       => auth()->id(),
                'client_id'      => $opportunity->client_id,
                'opportunity_id' => $opportunity->id,
                'type'           => 'follow_up',
                'subject'        => "Stage diadvance: {$opportunity->stage} → {$validated['stage']}",
                'notes'          => $validated['notes'] ?? null,
                'activity_date'  => now(),
            ]);

            $historyVehicles = [];
            if (isset($fleetIds) && is_array($fleetIds) && count($fleetIds) > 0) {
                $historyVehicles = \App\Models\Vehicle::whereIn('id', $fleetIds)->get(['id', 'plate_number', 'model'])->toArray();
            } else if (in_array($validated['stage'], ['won', 'negotiation', 'proposal'])) {
                $historyVehicles = $opportunity->assignedVehicles()->get(['id', 'plate_number', 'model'])->toArray();
            }

            $historyDrivers = [];
            if (isset($driverIds) && is_array($driverIds) && count($driverIds) > 0) {
                $historyDrivers = \App\Models\Driver::whereIn('id', $driverIds)->get(['id', 'name'])->toArray();
            } else if (in_array($validated['stage'], ['won', 'negotiation', 'proposal'])) {
                $historyDrivers = $opportunity->assignedDrivers()->get(['id', 'name'])->toArray();
            }

            $history = $opportunity->history_timeline ?? [];
            $history[] = [
                'id' => 'h' . time() . rand(1000, 9999),
                'stage' => $validated['stage'],
                'subType' => $request->input('subType') ?? 'Call',
                'timestamp' => now()->toIso8601String(),
                'note' => $validated['notes'] ?? null,
                'products' => $opportunity->products,
                'estimatedValue' => $opportunity->estimated_value,
                'vehicles' => $historyVehicles,
                'drivers' => $historyDrivers,
            ];

            $updates = [
                'stage' => $validated['stage'],
                'stage_changed_at' => now(),
                'history_timeline' => $history,
            ];

            if ($validated['stage'] === 'lost' && !empty($validated['lost_reason'])) {
                $updates['lost_reason']        = $validated['lost_reason'];
                $updates['actual_close_date']  = now()->toDateString();
            }

            if ($validated['stage'] === 'won') {
                $updates['actual_close_date']  = now()->toDateString();
                $updates['final_value']        = $validated['final_value'];
                $updates['contract_duration_months'] = $validated['contract_duration_months'];
                $opportunity->update($updates);
                $this->pipelineService->triggerWonActions($opportunity->fresh());
                
                \Illuminate\Support\Facades\DB::commit();
                return back()->with('success', 'Selamat! Opportunity berhasil dimenangkan dan Unit Operasional berhasil dialokasikan.');
            }

            $opportunity->update($updates);

            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', "Stage berhasil diubah ke {$validated['stage']}.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withErrors(['booking' => $e->getMessage()]);
        }
    }


    // ------------------------------------------------------------------
    // Kanban drag-drop: move card to new stage (PATCH, JSON)
    // ------------------------------------------------------------------

    public function moveStage(Request $request, Opportunity $opportunity)
    {
        $user = auth()->user();
        if ($user->role !== 'sales' || $opportunity->sales_id !== $user->id) {
            return response()->json([
                'ok'      => false,
                'message' => 'Akses ditolak: Hanya Sales pemilik yang dapat mengubah stage.',
            ], 403);
        }

        $validated = $request->validate([
            'stage'                     => 'required|in:call_meeting,prospecting,proposal,negotiation,won,lost',
            'lost_reason'               => 'nullable|string|max:500',
            'estimated_value'           => 'nullable|numeric|min:0',
            'final_value'               => 'required_if:stage,won|nullable|numeric|min:0',
            'contract_duration_months'  => 'required_if:stage,won|nullable|integer|min:1',
            'notes'                     => 'nullable|string',
        ]);

        $fromStage = $opportunity->stage;
        $toStage   = $validated['stage'];

        if ($fromStage === $toStage) {
            return response()->json(['ok' => true, 'message' => 'No change.']);
        }

        if (!$this->pipelineService->canTransition($fromStage, $toStage)) {
            return response()->json([
                'ok'      => false,
                'message' => "Transisi dari {$fromStage} ke {$toStage} tidak diizinkan.",
            ], 422);
        }

        $updates = [
            'stage' => $toStage,
            'stage_changed_at' => now(),
        ];

        if (isset($validated['estimated_value'])) {
            $updates['estimated_value'] = $validated['estimated_value'];
        }

        if ($toStage === 'lost') {
            $updates['lost_reason']       = $validated['lost_reason'] ?? 'Dipindah via Kanban';
            $updates['actual_close_date'] = now()->toDateString();
        }

        if ($toStage === 'won') {
            $updates['actual_close_date'] = now()->toDateString();
            $updates['final_value']        = $validated['final_value'];
            $updates['contract_duration_months'] = $validated['contract_duration_months'];
        }

        $opportunity->update($updates);

        $activityNotes = "Dipindah via drag-drop kanban";
        if (!empty($validated['notes'])) {
            $activityNotes = $validated['notes'];
        }

        ActivityLog::create([
            'sales_id'       => auth()->id(),
            'client_id'      => $opportunity->client_id,
            'opportunity_id' => $opportunity->id,
            'type'           => 'follow_up',
            'subject'        => "Kanban: {$fromStage} → {$toStage}",
            'notes'          => $activityNotes,
            'activity_date'  => now(),
        ]);

        if ($toStage === 'won') {
            $this->pipelineService->triggerWonActions($opportunity->fresh());
        }

        // Return per-stage summary scoped to current user's visible opportunities
        $summaryQuery = Opportunity::selectRaw("stage, COUNT(*) as count, COALESCE(SUM(estimated_value),0) as total");
        if ($user->role === 'sales') {
            $summaryQuery->where('sales_id', $user->id);
        } elseif ($user->role === 'manager') {
            $subIds = $user->subordinates()->pluck('id')->push($user->id);
            $summaryQuery->whereIn('sales_id', $subIds);
        }
        $summary = $summaryQuery->groupBy('stage')
            ->get()
            ->keyBy('stage')
            ->map(fn($r) => ['count' => (int)$r->count, 'total' => (float)$r->total])
            ->toArray();

        return response()->json([
            'ok'      => true,
            'message' => "Deal dipindah ke {$toStage}.",
            'stage'   => $toStage,
            'summary' => $summary,
            'opportunity' => [
                'estimated_value' => $opportunity->estimated_value
            ]
        ]);
    }

    // ------------------------------------------------------------------
    // Quick update (inline edit from Kanban card) — PATCH, JSON
    // ------------------------------------------------------------------

    public function quickUpdate(Request $request, Opportunity $opportunity)
    {
        $this->authorizeEdit($opportunity);

        $validated = $request->validate([
            'title'               => 'sometimes|required|string|max:255',
            'estimated_value'     => 'sometimes|nullable|numeric|min:0',
            'expected_close_date' => 'sometimes|nullable|date',
            'notes'               => 'sometimes|nullable|string',
            'pax'                 => 'sometimes|nullable|integer|min:1',
        ]);

        $opportunity->update($validated);

        return response()->json([
            'ok'          => true,
            'opportunity' => $opportunity->fresh(['client', 'sales', 'product']),
        ]);
    }

    // ------------------------------------------------------------------
    // Get History Timeline
    // ------------------------------------------------------------------

    public function getHistory(Opportunity $opportunity)
    {
        $this->authorizeView($opportunity);

        return response()->json([
            'history_timeline' => $opportunity->history_timeline ?? []
        ]);
    }

    // ------------------------------------------------------------------
    // 360° view data (GET, JSON)
    // ------------------------------------------------------------------

    public function view360(Opportunity $opportunity)
    {
        $this->authorizeView($opportunity);

        $opportunity->load([
            'client',
            'sales',
            'product.category',
            'approver',
            'activityLogs' => fn($q) => $q->latest()->limit(20),
            'activityLogs.sales',
            'booking',
            'subscription',
        ]);

        return response()->json([
            'ok'          => true,
            'opportunity' => $opportunity,
        ]);
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    // ------------------------------------------------------------------
    // Apply discount to opportunity — POST /opportunities/{o}/discount
    // ------------------------------------------------------------------

    public function storeDiscount(Request $request, Opportunity $opportunity)
    {
        $user = auth()->user();
        if ($user->role === 'sales' && $opportunity->sales_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'discount_percent' => 'required|numeric|min:0|max:100',
            'discount_notes'   => 'nullable|string|max:500',
        ]);

        $opportunity->update([
            'discount_percent' => $validated['discount_percent'],
            'discount_notes'   => $validated['discount_notes'] ?? null,
        ]);

        ActivityLog::create([
            'sales_id'       => $user->id,
            'client_id'      => $opportunity->client_id,
            'opportunity_id' => $opportunity->id,
            'type'           => 'follow_up',
            'subject'        => "Diskon {$validated['discount_percent']}% diterapkan",
            'notes'          => $validated['discount_notes'] ?? '',
            'activity_date'  => now(),
        ]);

        return back()->with('success', 'Diskon berhasil disimpan.');
    }

    // ------------------------------------------------------------------
    // Opportunities by client — GET /api/opportunities/by-client/{client}
    // ------------------------------------------------------------------

    public function byClient(\App\Models\Client $client)
    {
        $user  = auth()->user();
        $query = Opportunity::with('product')
            ->where('client_id', $client->id);

        if ($user->role === 'sales') {
            $query->where('sales_id', $user->id);
        } elseif ($user->role === 'manager') {
            $subIds = $user->subordinates()->pluck('id')->push($user->id);
            $query->whereIn('sales_id', $subIds);
        }

        $opportunities = $query->orderByDesc('created_at')->get([
            'id', 'opp_number', 'stage', 'estimated_value', 'final_value',
            'created_at', 'actual_close_date',
        ]);

        return response()->json(['ok' => true, 'opportunities' => $opportunities]);
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    protected function authorizeView(Opportunity $opportunity): void
    {
        \Illuminate\Support\Facades\Gate::authorize('view', $opportunity);
    }

    protected function authorizeEdit(Opportunity $opportunity): void
    {
        \Illuminate\Support\Facades\Gate::authorize('update', $opportunity);
    }
}
