@extends('layouts.app')
@section('title', 'Fleet')
@section('content')
<div class="bg-white rounded-lg shadow p-4 mb-4 flex flex-wrap gap-3 items-center justify-between">
    <form method="GET" class="flex gap-2">
        <input name="search" value="{{ request('search') }}" placeholder="Search plate/model..." class="border rounded px-3 py-1.5 text-sm w-48">
        <select name="status" class="border rounded px-3 py-1.5 text-sm">
            <option value="">All Status</option>
            @foreach(['available','on_trip','maintenance','inactive'] as $s)<option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach
        </select>
        <select name="brand" class="border rounded px-3 py-1.5 text-sm">
            <option value="">All Brands</option>
            @foreach(['bigbird','goldenbird','cititrans','executive'] as $b)<option value="{{ $b }}" {{ request('brand')===$b?'selected':'' }}>{{ ucfirst($b) }}</option>@endforeach
        </select>
        <button class="bg-navy text-white px-4 py-1.5 rounded text-sm">Filter</button>
    </form>
    @if(auth()->user()->role==='operational')
    <a href="{{ route('fleet.create') }}" class="bg-blue text-white px-4 py-2 rounded text-sm">+ New Vehicle</a>
    @endif
</div>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Plate</th><th class="px-4 py-3 text-left">Brand</th><th class="px-4 py-3 text-left">Model</th><th class="px-4 py-3 text-left">Capacity</th><th class="px-4 py-3 text-left">Year</th><th class="px-4 py-3 text-left">Pool</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3">Actions</th></tr></thead>
        <tbody>
        @forelse($vehicles as $v)
        <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-3 font-medium">{{ $v->plate_number }}</td>
            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs bg-navy/10 text-navy">{{ ucfirst($v->brand) }}</span></td>
            <td class="px-4 py-3">{{ $v->model }}</td>
            <td class="px-4 py-3">{{ $v->capacity }} pax</td>
            <td class="px-4 py-3">{{ $v->year }}</td>
            <td class="px-4 py-3">{{ $v->pool->name ?? '-' }}</td>
            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs {{ $v->status==='available'?'bg-green-100 text-green-700':($v->status==='on_trip'?'bg-blue-100 text-blue-700':'bg-yellow-100 text-yellow-700') }}">{{ ucfirst(str_replace('_',' ',$v->status)) }}</span></td>
            <td class="px-4 py-3 text-center"><a href="{{ route('fleet.show',$v) }}" class="text-blue hover:underline text-xs">View</a></td>
        </tr>
        @empty
        <tr><td colspan="8" class="px-4 py-6 text-center text-gray-400">No vehicles</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="p-3 border-t">{{ $vehicles->links() }}</div>
</div>
@endsection
