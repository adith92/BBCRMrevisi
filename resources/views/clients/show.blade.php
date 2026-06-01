@extends('layouts.app')
@section('title', 'Client — ' . $client->company_name)
@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-3xl mb-4">
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div><span class="text-gray-500">Company:</span> <strong>{{ $client->company_name }}</strong></div>
        <div><span class="text-gray-500">PIC:</span> {{ $client->pic_name }}</div>
        <div><span class="text-gray-500">Phone:</span> {{ $client->phone }}</div>
        <div><span class="text-gray-500">Email:</span> {{ $client->email }}</div>
        <div><span class="text-gray-500">Industry:</span> {{ $client->industry ?? '-' }}</div>
        <div><span class="text-gray-500">Status:</span> <span class="px-2 py-0.5 rounded text-xs {{ $client->status==='active'?'bg-green-100 text-green-700':'bg-gray-100 text-gray-600' }}">{{ $client->status }}</span></div>
        <div><span class="text-gray-500">Sales:</span> {{ $client->assignedSales->name ?? '-' }}</div>
        @if($client->address)<div class="col-span-2"><span class="text-gray-500">Address:</span> {{ $client->address }}</div>@endif
    </div>
    <div class="mt-4 flex gap-3">
        <a href="{{ route('clients.edit',$client) }}" class="bg-yellow-500 text-white px-4 py-2 rounded text-sm">Edit</a>
        <a href="{{ route('clients.index') }}" class="text-gray-500 hover:underline text-sm py-2">← Back</a>
    </div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-navy mb-3">Meeting Logs</h3>
        <table class="w-full text-sm"><thead><tr class="border-b"><th class="text-left py-2">Date</th><th>Outcome</th><th>Status</th></tr></thead><tbody>
        @forelse($meetingLogs ?? [] as $m)
        <tr class="border-b"><td class="py-2">{{ \Carbon\Carbon::parse($m->meeting_date)->format('d M Y') }}</td><td>{{ Str::limit($m->outcome,30) }}</td><td>{{ $m->status }}</td></tr>
        @empty<tr><td colspan="3" class="py-2 text-gray-400">No meetings</td></tr>@endforelse
        </tbody></table>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold text-navy mb-3">Bookings</h3>
        <table class="w-full text-sm"><thead><tr class="border-b"><th class="text-left py-2">No.</th><th>Vehicle</th><th>Status</th></tr></thead><tbody>
        @forelse($bookings ?? [] as $b)
        <tr class="border-b"><td class="py-2">{{ $b->booking_number }}</td><td>{{ $b->vehicle->plate_number ?? '-' }}</td><td>{{ $b->status }}</td></tr>
        @empty<tr><td colspan="3" class="py-2 text-gray-400">No bookings</td></tr>@endforelse
        </tbody></table>
    </div>
</div>
@endsection
