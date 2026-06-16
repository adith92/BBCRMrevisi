@extends('layouts.app')

@section('header_title', 'Operational Pool & Long-Term Fleet')

@push('styles')
<style>
    /* Add slight transition for smooth hover effects */
    .fleet-card {
        transition: all 0.2s ease-in-out;
    }
    .fleet-card:hover {
        border-color: rgba(99, 102, 241, 0.2); /* indigo-500/20 */
        background-color: rgba(255, 255, 255, 0.1);
    }
</style>
@endpush

@section('content')
@php
    $canModify = auth()->user()->isOperational() || auth()->user()->isManager();
    $statusColors = [
        'available'   => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        'maintenance' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        'rent_out'    => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        'assigned'    => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        'booked'      => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
        'hold'        => 'bg-pink-500/10 text-pink-400 border-pink-500/20',
        'inactive'    => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
    ];
@endphp

<script type="application/json" id="pending-assignments-data">
    @json(isset($pendingAssignments) ? $pendingAssignments : [])
</script>

<div class="space-y-6 pb-20" x-data="fleetPage()">
    
    {{-- Header Panel --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-[var(--cc-text)] mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined h-8 w-8 text-indigo-400" style="font-size: 32px">directions_car</span>
                Operational Pool & Long-Term Fleet
            </h1>
            <p class="text-[var(--cc-text-muted)] max-w-2xl text-sm">
                Operational dashboard to register, allocate, and monitor vehicle fleets assigned exclusively to <strong class="text-indigo-400">Mobil Long Term</strong> contracts.
            </p>
        </div>
        
        @if($canModify)
        <div class="flex items-center gap-3">
            <button @click="showCreateModal = true" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-lg shadow-indigo-600/30 hover:bg-indigo-500 transition-all">
                <span class="material-symbols-outlined text-[16px]">add</span>
                Register Vehicle
            </button>
        </div>
        @endif
    </div>

    {{-- Pending Assignments --}}
    @if(isset($pendingAssignments) && $pendingAssignments->count() > 0 && $canModify)
    <div class="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-6">
        <h2 class="text-amber-500 font-bold mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined">warning</span>
            Pending Vehicle Assignments ({{ $pendingAssignments->count() }} Opportunities)
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($pendingAssignments as $opp)
            <div class="bg-[var(--cc-surface)] border border-[var(--cc-border)] rounded-xl p-4 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="font-bold text-[var(--cc-text)] text-sm mb-1">{{ $opp->title }}</div>
                    <div class="text-xs text-[var(--cc-text-muted)] mb-3 flex justify-between">
                        <span>{{ $opp->client->company_name ?? 'No Client' }}</span>
                        <span class="px-2 py-0.5 rounded-full bg-slate-500/10 border border-slate-500/20 uppercase">{{ $opp->stage }}</span>
                    </div>
                    <div class="text-sm bg-amber-500/5 p-2 rounded border border-amber-500/10 mb-4">
                        <div class="flex justify-between mb-1"><span>Required:</span> <strong>{{ $opp->required_fleets }}</strong></div>
                        <div class="flex justify-between mb-1"><span>Assigned:</span> <strong>{{ $opp->assignedVehicles->count() }}</strong></div>
                        <div class="flex justify-between text-amber-500 font-bold"><span>Missing:</span> <span>{{ $opp->missing_fleets }} Unit(s)</span></div>
                    </div>
                </div>
                <button @click="openAssignModal({{ $opp->id }})" class="w-full py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-bold transition">
                    Assign Remaining
                </button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Fleet Stats Grid --}}
    @php
        $currentStatus = request('status', 'All');
    @endphp
    <div class="grid gap-4" :class="showMaintenanceDetails ? 'grid-cols-2 lg:grid-cols-9' : 'grid-cols-2 lg:grid-cols-7'">
        <a href="{{ request()->fullUrlWithQuery(['status' => 'All']) }}" 
           class="block rounded-2xl border bg-[var(--cc-surface)] p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'All' ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-[var(--cc-border)] hover:border-indigo-500/40' }}">
            <div class="text-xs font-bold text-[var(--cc-text-muted)] uppercase tracking-wider">Total Fleet</div>
            <div class="text-3xl font-mono font-bold text-[var(--cc-text)] mt-1">{{ $stats['total'] }}</div>
            <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">Mobil Long Term units</div>
        </a>

        <a href="{{ request()->fullUrlWithQuery(['status' => 'approval_pending']) }}" 
           class="block rounded-2xl border bg-indigo-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'approval_pending' ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-indigo-500/20 hover:border-indigo-500/50' }}">
            <div class="text-xs font-bold text-indigo-400 uppercase tracking-wider">Approval Pending</div>
            <div class="text-3xl font-mono font-bold text-indigo-400 mt-1">{{ $pendingAssignments->count() }}</div>
            <div class="text-[10px] text-indigo-500 mt-1">Pending Assignments</div>
        </a>
        
        <a href="{{ request()->fullUrlWithQuery(['status' => 'available']) }}" 
           class="block rounded-2xl border bg-emerald-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'available' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-emerald-500/20 hover:border-emerald-500/50' }}">
            <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Available</div>
            <div class="text-3xl font-mono font-bold text-emerald-400 mt-1">{{ $stats['available'] }}</div>
            <div class="text-[10px] text-emerald-500 mt-1">Ready for assignment</div>
        </a>
        
        <a href="{{ request()->fullUrlWithQuery(['status' => 'rent_out']) }}" 
           class="block rounded-2xl border bg-blue-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'rent_out' ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-blue-500/20 hover:border-blue-500/50' }}">
            <div class="text-xs font-bold text-blue-400 uppercase tracking-wider">Rented Out</div>
            <div class="text-3xl font-mono font-bold text-blue-400 mt-1">{{ $stats['rented'] }}</div>
            <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">On active contract</div>
        </a>
        
        <a href="{{ request()->fullUrlWithQuery(['status' => 'booked']) }}" 
           class="block rounded-2xl border bg-purple-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'booked' ? 'border-purple-500 ring-2 ring-purple-500/20' : 'border-purple-500/20 hover:border-purple-500/50' }}">
            <div class="text-xs font-bold text-purple-400 uppercase tracking-wider">Booked</div>
            <div class="text-3xl font-mono font-bold text-purple-400 mt-1">{{ $stats['booked'] }}</div>
            <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">Earmarked/Reserved</div>
        </a>
        
        <a href="{{ request()->fullUrlWithQuery(['status' => 'hold']) }}" 
           class="block rounded-2xl border bg-pink-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'hold' ? 'border-pink-500 ring-2 ring-pink-500/20' : 'border-pink-500/20 hover:border-pink-500/50' }}">
            <div class="text-xs font-bold text-pink-400 uppercase tracking-wider">Hold</div>
            <div class="text-3xl font-mono font-bold text-pink-400 mt-1">{{ $stats['hold'] }}</div>
            <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">Pending negotiation</div>
        </a>
 
        <template x-if="!showMaintenanceDetails">
            <a href="{{ request()->fullUrlWithQuery(['status' => 'maintenance']) }}" 
               @click="showMaintenanceDetails = true" 
               class="block rounded-2xl border bg-amber-500/10 p-4 backdrop-blur-md cursor-pointer transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'maintenance' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-amber-500/20 hover:border-amber-500/50' }}">
                <div class="text-xs font-bold text-amber-400 uppercase tracking-wider">Maintenance</div>
                <div class="text-3xl font-mono font-bold text-amber-400 mt-1">{{ $stats['maintenance'] }}</div>
                <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">Click to view details <span class="text-[10px]">▼</span></div>
            </a>
        </template>
        <template x-if="showMaintenanceDetails">
            <div class="contents">
                <a href="{{ request()->fullUrlWithQuery(['status' => 'maintenance']) }}" 
                   @click="showMaintenanceDetails = false" 
                   class="block rounded-2xl border bg-amber-500/10 p-4 backdrop-blur-md cursor-pointer transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'maintenance' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-amber-500/20 hover:border-amber-500/50' }}">
                    <div class="text-xs font-bold text-amber-400 uppercase tracking-wider">Maintenance</div>
                    <div class="text-3xl font-mono font-bold text-amber-400 mt-1">{{ $stats['maintenance'] }}</div>
                    <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">Total in workshop <span class="text-[10px]">▲</span></div>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'Being Serviced']) }}" 
                   class="block rounded-2xl border bg-rose-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg animate-in fade-in slide-in-from-left-4 duration-300 {{ $currentStatus === 'Being Serviced' ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-rose-500/20 hover:border-rose-500/50' }}">
                    <div class="text-xs font-bold text-rose-400 uppercase tracking-wider">Servicing</div>
                    <div class="text-3xl font-mono font-bold text-rose-400 mt-1">{{ $stats['beingServiced'] }}</div>
                    <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">In repair</div>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'In Queue']) }}" 
                   class="block rounded-2xl border bg-orange-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg animate-in fade-in slide-in-from-left-4 duration-300 {{ $currentStatus === 'In Queue' ? 'border-orange-500 ring-2 ring-orange-500/20' : 'border-orange-500/20 hover:border-orange-500/50' }}">
                    <div class="text-xs font-bold text-orange-400 uppercase tracking-wider">In Queue</div>
                    <div class="text-3xl font-mono font-bold text-orange-400 mt-1">{{ $stats['inQueue'] }}</div>
                    <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">Workshop queue</div>
                </a>
            </div>
        </template>
    </div>

    @if($currentStatus === 'approval_pending')
        {{-- Approval Pending List and sorting --}}
        <div class="bg-[var(--cc-surface)] border border-[var(--cc-border)] rounded-3xl p-6 shadow-sm space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-[var(--cc-text)] flex items-center gap-2">
                        <span class="material-symbols-outlined text-indigo-400">assignment_late</span>
                        Opportunities Awaiting Assignment
                    </h2>
                    <p class="text-sm text-[var(--cc-text-muted)] mt-1">
                        Daftar kontrak "Mobil Long Term" yang sudah dimenangkan (Won) atau tahap proposal/negosiasi namun armadanya belum dialokasikan secara penuh.
                    </p>
                </div>
                
                {{-- Sorting Controls --}}
                <div class="flex items-center gap-2 text-xs bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] p-1.5 rounded-xl">
                    <span class="text-[var(--cc-text-muted)] font-medium px-2">Sort By:</span>
                    @php
                        $sortPending = request('sort_pending', 'date');
                        $direction = request('direction', 'asc');
                    @endphp
                    <a href="{{ request()->fullUrlWithQuery(['sort_pending' => 'name', 'direction' => ($sortPending === 'name' && $direction === 'asc') ? 'desc' : 'asc', 'status' => 'approval_pending']) }}" class="px-3 py-1.5 rounded-lg font-semibold hover:bg-black/5 dark:hover:bg-white/5 transition flex items-center gap-1 {{ $sortPending === 'name' ? 'bg-[var(--cc-surface)] text-indigo-400 shadow-sm border border-[var(--cc-border)] font-bold' : 'text-[var(--cc-text-muted)]' }}">
                        Nama
                        @if($sortPending === 'name')
                            <span class="material-symbols-outlined text-[14px]">{{ $direction === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                        @endif
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['sort_pending' => 'date', 'direction' => ($sortPending === 'date' && $direction === 'asc') ? 'desc' : 'asc', 'status' => 'approval_pending']) }}" class="px-3 py-1.5 rounded-lg font-semibold hover:bg-black/5 dark:hover:bg-white/5 transition flex items-center gap-1 {{ $sortPending === 'date' ? 'bg-[var(--cc-surface)] text-indigo-400 shadow-sm border border-[var(--cc-border)] font-bold' : 'text-[var(--cc-text-muted)]' }}">
                        Tanggal
                        @if($sortPending === 'date')
                            <span class="material-symbols-outlined text-[14px]">{{ $direction === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                        @endif
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['sort_pending' => 'client', 'direction' => ($sortPending === 'client' && $direction === 'asc') ? 'desc' : 'asc', 'status' => 'approval_pending']) }}" class="px-3 py-1.5 rounded-lg font-semibold hover:bg-black/5 dark:hover:bg-white/5 transition flex items-center gap-1 {{ $sortPending === 'client' ? 'bg-[var(--cc-surface)] text-indigo-400 shadow-sm border border-[var(--cc-border)] font-bold' : 'text-[var(--cc-text-muted)]' }}">
                        Client
                        @if($sortPending === 'client')
                            <span class="material-symbols-outlined text-[14px]">{{ $direction === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                        @endif
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-[var(--cc-border)]">
                <table class="w-full text-sm">
                    <thead class="bg-[var(--cc-bg-muted)] border-b border-[var(--cc-border)]">
                        <tr class="text-[var(--cc-text-muted)] text-left">
                            <th class="px-6 py-4 font-semibold">Nama Opportunity</th>
                            <th class="px-6 py-4 font-semibold">Client / Perusahaan</th>
                            <th class="px-6 py-4 font-semibold">Tanggal</th>
                            <th class="px-6 py-4 font-semibold">Kebutuhan Alokasi</th>
                            <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--cc-border)]">
                        @forelse($pendingAssignments as $opp)
                        <tr class="hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-[var(--cc-text)] block">{{ $opp->title }}</span>
                                <span class="text-xs font-mono text-[var(--cc-text-muted)]">{{ $opp->opp_number }}</span>
                            </td>
                            <td class="px-6 py-4 text-[var(--cc-text)]">
                                {{ $opp->client->company_name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-[var(--cc-text-muted)]">
                                @if($opp->actual_close_date)
                                    {{ $opp->actual_close_date->format('d M Y') }}
                                @elseif($opp->expected_close_date)
                                    {{ $opp->expected_close_date->format('d M Y') }}
                                @else
                                    {{ $opp->created_at->format('d M Y') }}
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-1.5 text-xs text-[var(--cc-text-muted)]">
                                        <span>Total Butuh:</span> <strong class="text-[var(--cc-text)]">{{ $opp->required_fleets }} unit</strong>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-xs text-[var(--cc-text-muted)]">
                                        <span>Kendaraan:</span> <span class="{{ $opp->missing_fleets > 0 ? 'text-amber-500 font-semibold' : 'text-emerald-500 font-semibold' }}">{{ $opp->assignedVehicles->count() }} / {{ $opp->required_fleets }} Assigned (Kurang {{ $opp->missing_fleets }})</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-xs text-[var(--cc-text-muted)]">
                                        <span>Supir:</span> <span class="{{ $opp->missing_drivers > 0 ? 'text-amber-500 font-semibold' : 'text-emerald-500 font-semibold' }}">{{ $opp->assignedDrivers->count() }} / {{ $opp->required_fleets }} Assigned (Kurang {{ $opp->missing_drivers }})</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button @click="openAssignModal({{ $opp->id }})" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-gray-900 shadow-md hover:bg-indigo-500 transition cursor-pointer">
                                    <span class="material-symbols-outlined text-[16px]">link</span> Assign Now
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-[var(--cc-text-muted)]">
                                <span class="material-symbols-outlined text-[48px] text-slate-500 mb-2">check_circle</span>
                                <p class="font-bold text-base text-[var(--cc-text)]">Semua Alokasi Selesai</p>
                                <p class="text-sm">Tidak ada opportunity yang menunggu alokasi kendaraan/supir.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        {{-- Control Filters Panel --}}
        <form id="filter-form" method="GET" action="{{ route('fleet.index') }}" class="flex flex-col md:flex-row gap-4 bg-[var(--cc-surface)] border border-[var(--cc-border)] rounded-2xl p-4 backdrop-blur-md">
        <div class="flex-1 relative">
            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-[var(--cc-text-muted)]" style="font-size: 16px;">search</span>
            <input
                type="text"
                name="search"
                placeholder="Search by plate number, car model, details..."
                value="{{ request('search') }}"
                class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl py-2 pl-10 pr-4 text-sm text-[var(--cc-text)] placeholder-[var(--cc-text-muted)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"
            />
            {{-- Invisible submit button to allow Enter to search --}}
            <button type="submit" class="hidden"></button>
        </div>
        
        <div class="flex flex-wrap gap-3">
            <select
                name="location"
                onchange="this.form.submit()"
                class="bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl px-3 py-2 text-sm text-[var(--cc-text-muted)] focus:outline-none focus:border-indigo-500"
            >
                <option value="All" {{ request('location') === 'All' ? 'selected' : '' }}>All Locations</option>
                <option value="Jakarta" {{ request('location') === 'Jakarta' ? 'selected' : '' }}>Jakarta Pool</option>
                <option value="Surabaya" {{ request('location') === 'Surabaya' ? 'selected' : '' }}>Surabaya Pool</option>
            </select>

            <select
                name="status"
                onchange="this.form.submit()"
                class="bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl px-3 py-2 text-sm text-[var(--cc-text-muted)] focus:outline-none focus:border-indigo-500"
            >
                <option value="All" {{ request('status') === 'All' ? 'selected' : '' }}>All Statuses</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available Only</option>
                <option value="rent_out" {{ request('status') === 'rent_out' ? 'selected' : '' }}>Rent Out Only</option>
                <option value="booked" {{ request('status') === 'booked' ? 'selected' : '' }}>Booked Only</option>
                <option value="hold" {{ request('status') === 'hold' ? 'selected' : '' }}>Hold Only</option>
                <optgroup label="Maintenance">
                    <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance (All)</option>
                    <option value="Being Serviced" {{ request('status') === 'Being Serviced' ? 'selected' : '' }}>↳ Being Serviced</option>
                    <option value="In Queue" {{ request('status') === 'In Queue' ? 'selected' : '' }}>↳ In Queue</option>
                </optgroup>
            </select>
        </div>
    </form>

    {{-- Vehicles Grid --}}
    @if($vehicles->isEmpty())
        <div class="text-center py-16 bg-[var(--cc-surface)] border border-[var(--cc-border)] rounded-3xl backdrop-blur-md">
            <span class="material-symbols-outlined mx-auto text-slate-500 mb-3" style="font-size: 48px;">directions_car</span>
            <h3 class="text-lg font-bold text-[var(--cc-text)] mb-1">No Vehicles Found</h3>
            <p class="text-sm text-[var(--cc-text-muted)]">Try adjusting your filters or search criteria.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($vehicles as $u)
            <div class="group relative rounded-3xl border border-[var(--cc-border)] bg-[var(--cc-surface)] p-6 backdrop-blur-lg fleet-card flex flex-col justify-between">
                <div>
                    {{-- Top row: plate status & location --}}
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex flex-col items-center border border-slate-700 bg-slate-900 text-slate-100 font-mono px-3 py-1 rounded shadow-md select-none shrink-0 border-t-2 border-t-indigo-500">
                            <span class="text-base font-bold tracking-widest">{{ $u->plate_number }}</span>
                            <div class="w-full h-px bg-slate-800 my-0.5"></div>
                            <span class="text-[8px] tracking-widest text-slate-400">06.31</span>
                        </div>

                        <div class="flex flex-col items-end gap-1.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black border uppercase tracking-wider {{ $statusColors[$u->status] ?? $statusColors['available'] }}">
                                {{ str_replace('_', ' ', $u->status) }}
                            </span>
                            
                            @if($u->status === 'maintenance')
                                @php
                                    $mStatus = str_contains($u->notes ?? '', 'Servicing') ? 'Being Serviced' : 'In Queue';
                                    $mClass = $mStatus === 'Being Serviced' ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' : 'bg-orange-500/10 text-orange-400 border-orange-500/20';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold border {{ $mClass }}">
                                    {{ $mStatus }}
                                </span>
                            @endif

                            <span class="inline-flex items-center gap-1 text-xs text-[var(--cc-text-muted)] font-medium">
                                <span class="material-symbols-outlined text-[12px] text-red-400">location_on</span>
                                {{ $u->pool?->name ?? 'Unknown' }}
                            </span>
                        </div>
                    </div>

                    {{-- Car info --}}
                    <div class="mb-4">
                        <h3 class="font-bold text-[var(--cc-text)] text-lg tracking-tight group-hover:text-indigo-400 transition-colors">
                            <a href="{{ route('fleet.show', $u->id) }}" class="hover:underline">
                                {{ $u->brand }} {{ $u->model }}
                            </a>
                        </h3>
                        <div class="inline-flex items-center gap-1.5 mt-1 bg-[var(--cc-bg-muted)] px-2 py-0.5 rounded text-[10px] text-[var(--cc-text-muted)] uppercase font-black tracking-wider">
                            <span class="material-symbols-outlined text-[12px] text-indigo-400">sell</span>
                            Mobil Long Term
                        </div>
                    </div>

                    {{-- Relational Linked Contract --}}
                    @if(!in_array($u->status, ['available', 'maintenance', 'inactive']))
                        @if($u->assignedOpportunity)
                            <div class="mt-4 p-3.5 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-xs">
                                <span class="block text-[10px] uppercase font-bold tracking-wider text-indigo-400 mb-1">Assigned Client & Contract</span>
                                <div class="font-bold text-[var(--cc-text)] mb-0.5">{{ $u->assignedOpportunity->client->company_name ?? 'Unknown Company' }}</div>
                                <div class="text-[var(--cc-text-muted)] flex items-center gap-1.5 mt-1">
                                    <span class="truncate">{{ $u->assignedOpportunity->title }}</span>
                                    <span class="text-[10px] bg-indigo-500/20 px-1.5 py-0.5 rounded text-gray-900 shrink-0">{{ ucfirst($u->assignedOpportunity->stage) }}</span>
                                </div>
                            </div>
                        @else
                            <div class="mt-4 p-3.5 rounded-2xl bg-[var(--cc-surface)] border border-dashed border-[var(--cc-border)] text-xs text-[var(--cc-text-muted)] italic">
                                No active customer contract assigned.
                            </div>
                        @endif
                    @endif

                    {{-- Details Grid --}}
                    @if($u->year_manufactured || $u->color || $u->transmission || $u->current_km !== null)
                        <div class="mt-4 grid grid-cols-2 gap-2 text-xs text-[var(--cc-text-muted)] bg-[var(--cc-bg-muted)] p-3 rounded-2xl border border-[var(--cc-border)]">
                            @if($u->year_manufactured) <div><span class="font-bold text-slate-500">Year:</span> {{ $u->year_manufactured }}</div> @endif
                            @if($u->color) <div><span class="font-bold text-slate-500">Color:</span> {{ $u->color }}</div> @endif
                            @if($u->transmission) <div><span class="font-bold text-slate-500">Transmission:</span> {{ $u->transmission }}</div> @endif
                            @if($u->current_km !== null) <div><span class="font-bold text-slate-500">Odo:</span> {{ number_format($u->current_km) }} km</div> @endif
                        </div>
                    @endif

                    {{-- Operational Logs --}}
                    @if($u->notes)
                        <div class="mt-2 bg-[var(--cc-bg-muted)] p-3 rounded-2xl text-xs text-[var(--cc-text-muted)] border border-[var(--cc-border)]">
                            <span class="font-bold text-[var(--cc-text)] block mb-1">Operational Logs:</span>
                            {{ $u->notes }}
                        </div>
                    @endif

                    <div class="mt-3 grid grid-cols-2 gap-2">
                        @if($u->pajak_expiry)
                            <div class="text-[10px] text-slate-500 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[12px] text-indigo-400/70">description</span>
                                Tax Exp: {{ $u->pajak_expiry->format('Y-m-d') }}
                            </div>
                        @endif
                        @if($u->stnk_expiry)
                            <div class="text-[10px] text-slate-500 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[12px] text-indigo-400/70">description</span>
                                STNK Exp: {{ $u->stnk_expiry->format('Y-m-d') }}
                            </div>
                        @endif
                        @if($u->insurance_expiry)
                            <div class="text-[10px] text-slate-500 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[12px] text-indigo-400/70">description</span>
                                Ins Exp: {{ $u->insurance_expiry->format('Y-m-d') }}
                            </div>
                        @endif
                    </div>

                    @if(auth()->user()->isOperational() || auth()->user()->isGM() || auth()->user()->isManager())
                        @php
                            $today = now()->startOfDay();
                            $isStnkNear = $u->stnk_expiry && $today->diffInDays($u->stnk_expiry, false) < 30;
                            $isPajakNear = $u->pajak_expiry && $today->diffInDays($u->pajak_expiry, false) < 30;
                            $isInsuranceNear = $u->insurance_expiry && $today->diffInDays($u->insurance_expiry, false) < 30;
                        @endphp
                        @if($isStnkNear || $isPajakNear || $isInsuranceNear)
                            <div class="mt-3 p-2.5 rounded-xl bg-red-500/10 border border-red-500/20 text-[11px] text-red-400 space-y-1">
                                <div class="font-bold uppercase tracking-wider flex items-center gap-1 mb-1">
                                    <span class="material-symbols-outlined text-[14px]">warning</span> Expiry Warning
                                </div>
                                @if($isStnkNear)
                                    @php $days = $today->diffInDays($u->stnk_expiry, false); @endphp
                                    <div>• STNK: {{ $days < 0 ? 'Expired' : ($days == 0 ? 'Expires today' : "$days days left") }} ({{ $u->stnk_expiry->format('Y-m-d') }})</div>
                                @endif
                                @if($isPajakNear)
                                    @php $days = $today->diffInDays($u->pajak_expiry, false); @endphp
                                    <div>• Tax: {{ $days < 0 ? 'Expired' : ($days == 0 ? 'Expires today' : "$days days left") }} ({{ $u->pajak_expiry->format('Y-m-d') }})</div>
                                @endif
                                @if($isInsuranceNear)
                                    @php $days = $today->diffInDays($u->insurance_expiry, false); @endphp
                                    <div>• Insurance: {{ $days < 0 ? 'Expired' : ($days == 0 ? 'Expires today' : "$days days left") }} ({{ $u->insurance_expiry->format('Y-m-d') }})</div>
                                @endif
                            </div>
                        @endif
                    @endif

                    @php
                        // Fetch latest maintenance log if any (Requires relationship to be loaded or just placeholder if not)
                        $lastService = null;
                        if ($u->relationLoaded('maintenanceLogs') && $u->maintenanceLogs->isNotEmpty()) {
                            $lastService = $u->maintenanceLogs->first()->scheduled_date;
                        }
                    @endphp
                    @if($lastService)
                        <div class="mt-2 text-[11px] text-slate-500 font-medium flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[14px] text-amber-500/70">build</span>
                            Last serviced: {{ \Carbon\Carbon::parse($lastService)->format('Y-m-d') }}
                        </div>
                    @endif
                </div>

                {{-- Bottom Actions --}}
                <div class="mt-6 pt-4 border-t border-[var(--cc-border)] flex gap-2">
                    @if($canModify)
                    <button class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl border border-indigo-500/20 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 py-2 text-xs font-semibold transition-all">
                        <span class="material-symbols-outlined text-[14px]">build</span>
                        Status
                    </button>
                    @endif
                    <a href="{{ route('fleet.show', $u->id) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] hover:bg-[var(--cc-bg-muted)] py-2 text-xs font-semibold text-[var(--cc-text)] text-center transition-all">
                        <span class="material-symbols-outlined text-[14px] text-[var(--cc-text-muted)]">visibility</span>
                        Detail
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @endif

    {{-- Create Vehicle Modal --}}
    <div x-show="showCreateModal" style="display: none;" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="showCreateModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="showCreateModal"
                     @click.away="showCreateModal = false"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-2xl bg-[var(--cc-surface)] border border-[var(--cc-border)] text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl">
                    
                    <form action="{{ route('fleet.store') }}" method="POST">
                        @csrf
                        <div class="px-6 py-5 border-b border-[var(--cc-border)] flex items-center justify-between">
                            <h3 class="text-xl font-bold text-[var(--cc-text)]" id="modal-title">Register Vehicle (Mobil Long Term)</h3>
                            <button type="button" @click="showCreateModal = false" class="text-[var(--cc-text-muted)] hover:text-[var(--cc-text)] transition">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>
                        <div class="px-6 py-5 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-[var(--cc-text-muted)] mb-1">Police Number <span class="text-rose-500">*</span></label>
                                    <input type="text" name="police_number" required placeholder="B 1234 XYZ"
                                        class="w-full rounded-xl bg-[var(--cc-bg)] border-[var(--cc-border)] text-[var(--cc-text)] focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 px-4 py-2.5">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-[var(--cc-text-muted)] mb-1">Brand/Model <span class="text-rose-500">*</span></label>
                                    <input type="text" name="brand_model" required placeholder="Toyota Avanza"
                                        class="w-full rounded-xl bg-[var(--cc-bg)] border-[var(--cc-border)] text-[var(--cc-text)] focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 px-4 py-2.5">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-[var(--cc-text-muted)] mb-1">Vehicle Type</label>
                                    <input type="text" name="vehicle_type" placeholder="MPV"
                                        class="w-full rounded-xl bg-[var(--cc-bg)] border-[var(--cc-border)] text-[var(--cc-text)] focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 px-4 py-2.5">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-[var(--cc-text-muted)] mb-1">Year</label>
                                    <input type="number" name="year" placeholder="2022"
                                        class="w-full rounded-xl bg-[var(--cc-bg)] border-[var(--cc-border)] text-[var(--cc-text)] focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 px-4 py-2.5">
                                </div>
                            </div>
                            
                            <hr class="border-[var(--cc-border)] my-2">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-[var(--cc-text-muted)] mb-1">STNK Expiry Date</label>
                                    <input type="date" name="stnk_expiry"
                                        class="w-full rounded-xl bg-[var(--cc-bg)] border-[var(--cc-border)] text-[var(--cc-text)] focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 px-4 py-2.5">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-[var(--cc-text-muted)] mb-1">Tax (Pajak) Expiry Date</label>
                                    <input type="date" name="pajak_expiry"
                                        class="w-full rounded-xl bg-[var(--cc-bg)] border-[var(--cc-border)] text-[var(--cc-text)] focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 px-4 py-2.5">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-[var(--cc-text-muted)] mb-1">KIR Expiry Date</label>
                                    <input type="date" name="kir_expiry"
                                        class="w-full rounded-xl bg-[var(--cc-bg)] border-[var(--cc-border)] text-[var(--cc-text)] focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 px-4 py-2.5">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-[var(--cc-text-muted)] mb-1">Insurance Expiry Date</label>
                                    <input type="date" name="insurance_expiry"
                                        class="w-full rounded-xl bg-[var(--cc-bg)] border-[var(--cc-border)] text-[var(--cc-text)] focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 px-4 py-2.5">
                                </div>
                            </div>

                            <hr class="border-[var(--cc-border)] my-2">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-[var(--cc-text-muted)] mb-1">Pool Assignment (Optional)</label>
                                    <select name="pool_id" class="w-full rounded-xl bg-[var(--cc-bg)] border-[var(--cc-border)] text-[var(--cc-text)] focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 px-4 py-2.5">
                                        <option value="">-- No Pool Assignment --</option>
                                        @php
                                            $pools = \App\Models\Pool::orderBy('name')->get();
                                        @endphp
                                        @foreach($pools as $pool)
                                            <option value="{{ $pool->id }}">{{ $pool->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-[var(--cc-text-muted)] mb-1">Initial Status</label>
                                    <select name="status" class="w-full rounded-xl bg-[var(--cc-bg)] border-[var(--cc-border)] text-[var(--cc-text)] focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 px-4 py-2.5">
                                        <option value="available">Available</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-[var(--cc-bg-muted)] border-t border-[var(--cc-border)] flex items-center justify-end gap-3 rounded-b-2xl">
                            <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-sm font-medium text-[var(--cc-text-muted)] hover:text-[var(--cc-text)] transition">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-gray-900 text-sm font-semibold rounded-xl shadow transition">
                                Register Vehicle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
    </div>
    @endif

    {{-- Assign Vehicle Modal --}}
    <template x-if="showAssignModal">
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm px-4"
             x-transition.opacity>
            <div class="bg-[var(--cc-surface)] border border-[var(--cc-border)] rounded-3xl w-full max-w-lg shadow-2xl flex flex-col max-h-[90vh] overflow-hidden"
                 @click.outside="showAssignModal = false">
                
                <div class="p-6 border-b border-[var(--cc-border)] flex items-center justify-between bg-[var(--cc-bg-muted)]">
                    <h3 class="text-lg font-bold text-[var(--cc-text)]">Alokasi Kendaraan & Supir</h3>
                    <button @click="showAssignModal = false" class="text-[var(--cc-text-muted)] hover:text-rose-500 transition">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto flex-1 custom-scrollbar space-y-6">
                    <!-- Vehicles Allocation -->
                    <template x-if="assigningOpp?.missing_fleets > 0">
                        <div>
                            <div class="mb-2.5 text-sm font-semibold text-[var(--cc-text)] flex justify-between">
                                <span>Pilih Kendaraan</span>
                                <span class="text-xs text-[var(--cc-text-muted)]">Butuh <strong class="text-amber-500" x-text="assigningOpp?.missing_fleets"></strong> unit</span>
                            </div>
                            <div class="space-y-2 max-h-[200px] overflow-y-auto border border-[var(--cc-border)] p-3 rounded-2xl bg-[var(--cc-bg-muted)]/55 custom-scrollbar">
                                <template x-if="availableFleets.length === 0">
                                    <div class="text-center py-4 text-[var(--cc-text-muted)] text-xs">
                                        Tidak ada kendaraan yang tersedia.
                                    </div>
                                </template>
                                <template x-for="fleet in availableFleets" :key="fleet.id">
                                    <label class="flex items-center gap-3 p-2.5 rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] hover:border-indigo-500 cursor-pointer transition text-xs"
                                           :class="selectedFleets.length >= assigningOpp?.missing_fleets && !selectedFleets.includes(fleet.id) ? 'opacity-50 cursor-not-allowed' : ''">
                                        <input type="checkbox" :value="fleet.id" x-model="selectedFleets"
                                               :disabled="selectedFleets.length >= assigningOpp?.missing_fleets && !selectedFleets.includes(fleet.id)"
                                               class="rounded text-indigo-500 focus:ring-indigo-500 bg-[var(--cc-bg)] border-[var(--cc-border)]">
                                        <div>
                                            <div class="font-bold text-[var(--cc-text)]" x-text="fleet.plate_number"></div>
                                            <div class="text-[9px] text-[var(--cc-text-muted)]" x-text="fleet.model + ' (' + fleet.year + ')'"></div>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Drivers Allocation -->
                    <template x-if="assigningOpp?.missing_drivers > 0">
                        <div>
                            <div class="mb-2.5 text-sm font-semibold text-[var(--cc-text)] flex justify-between">
                                <span>Pilih Supir / Driver</span>
                                <span class="text-xs text-[var(--cc-text-muted)]">Butuh <strong class="text-amber-500" x-text="assigningOpp?.missing_drivers"></strong> orang</span>
                            </div>
                            <div class="space-y-2 max-h-[200px] overflow-y-auto border border-[var(--cc-border)] p-3 rounded-2xl bg-[var(--cc-bg-muted)]/55 custom-scrollbar">
                                <template x-if="availableDrivers.length === 0">
                                    <div class="text-center py-4 text-[var(--cc-text-muted)] text-xs">
                                        Tidak ada supir yang tersedia.
                                    </div>
                                </template>
                                <template x-for="driver in availableDrivers" :key="driver.id">
                                    <label class="flex items-center gap-3 p-2.5 rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] hover:border-indigo-500 cursor-pointer transition text-xs"
                                           :class="selectedDrivers.length >= assigningOpp?.missing_drivers && !selectedDrivers.includes(driver.id) ? 'opacity-50 cursor-not-allowed' : ''">
                                        <input type="checkbox" :value="driver.id" x-model="selectedDrivers"
                                               :disabled="selectedDrivers.length >= assigningOpp?.missing_drivers && !selectedDrivers.includes(driver.id)"
                                               class="rounded text-indigo-500 focus:ring-indigo-500 bg-[var(--cc-bg)] border-[var(--cc-border)]">
                                        <div>
                                            <div class="font-bold text-[var(--cc-text)]" x-text="driver.name"></div>
                                            <div class="text-[9px] text-[var(--cc-text-muted)]" x-text="driver.pool ? driver.pool.name : ''"></div>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="p-6 border-t border-[var(--cc-border)] bg-[var(--cc-bg-muted)] flex justify-end gap-3">
                    <button @click="showAssignModal = false" class="px-5 py-2.5 rounded-xl border border-[var(--cc-border)] text-[var(--cc-text-muted)] hover:text-[var(--cc-text)] hover:bg-[var(--cc-bg)] text-sm font-semibold transition">
                        Batal
                    </button>
                    <button @click="saveAssignment()" :disabled="isAssigning || (selectedFleets.length === 0 && selectedDrivers.length === 0)" 
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-500 text-sm font-semibold transition disabled:opacity-50 flex items-center gap-2">
                        <span x-show="isAssigning" class="material-symbols-outlined animate-spin" style="font-size: 18px">progress_activity</span>
                        <span x-text="isAssigning ? 'Menyimpan...' : 'Simpan Alokasi'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
@push('scripts')
<script>
    window.fleetPage = function() {
        return {
            showMaintenanceDetails: false,
            showCreateModal: false,
            showAssignModal: false,
            assigningOpp: null,
            availableFleets: [],
            selectedFleets: [],
            availableDrivers: [],
            selectedDrivers: [],
            isAssigning: false,
            pendingOpps: [],

            init() {
                const pendingOppsElement = document.getElementById('pending-assignments-data');
                this.pendingOpps = pendingOppsElement ? JSON.parse(pendingOppsElement.textContent) : [];

                const urlParams = new URLSearchParams(window.location.search);
                const assignOppId = urlParams.get('assign_opp');
                if (assignOppId) {
                    this.openAssignModal(assignOppId);
                }
            },

            openAssignModal(oppId) {
                const opp = this.pendingOpps.find(o => String(o.id) === String(oppId));
                if (!opp) {
                    console.error('Opportunity not found:', oppId);
                    return;
                }
                this.assigningOpp = opp;
                this.selectedFleets = [];
                this.selectedDrivers = [];
                this.showAssignModal = true;
                
                this.loadAvailableData(opp.id);
            },

            async loadAvailableData(oppId) {
                try {
                    const res = await fetch(`/api/vehicles/available?opportunity_id=${oppId}`);
                    this.availableFleets = await res.json();
                } catch(e) {
                    console.error('Failed to load vehicles', e);
                }
                try {
                    const res = await fetch(`/api/drivers/available?opportunity_id=${oppId}`);
                    this.availableDrivers = await res.json();
                } catch(e) {
                    console.error('Failed to load drivers', e);
                }
            },

            async saveAssignment() {
                if (this.selectedFleets.length === 0 && this.selectedDrivers.length === 0) {
                    alert('Silakan pilih minimal 1 kendaraan atau supir.');
                    return;
                }
                this.isAssigning = true;
                try {
                    const res = await fetch(`/api/vehicles/assign-to-opportunity/${this.assigningOpp.id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            vehicle_ids: this.selectedFleets,
                            driver_ids: this.selectedDrivers
                        })
                    });
                    if (res.ok) {
                        window.location.reload();
                    } else {
                        alert('Gagal menyimpan alokasi.');
                    }
                } catch(e) {
                    console.error(e);
                } finally {
                    this.isAssigning = false;
                }
            }
        };
    };
</script>
@endpush
@endsection

