@extends('layouts.app')

@section('header_title', 'Vehicle Contracts')

@section('content')
<div class="space-y-6 font-sans">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-[#003887] text-[28px]">contract</span>
            <div>
                <h2 class="text-xl font-bold text-slate-900">Vehicle Contracts</h2>
                <p class="text-xs text-slate-400">Kontrak penyewaan armada &amp; driver</p>
            </div>
        </div>
        <a href="{{ route('vehicle-contracts.create') }}" class="flex items-center gap-2 bg-[#003887] hover:bg-secondary text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
            <span class="material-symbols-outlined text-[18px]">add</span> Kontrak Baru
        </a>
    </div>

    {{-- Table --}}
    <div class="cc-card rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-5 py-3">Kontrak</th>
                        <th class="px-5 py-3">Client</th>
                        <th class="px-5 py-3">Kendaraan</th>
                        <th class="px-5 py-3">Driver</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($contracts as $c)
                    @php
                        $statusColors = [
                            'active'    => 'bg-emerald-100 text-emerald-700',
                            'pending'   => 'bg-amber-100 text-amber-700',
                            'completed' => 'bg-blue-100 text-blue-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                        ];
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <a href="{{ route('vehicle-contracts.show', $c->id) }}" class="font-semibold text-slate-800 hover:text-[#003887]">{{ $c->contract_number ?? 'KTR-'.str_pad($c->id,4,'0',STR_PAD_LEFT) }}</a>
                        </td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $c->client->company_name ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $c->vehicle->plate_number ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $c->driver->name ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-[11px] font-bold {{ $statusColors[$c->status] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($c->status ?? 'unknown') }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('vehicle-contracts.show', $c->id) }}" class="text-[#003887] hover:underline text-xs font-semibold">Detail →</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">
                        <span class="material-symbols-outlined text-4xl block mb-2 opacity-40">inbox</span>
                        Belum ada kontrak kendaraan
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($contracts->hasPages())
        <div class="px-5 py-3 border-t border-slate-100">{{ $contracts->links() }}</div>
        @endif
    </div>
</div>
@endsection
