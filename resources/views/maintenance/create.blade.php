@extends('layouts.app')
@section('title', 'New Log')
@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form method="POST" action="{{ route('maintenance.store') }}">@csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium mb-1">Vehicle</label><select name="vehicle_id" required class="w-full border rounded px-3 py-2 text-sm">@foreach($vehicles as $v)<option value="{{ $v->id }}">{{ $v->plate_number }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium mb-1">Type</label><select name="type" class="w-full border rounded px-3 py-2 text-sm">@foreach(['routine','repair','modification'] as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium mb-1">Scheduled Date</label><input name="scheduled_date" type="date" required class="w-full border rounded px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium mb-1">Cost (IDR)</label><input name="cost" class="idr-input w-full border rounded px-3 py-2 text-sm" placeholder="Rp 0"></div>
            <div><label class="block text-sm font-medium mb-1">Vendor</label><input name="vendor" class="w-full border rounded px-3 py-2 text-sm"></div>
            <div><label class="block text-sm font-medium mb-1">Status</label><select name="status" class="w-full border rounded px-3 py-2 text-sm">@foreach(['scheduled','in_progress','completed'] as $s)<option value="{{ $s }}">{{ ucfirst($s) }}</option>@endforeach</select></div>
            <div class="md:col-span-2"><label class="block text-sm font-medium mb-1">Description</label><textarea name="description" rows="2" class="w-full border rounded px-3 py-2 text-sm"></textarea></div>
        </div>
        <div class="flex gap-3 mt-4">
            <button type="submit" class="bg-navy text-white px-6 py-2 rounded text-sm">Create Log</button>
            <a href="{{ route('maintenance.index') }}" class="text-gray-500 hover:underline text-sm py-2">Cancel</a>
        </div>
    </form>
</div>
@endsection
