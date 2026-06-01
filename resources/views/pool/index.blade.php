@extends('layouts.app')
@section('title', 'Pools')
@section('content')
<div class="bg-white rounded-lg shadow p-4 mb-4 flex justify-between items-center">
    <p class="text-sm text-gray-500">{{ count($pools ?? []) }} pools registered</p>
    @if(auth()->user()->role==='operational')
    <a href="{{ route('pool.create') }}" class="bg-blue text-white px-4 py-2 rounded text-sm">+ New Pool</a>
    @endif
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
@forelse($pools ?? [] as $p)
<div class="bg-white rounded-lg shadow p-4 border-l-4 border-navy">
    <h3 class="font-semibold text-navy">{{ $p->name }}</h3>
    <p class="text-sm text-gray-500 mt-1">{{ $p->location }}</p>
    <div class="mt-3 flex justify-between text-sm">
        <span>Vehicles: <strong>{{ $p->vehicles_count }}</strong></span>
        <span>Capacity: <strong>{{ $p->capacity }}</strong></span>
    </div>
    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
        <div class="bg-blue h-2 rounded-full" style="width: {{ min(100, ($p->vehicles_count/max(1,$p->capacity))*100) }}%"></div>
    </div>
    <a href="{{ route('pool.show',$p) }}" class="text-blue text-xs mt-2 block">View Details →</a>
</div>
@empty
<div class="col-span-3 text-center py-8 text-gray-400">No pools registered</div>
@endforelse
</div>
@endsection
