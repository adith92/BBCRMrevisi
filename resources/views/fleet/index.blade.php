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
        <select name="tier" class="border rounded px-3 py-1.5 text-sm">
            <option value="">All Tiers</option>
            @foreach(['standard','business','premium','luxury','executive'] as $t)<option value="{{ $t }}" {{ request('tier')===$t?'selected':'' }}>{{ ucfirst($t) }}</option>@endforeach
        </select>
        <button class="bg-navy text-white px-4 py-1.5 rounded text-sm">Filter</button>
    </form>
    @if(auth()->user()->role==='operational')
    <a href="{{ route('fleet.create') }}" class="bg-blue text-white px-4 py-2 rounded text-sm">+ New Vehicle</a>
    @endif
</div>

<!-- Fleet Grid View -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
@forelse($vehicles as $v)
<div class="card overflow-hidden group cursor-pointer" onclick="window.location='{{ route('fleet.show',$v) }}'">
    <!-- Thumbnail Foto Hitam -->
    <div class="h-40 bg-gray-900 relative overflow-hidden">
        <img src="https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=400&h=300&fit=crop" 
             alt="{{ $v->model }}" 
             class="w-full h-full object-cover opacity-60 group-hover:opacity-80 transition-opacity duration-300"
             style="filter: brightness(0.75) saturate(0.3);">
        <div class="absolute top-2 right-2">
            <span class="px-2 py-1 rounded text-xs font-semibold text-white backdrop-blur-sm
                @if($v->tier==='standard') bg-gray-500
                @elseif($v->tier==='business') bg-blue-500
                @elseif($v->tier==='premium') bg-purple-500
                @elseif($v->tier==='luxury') bg-yellow-500
                @else bg-red-500 @endif">
                {{ ucfirst($v->tier) }}
            </span>
        </div>
        <div class="absolute bottom-2 left-2">
            <span class="px-2 py-1 rounded text-xs font-semibold text-white backdrop-blur-sm
                @if($v->status==='available') bg-green-500
                @elseif($v->status==='on_trip') bg-blue-500
                @else bg-yellow-500 @endif">
                {{ ucfirst(str_replace('_',' ',$v->status)) }}
            </span>
        </div>
    </div>
    <div class="p-4">
        <div class="flex justify-between items-start mb-2">
            <div>
                <h3 class="font-semibold text-navy">{{ $v->model }}</h3>
                <p class="text-xs text-gray-500">{{ $v->plate_number }}</p>
            </div>
            <span class="text-xs text-gray-400">{{ $v->year }}</span>
        </div>
        <div class="flex justify-between text-sm text-gray-600">
            <span>{{ $v->capacity }} seats</span>
            <span>{{ $v->pool->name ?? '-' }}</span>
        </div>
    </div>
</div>
@empty
<div class="col-span-full text-center py-8 text-gray-400">No vehicles found</div>
@endforelse
</div>

<div class="mt-4">{{ $vehicles->links() }}</div>
@endsection
