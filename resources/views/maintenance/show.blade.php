@extends('layouts.app')
@section('title', 'Maintenance Detail')
@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div><span class="text-gray-500">Vehicle:</span> <strong>{{ $maintenance->vehicle->plate_number ?? '-' }}</strong></div>
        <div><span class="text-gray-500">Type:</span> {{ ucfirst($maintenance->type) }}</div>
        <div><span class="text-gray-500">Vendor:</span> {{ $maintenance->vendor ?? '-' }}</div>
        <div><span class="text-gray-500">Cost:</span> <strong>{{ formatIDR($maintenance->cost) }}</strong></div>
        <div><span class="text-gray-500">Scheduled:</span> {{ $maintenance->scheduled_date->format('d M Y') }}</div>
        <div><span class="text-gray-500">Completed:</span> {{ $maintenance->completed_date ? $maintenance->completed_date->format('d M Y') : 'Not yet' }}</div>
        <div><span class="text-gray-500">Status:</span> <span class="px-2 py-0.5 rounded text-xs {{ $maintenance->status==='completed'?'bg-green-100 text-green-700':'bg-yellow-100 text-yellow-700' }}">{{ ucfirst(str_replace('_',' ',$maintenance->status)) }}</span></div>
        @if($maintenance->description)<div class="col-span-2"><span class="text-gray-500">Description:</span> {{ $maintenance->description }}</div>@endif
        @if($maintenance->notes)<div class="col-span-2"><span class="text-gray-500">Notes:</span> {{ $maintenance->notes }}</div>@endif
    </div>
    <div class="flex gap-3 mt-4">
        <a href="{{ route('maintenance.edit',$maintenance) }}" class="bg-yellow-500 text-white px-4 py-2 rounded text-sm">Edit</a>
        <a href="{{ route('maintenance.index') }}" class="text-gray-500 hover:underline text-sm py-2">← Back</a>
    </div>
</div>
@endsection
