@extends('layouts.app')
@section('title', 'Maintenance')
@section('content')
<div class="bg-white rounded-lg shadow p-4 mb-4 flex flex-wrap gap-3 items-center justify-between">
    <form method="GET" class="flex gap-2">
        <input name="search" value="{{ request('search') }}" placeholder="Search plate..." class="border rounded px-3 py-1.5 text-sm w-48">
        <select name="status" class="border rounded px-3 py-1.5 text-sm">
            <option value="">All Status</option>
            @foreach(['scheduled','in_progress','completed'] as $s)<option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach
        </select>
        <button class="bg-navy text-white px-4 py-1.5 rounded text-sm">Filter</button>
    </form>
    @if(auth()->user()->role==='operational')
    <a href="{{ route('maintenance.create') }}" class="bg-blue text-white px-4 py-2 rounded text-sm">+ New Log</a>
    @endif
</div>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Vehicle</th><th class="px-4 py-3 text-left">Type</th><th class="px-4 py-3 text-left">Vendor</th><th class="px-4 py-3 text-right">Cost</th><th class="px-4 py-3 text-left">Scheduled</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3">Actions</th></tr></thead>
        <tbody>
        @forelse($logs as $m)
        <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-3 font-medium">{{ $m->vehicle->plate_number ?? '-' }}</td>
            <td class="px-4 py-3">{{ ucfirst($m->type) }}</td>
            <td class="px-4 py-3">{{ $m->vendor ?? '-' }}</td>
            <td class="px-4 py-3 text-right">{{ formatIDR($m->cost) }}</td>
            <td class="px-4 py-3">{{ $m->scheduled_date->format('d M Y') }}</td>
            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs {{ $m->status==='completed'?'bg-green-100 text-green-700':'bg-yellow-100 text-yellow-700' }}">{{ ucfirst(str_replace('_',' ',$m->status)) }}</span></td>
            <td class="px-4 py-3 text-center"><a href="{{ route('maintenance.show',$m) }}" class="text-blue hover:underline text-xs">View</a></td>
        </tr>
        @empty
        <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">No maintenance logs</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="p-3 border-t">{{ $logs->links() }}</div>
</div>
@endsection
