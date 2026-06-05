@extends('layouts.app')

@section('header_title', 'Sales Pipeline')

@section('content')
<div
    x-data="{
        searchTerm: '',
        filterBySearch(title, clientName) {
            if (!this.searchTerm) return true;
            const q = this.searchTerm.toLowerCase();
            return title.toLowerCase().includes(q) || clientName.toLowerCase().includes(q);
        }
    }"
    class="p-4 md:p-6 space-y-5"
>

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Sales Pipeline</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola pipeline deal secara visual per tahap</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Search --}}
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z"/>
                </svg>
                <input
                    x-model="searchTerm"
                    type="text"
                    placeholder="Cari deal..."
                    class="pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-52"
                >
            </div>

            @if(auth()->user()->isSales() || auth()->user()->isManager() || auth()->user()->isGM() || auth()->user()->isDirector())
            <a href="{{ route('opportunities.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors duration-200 cursor-pointer shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Deal
            </a>
            @endif
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="flex items-start gap-3 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
        <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Kanban Board --}}
    <div class="overflow-x-auto pb-4">
        <div class="flex gap-4 min-w-max">

            @php
            $stageConfig = [
                'prospecting' => [
                    'label'      => 'Prospekting',
                    'header_bg'  => 'bg-blue-600',
                    'card_border'=> 'border-l-blue-500',
                    'badge_bg'   => 'bg-blue-100 text-blue-700',
                    'count_bg'   => 'bg-blue-100 text-blue-700',
                ],
                'proposal' => [
                    'label'      => 'Proposal',
                    'header_bg'  => 'bg-amber-500',
                    'card_border'=> 'border-l-amber-400',
                    'badge_bg'   => 'bg-amber-100 text-amber-700',
                    'count_bg'   => 'bg-amber-100 text-amber-700',
                ],
                'negotiation' => [
                    'label'      => 'Negosiasi',
                    'header_bg'  => 'bg-orange-500',
                    'card_border'=> 'border-l-orange-400',
                    'badge_bg'   => 'bg-orange-100 text-orange-700',
                    'count_bg'   => 'bg-orange-100 text-orange-700',
                ],
                'won' => [
                    'label'      => 'Menang',
                    'header_bg'  => 'bg-emerald-600',
                    'card_border'=> 'border-l-emerald-500',
                    'badge_bg'   => 'bg-emerald-100 text-emerald-700',
                    'count_bg'   => 'bg-emerald-100 text-emerald-700',
                ],
                'lost' => [
                    'label'      => 'Kalah',
                    'header_bg'  => 'bg-red-500',
                    'card_border'=> 'border-l-red-400',
                    'badge_bg'   => 'bg-red-100 text-red-700',
                    'count_bg'   => 'bg-red-100 text-red-700',
                ],
            ];
            @endphp

            @foreach($stages as $stage)
            @php
            $cfg   = $stageConfig[$stage];
            $col   = $kanban[$stage];
            $opps  = $col['opportunities'];
            $count = $col['count'];
            $total = $col['total_value'];
            @endphp

            <div class="w-72 flex-shrink-0 flex flex-col gap-3">

                {{-- Column Header --}}
                <div class="{{ $cfg['header_bg'] }} rounded-xl p-4 text-white shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-sm tracking-wide uppercase">{{ $cfg['label'] }}</span>
                        <span class="bg-white/25 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">{{ $count }}</span>
                    </div>
                    <div class="mt-1 text-xs text-white/80 font-medium">
                        Rp {{ number_format($total, 0, ',', '.') }}
                    </div>
                </div>

                {{-- Cards --}}
                <div class="flex flex-col gap-3 min-h-24">
                    @forelse($opps as $opp)
                    <div
                        x-show="filterBySearch('{{ addslashes($opp->title) }}', '{{ addslashes($opp->client->company_name ?? '') }}')"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        class="bg-white rounded-xl border border-slate-100 border-l-4 {{ $cfg['card_border'] }} p-4 shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer group"
                    >
                        <a href="{{ route('opportunities.show', $opp->id) }}" class="block">
                            {{-- OPP number --}}
                            <div class="text-xs text-slate-400 font-mono mb-1">{{ $opp->opp_number }}</div>

                            {{-- Title --}}
                            <h3 class="text-sm font-semibold text-slate-800 group-hover:text-blue-700 transition-colors line-clamp-2 leading-snug">
                                {{ $opp->title }}
                            </h3>

                            {{-- Client --}}
                            <div class="flex items-center gap-1.5 mt-2 text-xs text-slate-500">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <span class="truncate">{{ $opp->client->company_name ?? '-' }}</span>
                            </div>

                            {{-- Value --}}
                            @if($opp->estimated_value)
                            <div class="mt-2.5 flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-700">
                                    Rp {{ number_format((float)$opp->estimated_value, 0, ',', '.') }}
                                </span>
                                @if($opp->pax)
                                <span class="text-xs text-slate-400">{{ $opp->pax }} pax</span>
                                @endif
                            </div>
                            @endif

                            {{-- Expected close date --}}
                            @if($opp->expected_close_date)
                            <div class="mt-2 flex items-center gap-1.5 text-xs text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span @class([
                                    'font-medium',
                                    'text-red-500' => $opp->expected_close_date->isPast() && !in_array($opp->stage, ['won','lost']),
                                    'text-slate-400' => !($opp->expected_close_date->isPast() && !in_array($opp->stage, ['won','lost']))
                                ])>
                                    {{ $opp->expected_close_date->format('d M Y') }}
                                </span>
                            </div>
                            @endif

                            {{-- Discount warning --}}
                            @if($opp->discount_percent > 0 && !$opp->discount_approved)
                            <div class="mt-2 flex items-center gap-1.5 text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded-md">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                Diskon {{ $opp->discount_percent }}% pending
                            </div>
                            @endif

                            {{-- Sales avatar --}}
                            @if($opp->sales && !auth()->user()->isSales())
                            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center gap-2">
                                <div class="w-5 h-5 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 text-xs font-bold">
                                    {{ strtoupper(substr($opp->sales->name, 0, 1)) }}
                                </div>
                                <span class="text-xs text-slate-400 truncate">{{ $opp->sales->name }}</span>
                            </div>
                            @endif
                        </a>
                    </div>
                    @empty
                    <div class="text-center py-8 text-sm text-slate-400 bg-white/50 rounded-xl border border-dashed border-slate-200">
                        Belum ada deal
                    </div>
                    @endforelse
                </div>

            </div>
            @endforeach

        </div>
    </div>

</div>
@endsection
