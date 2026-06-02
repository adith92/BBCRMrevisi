@extends('layouts.app')
@section('title', 'Clients')
@section('content')
<div class="bg-white rounded-lg shadow p-4 mb-4 flex flex-wrap gap-3 items-center justify-between">
    <form method="GET" class="flex gap-2">
        <input name="search" value="{{ request('search') }}" placeholder="Search..." class="border rounded px-3 py-1.5 text-sm w-48">
        <select name="status" class="border rounded px-3 py-1.5 text-sm">
            <option value="">All Status</option>
            @foreach(['active','prospect','inactive'] as $s)<option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>@endforeach
        </select>
        <button class="bg-navy text-white px-4 py-1.5 rounded text-sm">Filter</button>
    </form>
    @if(auth()->user()->role==='sales')
    <a href="{{ route('clients.create') }}" class="bg-blue text-white px-4 py-2 rounded text-sm">+ New Client</a>
    @endif
</div>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left">Company</th><th class="px-4 py-3 text-left">PIC</th><th class="px-4 py-3 text-left">Industry</th><th class="px-4 py-3 text-left">Tier</th><th class="px-4 py-3 text-left">Sales</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3">Actions</th></tr></thead>
        <tbody>
        @forelse($clients as $c)
        <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-3 font-medium">{{ $c->company_name }}</td>
            <td class="px-4 py-3">{{ $c->pic_name }}</td>
            <td class="px-4 py-3">{{ $c->industry ?? '-' }}</td>
            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs font-semibold text-white
                @if($c->tier==='platinum') bg-purple-500
                @elseif($c->tier==='gold') bg-yellow-500
                @elseif($c->tier==='silver') bg-gray-400
                @else bg-orange-400 @endif">{{ ucfirst($c->tier) }}</span></td>
            <td class="px-4 py-3">{{ $c->assignedSales->name ?? '-' }}</td>
            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs {{ $c->status==='active'?'bg-green-100 text-green-700':'bg-gray-100 text-gray-600' }}">{{ $c->status }}</span></td>
            <td class="px-4 py-3 text-center"><a href="{{ route('clients.show',$c) }}" class="text-blue hover:underline text-xs">View</a></td>
        </tr>
        @empty
        <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">No clients</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="p-3 border-t">{{ $clients->links() }}</div>
</div>
@endsection
