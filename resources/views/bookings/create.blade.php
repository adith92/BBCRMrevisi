@extends('layouts.app')

@section('header_title', 'Create Booking')

@section('content')
<div class="max-w-2xl mx-auto cc-card rounded-lg shadow p-8">
    <h2 class="text-2xl font-bold mb-6">Create New Booking</h2>

    <form method="POST" action="{{ route('bookings.store') }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Client -->
            <div>
                <label class="block text-sm font-semibold mb-2">Client *</label>
                <select name="client_id" required class="w-full px-4 py-2 border rounded">
                    <option value="">Select Client</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                    @endforeach
                </select>
                @error('client_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Vehicle -->
            <div>
                <label class="block text-sm font-semibold mb-2">Vehicle *</label>
                <select name="vehicle_id" required class="w-full px-4 py-2 border rounded">
                    <option value="">Select Vehicle</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->model }} ({{ $vehicle->plate_number }})</option>
                    @endforeach
                </select>
                @error('vehicle_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Driver -->
            <div>
                <label class="block text-sm font-semibold mb-2">Driver *</label>
                <select name="driver_id" required class="w-full px-4 py-2 border rounded">
                    <option value="">Select Driver</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                    @endforeach
                </select>
                @error('driver_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Sales (GM only) -->
            @if(auth()->user()->isGM())
            <div>
                <label class="block text-sm font-semibold mb-2">Sales *</label>
                <select name="sales_id" required class="w-full px-4 py-2 border rounded">
                    <option value="">Select Sales</option>
                    @foreach($sales as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Pickup DateTime -->
            <div>
                <label class="block text-sm font-semibold mb-2">Pickup Date & Time *</label>
                <input type="datetime-local" name="pickup_datetime" required class="w-full px-4 py-2 border rounded">
                @error('pickup_datetime') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Dropoff DateTime -->
            <div>
                <label class="block text-sm font-semibold mb-2">Dropoff Date & Time *</label>
                <input type="datetime-local" name="dropoff_datetime" required class="w-full px-4 py-2 border rounded">
                @error('dropoff_datetime') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Destination -->
            <div>
                <label class="block text-sm font-semibold mb-2">Destination *</label>
                <input type="text" name="destination" required class="w-full px-4 py-2 border rounded" placeholder="e.g. Bandung">
                @error('destination') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Price -->
            <div>
                <label class="block text-sm font-semibold mb-2">Price (Rp) *</label>
                <input type="text" name="price" required class="w-full px-4 py-2 border rounded idr-input" placeholder="1000000">
                @error('price') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Notes -->
        <div>
            <label class="block text-sm font-semibold mb-2">Notes</label>
            <textarea name="notes" rows="4" class="w-full px-4 py-2 border rounded" placeholder="Additional notes..."></textarea>
        </div>

        <!-- Buttons -->
        <div class="flex gap-4 pt-6 border-t">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded font-semibold hover:bg-blue-600">Create Booking</button>
            <a href="{{ route('bookings.index') }}" class="bg-gray-300 text-gray-800 px-6 py-2 rounded font-semibold hover:bg-gray-400">Cancel</a>
        </div>
    </form>
</div>

<script>
    // IDR input formatting
    document.querySelectorAll('.idr-input').forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            this.value = value ? parseInt(value).toLocaleString('id-ID') : '';
        });
    });
</script>
@endsection
