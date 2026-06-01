@extends('layouts.app')
@section('title', 'New Vehicle')
@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form method="POST" action="{{ route('fleet.store') }}">@csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium mb-1">Plate Number</label><input name="plate_number" required class="w-full border rounded px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium mb-1">Brand</label><select name="brand" class="w-full border rounded px-3 py-2 text-sm">@foreach(['bigbird','goldenbird','cititrans','executive'] as $b)<option value="{{ $b }}">{{ ucfirst($b) }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium mb-1">Model</label><input name="model" required class="w-full border rounded px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium mb-1">Capacity</label><input name="capacity" type="number" required class="w-full border rounded px-3 py-2 text-sm" value="7"></div>
            <div><label class="block text-sm font-medium mb-1">Year</label><input name="year" type="number" required class="w-full border rounded px-3 py-2 text-sm" value="{{ date('Y') }}"></div>
            <div><label class="block text-sm font-medium mb-1">Status</label><select name="status" class="w-full border rounded px-3 py-2 text-sm">@foreach(['available','on_trip','maintenance','inactive'] as $s)<option value="{{ $s }}">{{ ucfirst($s) }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium mb-1">Pool</label><select name="pool_id" class="w-full border rounded px-3 py-2 text-sm">@foreach($pools as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
            <div class="md:col-span-2"><label class="block text-sm font-medium mb-1">Notes</label><textarea name="notes" rows="2" class="w-full border rounded px-3 py-2 text-sm"></textarea></div>
        </div>
        <div class="flex gap-3 mt-4">
            <button type="submit" class="bg-navy text-white px-6 py-2 rounded text-sm">Create Vehicle</button>
            <a href="{{ route('fleet.index') }}" class="text-gray-500 hover:underline text-sm py-2">Cancel</a>
        </div>
    </form>
</div>
@endsection
