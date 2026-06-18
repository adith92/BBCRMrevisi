@extends('layouts.app')

@section('header_title', 'Operational Supir')

@push('styles')
<style>
    .driver-card {
        transition: all 0.2s ease-in-out;
    }
    .driver-card:hover {
        border-color: rgba(99, 102, 241, 0.2);
        background-color: rgba(255, 255, 255, 0.1);
    }
</style>
@endpush

@section('content')
@php
    $canModify = auth()->user()->isOperational()
        || auth()->user()->isPool()
        || auth()->user()->isManager();
    $canAssign = auth()->user()->isOperational() || auth()->user()->isPool();
    $pendingDriverCount = isset($pendingAssignments) ? $pendingAssignments->where('missing_drivers', '>', 0)->count() : 0;
    $statusColors = [
        'available' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        'assigned'  => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        'reserved'  => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
        'inactive'  => 'bg-rose-500/10 text-rose-400 border-rose-500/20', // leave
    ];
@endphp

<script type="application/json" id="pending-driver-assignments-data">
    @json(isset($pendingAssignments) ? $pendingAssignments : [])
</script>

<div class="space-y-6 pb-20" x-data="driverPage">
    
    {{-- Header Panel --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-[var(--cc-text)] mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined h-8 w-8 text-indigo-400" style="font-size: 32px">person</span>
                Operational Supir
            </h1>
            <p class="text-[var(--cc-text-muted)] max-w-2xl text-sm">
                Operational dashboard to register, allocate, and monitor driver (Supir) fleets.
            </p>
        </div>
        
        @if($canModify)
        <div class="flex items-center gap-3">
            <button @click="showCreateModal = true" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-lg shadow-indigo-600/30 hover:bg-indigo-500 transition-all">
                <span class="material-symbols-outlined text-[16px]">add</span>
                Register Driver
            </button>
        </div>
        @endif
    </div>

    {{-- Pending Driver Assignments --}}
    @if(isset($pendingAssignments) && $pendingAssignments->count() > 0 && $canAssign)
    <div class="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-6">
        <h2 class="text-amber-500 font-bold mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined">warning</span>
            Driver Assignment: Supir Long Term ({{ $pendingDriverCount }} pending / {{ $pendingAssignments->count() }} total)
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
                    <div class="text-sm bg-amber-500/5 p-2 rounded border border-amber-500/10 mb-4 space-y-1">
                        <div class="flex justify-between"><span>Supir Required:</span> <strong>{{ $opp->required_drivers }}</strong></div>
                        <div class="flex justify-between"><span>Supir Assigned:</span> <strong>{{ $opp->assignedDrivers->count() }}</strong></div>
                        <div class="flex justify-between {{ $opp->missing_drivers > 0 ? 'text-amber-500' : 'text-emerald-500' }} font-bold">
                            <span>Status:</span>
                            <span>{{ $opp->missing_drivers > 0 ? $opp->missing_drivers . ' Person Missing' : 'Fulfilled' }}</span>
                        </div>
                    </div>
                </div>
                <button type="button" @click.stop.prevent="openAssignModal({{ $opp->id }})" class="w-full py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-bold transition">
                    {{ $opp->missing_drivers > 0 ? 'Assign Driver' : 'Ubah Alokasi Supir' }}
                </button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Stats Grid --}}
    @php
        $currentStatus = request('status', 'All');
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
        <a href="{{ request()->fullUrlWithQuery(['status' => 'All']) }}" 
           class="block rounded-2xl border bg-[var(--cc-surface)] p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'All' ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-[var(--cc-border)] hover:border-indigo-500/40' }}">
            <div class="text-xs font-bold text-[var(--cc-text-muted)] uppercase tracking-wider">Total Supir</div>
            <div class="text-3xl font-mono font-bold text-[var(--cc-text)] mt-1">{{ $stats['total'] }}</div>
            <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">Registered drivers</div>
        </a>
        
        <a href="{{ request()->fullUrlWithQuery(['status' => 'available']) }}" 
           class="block rounded-2xl border bg-emerald-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'available' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-emerald-500/20 hover:border-emerald-500/50' }}">
            <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Available</div>
            <div class="text-3xl font-mono font-bold text-emerald-400 mt-1">{{ $stats['available'] }}</div>
            <div class="text-[10px] text-emerald-500 mt-1">Ready for assignment</div>
        </a>

        <a href="{{ request()->fullUrlWithQuery(['status' => 'approval_pending']) }}"
           class="block rounded-2xl border bg-indigo-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'approval_pending' ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-indigo-500/20 hover:border-indigo-500/50' }}">
            <div class="text-xs font-bold text-indigo-400 uppercase tracking-wider">Assign Pending</div>
            <div class="text-3xl font-mono font-bold text-indigo-400 mt-1">{{ $pendingDriverCount }}</div>
            <div class="text-[10px] text-indigo-500 mt-1">Pending Driver Assignments</div>
        </a>
        
        <a href="{{ request()->fullUrlWithQuery(['status' => 'assigned']) }}" 
           class="block rounded-2xl border bg-blue-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'assigned' ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-blue-500/20 hover:border-blue-500/50' }}">
            <div class="text-xs font-bold text-blue-400 uppercase tracking-wider">Assigned</div>
            <div class="text-3xl font-mono font-bold text-blue-400 mt-1">{{ $stats['assigned'] }}</div>
            <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">On active duty</div>
        </a>
        
        <a href="{{ request()->fullUrlWithQuery(['status' => 'reserved']) }}" 
           class="block rounded-2xl border bg-purple-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'reserved' ? 'border-purple-500 ring-2 ring-purple-500/20' : 'border-purple-500/20 hover:border-purple-500/50' }}">
            <div class="text-xs font-bold text-purple-400 uppercase tracking-wider">Reserved</div>
            <div class="text-3xl font-mono font-bold text-purple-400 mt-1">{{ $stats['reserved'] }}</div>
            <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">Booked for contract</div>
        </a>
        
        <a href="{{ request()->fullUrlWithQuery(['status' => 'inactive']) }}" 
           class="block rounded-2xl border bg-rose-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'inactive' ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-rose-500/20 hover:border-rose-500/50' }}">
            <div class="text-xs font-bold text-rose-400 uppercase tracking-wider">Leave</div>
            <div class="text-3xl font-mono font-bold text-rose-400 mt-1">{{ $stats['leave'] }}</div>
            <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">Not available</div>
        </a>
    </div>

    @if(isset($driverStatusSummary) && $driverStatusSummary->count() > 0)
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="cc-card rounded-2xl p-5">
            <div class="flex items-start justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-bold text-[var(--cc-text)]">Driver Status Distribution</h2>
                    <p class="text-xs text-[var(--cc-text-muted)]">Ringkasan supir available, assigned, reserved, dan leave.</p>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                    {{ $stats['total'] }} person
                </span>
            </div>
            <div class="h-72">
                <canvas id="driver-status-chart"></canvas>
            </div>
        </div>

        <div class="cc-card rounded-2xl p-5">
            <div class="flex items-start justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-bold text-[var(--cc-text)]">Driver by Pool</h2>
                    <p class="text-xs text-[var(--cc-text-muted)]">Distribusi supir per pool agar penempatan lebih mudah dibaca.</p>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    Top pools
                </span>
            </div>
            <div class="h-72">
                <canvas id="driver-pool-chart"></canvas>
            </div>
        </div>
    </div>
    @endif

    {{-- Control Filters Panel --}}
    <form id="driver-filter-form" method="GET" action="{{ route('drivers.index') }}" class="flex flex-col md:flex-row gap-4 bg-[var(--cc-surface)] border border-[var(--cc-border)] rounded-2xl p-4 backdrop-blur-md">
        <div class="flex-1 relative">
            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-[var(--cc-text-muted)]" style="font-size: 16px;">search</span>
            <input
                type="text"
                name="search"
                placeholder="Search by name, phone..."
                value="{{ request('search') }}"
                class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl py-2 pl-10 pr-4 text-sm text-[var(--cc-text)] placeholder-[var(--cc-text-muted)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"
            />
            <button type="submit" class="hidden"></button>
        </div>
        
        <div class="flex flex-wrap gap-3">
            @if(!auth()->user()->isPool())
            <select
                name="location"
                onchange="this.form.submit()"
                class="bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl px-3 py-2 text-sm text-[var(--cc-text-muted)] focus:outline-none focus:border-indigo-500"
            >
                <option value="All" {{ request('location') === 'All' ? 'selected' : '' }}>All Locations</option>
                <option value="Jakarta" {{ request('location') === 'Jakarta' ? 'selected' : '' }}>Jakarta Pool</option>
                <option value="Surabaya" {{ request('location') === 'Surabaya' ? 'selected' : '' }}>Surabaya Pool</option>
            </select>
            @endif

            <select
                name="status"
                onchange="this.form.submit()"
                class="bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl px-3 py-2 text-sm text-[var(--cc-text-muted)] focus:outline-none focus:border-indigo-500"
            >
                <option value="All" {{ request('status') === 'All' ? 'selected' : '' }}>All Statuses</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available Only</option>
                <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned Only</option>
                <option value="reserved" {{ request('status') === 'reserved' ? 'selected' : '' }}>Reserved Only</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Leave Only</option>
            </select>
        </div>
    </form>

    {{-- Drivers Grid --}}
    @if($drivers->isEmpty())
        <div class="text-center py-16 bg-[var(--cc-surface)] border border-[var(--cc-border)] rounded-3xl backdrop-blur-md">
            <span class="material-symbols-outlined mx-auto text-slate-500 mb-3" style="font-size: 48px;">person_off</span>
            <h3 class="text-lg font-bold text-[var(--cc-text)] mb-1">No Drivers Found</h3>
            <p class="text-sm text-[var(--cc-text-muted)]">Try adjusting your filters or search criteria.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($drivers as $d)
            <div class="group relative rounded-3xl border border-[var(--cc-border)] bg-[var(--cc-surface)] p-6 backdrop-blur-lg driver-card flex flex-col justify-between h-full">
                <div>
                    {{-- Top Row: Avatar & Status Badge --}}
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-lg shrink-0 border border-indigo-500/30">
                            {{ strtoupper(substr($d->name, 0, 1)) }}
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black border uppercase tracking-wider {{ $statusColors[$d->status] ?? $statusColors['available'] }}">
                            {{ str_replace('_', ' ', $d->status === 'inactive' ? 'leave' : $d->status) }}
                        </span>
                    </div>
                    
                    {{-- Middle: Driver Details --}}
                    <div class="space-y-1">
                        <h3 class="font-bold text-[var(--cc-text)] text-lg tracking-tight group-hover:text-indigo-400 transition-colors">
                            <a href="{{ route('drivers.show', $d->id) }}" class="hover:underline">
                                {{ $d->name }}
                            </a>
                        </h3>
                        <div class="text-xs text-[var(--cc-text-muted)] flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[14px]">phone</span>
                            <span>{{ $d->phone ?? 'No phone' }}</span>
                        </div>
                    </div>

                    {{-- Bottom: Pool Info Banner --}}
                    <div class="mt-4 flex items-center gap-2 text-xs text-[var(--cc-text-muted)] bg-[var(--cc-bg-muted)] border border-[var(--cc-border)]/50 rounded-xl px-3 py-2">
                        <span class="material-symbols-outlined text-[16px] text-indigo-400">home</span>
                        <span class="font-medium">Pool: <span class="text-[var(--cc-text)] font-semibold">{{ $d->pool?->name ?? '—' }}</span></span>
                    </div>

                    {{-- Relational Linked Contract --}}
                    @if(!in_array($d->status, ['available', 'inactive']))
                        @if($d->assignedOpportunity)
                            <div class="mt-3.5 p-3.5 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-xs">
                                <span class="block text-[10px] uppercase font-bold tracking-wider text-indigo-400 mb-1">Assigned Client</span>
                                <div class="font-bold text-[var(--cc-text)] mb-0.5">{{ $d->assignedOpportunity->client->company_name ?? 'Unknown Company' }}</div>
                                <div class="text-[var(--cc-text-muted)] flex items-center gap-1.5 mt-1">
                                    <span class="truncate">{{ $d->assignedOpportunity->title }}</span>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
                
                {{-- Action Buttons --}}
                <div class="mt-5 flex gap-2">
                    @if($canModify)
                    <button class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl border border-[var(--cc-border)] bg-[var(--cc-bg-muted)] hover:bg-[var(--cc-surface)] py-2 text-xs font-semibold text-[var(--cc-text)] transition-all">
                        <span class="material-symbols-outlined text-[14px]">edit</span>
                        Edit
                    </button>
                    @endif
                    <a href="{{ route('drivers.show', $d->id) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] hover:bg-[var(--cc-bg-muted)] py-2 text-xs font-semibold text-[var(--cc-text)] text-center transition-all">
                        <span class="material-symbols-outlined text-[14px] text-[var(--cc-text-muted)]">visibility</span>
                        Detail
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @endif

    @if($canModify)
    {{-- Create Driver Modal --}}
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
                     class="relative transform overflow-hidden rounded-2xl bg-[var(--cc-surface)] border border-[var(--cc-border)] text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    
                    <form action="{{ route('drivers.store') }}" method="POST">
                        @csrf
                        <div class="px-6 py-5 border-b border-[var(--cc-border)] flex items-center justify-between">
                            <h3 class="text-xl font-bold text-[var(--cc-text)]" id="modal-title">Register Driver</h3>
                            <button type="button" @click="showCreateModal = false" class="text-[var(--cc-text-muted)] hover:text-[var(--cc-text)] transition">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>
                        <div class="px-6 py-5 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-[var(--cc-text-muted)] mb-1">Driver Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" required
                                    class="w-full rounded-xl bg-[var(--cc-bg)] border-[var(--cc-border)] text-[var(--cc-text)] focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 px-4 py-2.5">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[var(--cc-text-muted)] mb-1">Phone Number <span class="text-rose-500">*</span></label>
                                <input type="text" name="phone" required
                                    class="w-full rounded-xl bg-[var(--cc-bg)] border-[var(--cc-border)] text-[var(--cc-text)] focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 px-4 py-2.5">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[var(--cc-text-muted)] mb-1">Pool (Optional)</label>
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
                                    <option value="available">Available (Ready for assignment)</option>
                                    <option value="inactive">Leave (Not available)</option>
                                </select>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-[var(--cc-bg-muted)] border-t border-[var(--cc-border)] flex items-center justify-end gap-3 rounded-b-2xl">
                            <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-sm font-medium text-[var(--cc-text-muted)] hover:text-[var(--cc-text)] transition">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-gray-900 text-sm font-semibold rounded-xl shadow transition">
                                Register Driver
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Assign Driver Modal --}}
    <div x-show="showAssignModal"
         x-cloak
         style="display: none;"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm px-4"
         x-transition.opacity>
        <div class="bg-[var(--cc-surface)] border border-[var(--cc-border)] rounded-3xl w-full max-w-lg shadow-2xl flex flex-col max-h-[90vh] overflow-hidden"
             @click.outside="showAssignModal = false">
            <div class="p-6 border-b border-[var(--cc-border)] flex items-center justify-between bg-[var(--cc-bg-muted)]">
                <h3 class="text-lg font-bold text-[var(--cc-text)]">Alokasi Supir</h3>
                <button @click="showAssignModal = false" class="text-[var(--cc-text-muted)] hover:text-rose-500 transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 custom-scrollbar space-y-6">
                @if(auth()->user()->isPool())
                <div class="p-3 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 rounded-xl text-xs flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">info</span>
                    <span>You are assigning drivers from your pool only.</span>
                </div>
                @endif

                <template x-if="assigningOpp?.required_drivers > 0">
                    <div>
                        <div class="mb-2.5 text-sm font-semibold text-[var(--cc-text)] flex justify-between">
                            <span>Pilih Supir / Driver</span>
                            <span class="text-xs text-[var(--cc-text-muted)]">
                                Pilihan: <strong class="text-indigo-400" x-text="selectedDrivers.length"></strong> / <strong x-text="assigningOpp?.required_drivers"></strong> orang
                            </span>
                        </div>
                        <div class="mb-3 flex flex-wrap gap-2">
                            <button type="button" @click="autoSelectDrivers()"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500/10 px-3 py-1.5 text-xs font-bold text-emerald-400 hover:bg-emerald-500/20 transition">
                                <span class="material-symbols-outlined text-[15px]">bolt</span>
                                Auto Assign
                            </button>
                            <button type="button" @click="selectedDrivers = []"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-[var(--cc-border)] px-3 py-1.5 text-xs font-bold text-[var(--cc-text-muted)] hover:text-[var(--cc-text)] transition">
                                <span class="material-symbols-outlined text-[15px]">backspace</span>
                                Clear
                            </button>
                        </div>
                        <div class="space-y-2 max-h-[260px] overflow-y-auto border border-[var(--cc-border)] p-3 rounded-2xl bg-[var(--cc-bg-muted)]/55 custom-scrollbar">
                            <template x-if="availableDrivers.length === 0">
                                <div class="text-center py-4 text-[var(--cc-text-muted)] text-xs">
                                    Tidak ada supir yang tersedia.
                                </div>
                            </template>
                            <template x-for="driver in availableDrivers" :key="driver.id">
                                <label class="flex items-center gap-3 p-2.5 rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] hover:border-indigo-500 cursor-pointer transition text-xs"
                                       :class="selectedDrivers.length >= assigningOpp?.required_drivers && !selectedDrivers.includes(driver.id) ? 'opacity-50 cursor-not-allowed' : ''">
                                    <input type="checkbox" :value="driver.id" x-model="selectedDrivers"
                                           :disabled="selectedDrivers.length >= assigningOpp?.required_drivers && !selectedDrivers.includes(driver.id)"
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
                <button @click="saveAssignment()" :disabled="isAssigning"
                        class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-500 text-sm font-semibold transition disabled:opacity-50 flex items-center gap-2">
                    <span x-show="isAssigning" class="material-symbols-outlined animate-spin" style="font-size: 18px">progress_activity</span>
                    <span x-text="isAssigning ? 'Menyimpan...' : 'Simpan Alokasi'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const textColor = getComputedStyle(document.documentElement).getPropertyValue('--cc-text-muted').trim() || '#94a3b8';
        const cardColor = getComputedStyle(document.documentElement).getPropertyValue('--cc-card').trim() || '#111827';

        const statusCanvas = document.getElementById('driver-status-chart');
        if (statusCanvas) {
            const statusRows = @json($driverStatusSummary ?? []);
            new Chart(statusCanvas, {
                type: 'doughnut',
                data: {
                    labels: statusRows.map(row => row.label),
                    datasets: [{
                        data: statusRows.map(row => row.count),
                        backgroundColor: ['#10b981', '#3b82f6', '#8b5cf6', '#ef4444'],
                        borderColor: cardColor,
                        borderWidth: 3,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '64%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: textColor }
                        }
                    }
                }
            });
        }

        const poolCanvas = document.getElementById('driver-pool-chart');
        if (poolCanvas) {
            const poolRows = @json($driverPoolSummary ?? []);
            new Chart(poolCanvas, {
                type: 'bar',
                data: {
                    labels: poolRows.map(row => row.pool),
                    datasets: [{
                        label: 'Person',
                        data: poolRows.map(row => row.count),
                        backgroundColor: 'rgba(16, 185, 129, 0.72)',
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: textColor }, grid: { display: false } },
                        y: { ticks: { color: textColor, precision: 0 }, grid: { color: 'rgba(148,163,184,0.15)' } }
                    }
                }
            });
        }
    });

    document.addEventListener('alpine:init', () => {
        Alpine.data('driverPage', () => ({
            showCreateModal: false,
            showAssignModal: false,
            assigningOpp: null,
            availableDrivers: [],
            selectedDrivers: [],
            isAssigning: false,
            pendingOpps: [],

            init() {
                const pendingOppsElement = document.getElementById('pending-driver-assignments-data');
                this.pendingOpps = pendingOppsElement ? JSON.parse(pendingOppsElement.textContent) : [];
            },

            openAssignModal(oppId) {
                const opp = this.pendingOpps.find(o => String(o.id) === String(oppId));
                if (!opp) {
                    console.error('Opportunity not found:', oppId);
                    return;
                }

                this.showCreateModal = false;
                this.showAssignModal = false;
                this.assigningOpp = JSON.parse(JSON.stringify(opp));
                this.selectedDrivers = (this.assigningOpp.assigned_drivers || this.assigningOpp.assignedDrivers || []).map(d => d.id);

                window.requestAnimationFrame(() => {
                    this.showAssignModal = true;
                });

                this.loadAvailableDrivers(opp.id);
            },

            async loadAvailableDrivers(oppId) {
                try {
                    const res = await fetch(`/api/drivers/available?opportunity_id=${oppId}`);
                    this.availableDrivers = await res.json();
                } catch(e) {
                    console.error('Failed to load drivers', e);
                }
            },

            autoSelectDrivers() {
                const required = Number(this.assigningOpp?.required_drivers || 0);
                if (required <= 0) return;
                const existing = new Set(this.selectedDrivers.map(id => Number(id)));
                const next = [...existing];
                for (const driver of this.availableDrivers) {
                    if (next.length >= required) break;
                    if (!existing.has(Number(driver.id))) {
                        next.push(Number(driver.id));
                    }
                }
                this.selectedDrivers = next.slice(0, required);
            },

            async saveAssignment() {
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
                            driver_ids: this.selectedDrivers
                        })
                    });

                    if (res.ok) {
                        this.showAssignModal = false;
                        this.assigningOpp = null;

                        const url = new URL(window.location.href);
                        window.location.href = url.toString();
                    } else {
                        const err = await res.json();
                        alert(err.message || 'Gagal menyimpan alokasi supir.');
                    }
                } catch(e) {
                    console.error(e);
                } finally {
                    this.isAssigning = false;
                }
            }
        }));
    });
</script>
@endpush
@endsection
