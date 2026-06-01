@extends('layouts.app')
@section('title', 'Pool — ' . ($pool->name ?? ''))
@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl mb-4">
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div><span class="text-gray-500">Name:</span> <strong>{{ $pool->name }}</strong></div>
        <div><span class="text-gray-500">Location:</span> {{ $pool->location }}</div>
        <div><span class="text-gray-500">Capacity:</span> {{ $pool->capacity }}</div>
        <div><span class="text-gray-500">Current Vehicles:</span> {{ $pool->vehicles->count() }}</div>
    </div>
    <a href="{{ route('pool.index') }}" class="text-gray-500 hover:underline text-sm mt-4 block">← Back</a>
</div>
<div class="bg-white rounded-lg shadow p-4">
    <h3 class="font-semibold text-navy mb-3">Vehicles in this Pool</h3>
    <table class="w-full text-sm"><thead><tr class="border-b"><th class="text-left py-2">Plate</th><th>Brand</th><th>Model</th><th>Status</th></tr></thead><tbody>
    @forelse($vehicles as $v)
    <tr class="border-b"><td class="py-2">{{ $v->plate_number }}</td><td>{{ ucfirst($v->brand) }}</td><td>{{ $v->model }}</td><td><span class="px-2 py-0.5 rounded text-xs {{ $v->status==='available'?'bg-green-100 text-green-700':'bg-yellow-100 text-yellow-700' }}">{{ ucfirst(str_replace('_',' ',$v->status)) }}</span></td></tr>
    @empty<tr><td colspan="4" class="py-2 text-gray-400">No vehicles</td></tr>@endforelse
    </tbody></table>
</div>
@endsection
