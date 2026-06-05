@extends('layouts.app')

@section('page-title', 'Tambah Kontrak Berlangganan')

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => route('subscriptions.index'), 'label' => 'Subscription Billing'],
    ['url' => '#', 'label' => 'Tambah Kontrak'],
]" />

<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Tambah Kontrak Berlangganan</h2>

        @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-300 rounded-lg p-4">
            <p class="font-medium text-red-800 mb-1">Terdapat kesalahan:</p>
            <ul class="list-disc list-inside text-sm text-red-700 space-y-0.5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('subscriptions.store') }}" x-data="subscriptionForm()" class="space-y-5">
            @csrf

            {{-- Client --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Client <span class="text-red-500">*</span>
                </label>
                <select name="client_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">— Pilih Client —</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                        {{ $client->company_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Vehicle & Driver --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Kendaraan <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <select name="vehicle_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">— Pilih Kendaraan —</option>
                        @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->plate_number }} — {{ $vehicle->brand }} {{ $vehicle->model }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Driver <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <select name="driver_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">— Pilih Driver —</option>
                        @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                            {{ $driver->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Product --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Produk <span class="text-gray-400 font-normal">(opsional)</span>
                </label>
                <select name="product_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">— Pilih Produk —</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                        {{ $product->name }} ({{ $product->sku }})
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Start & End Date --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tanggal Mulai <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="start_date" required
                           value="{{ old('start_date') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tanggal Selesai <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="end_date" required
                           value="{{ old('end_date') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            {{-- Monthly Rate --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Monthly Rate (IDR) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">Rp</span>
                    <input type="text" id="monthly_rate_display" placeholder="0"
                           value="{{ old('monthly_rate') ? number_format((float)old('monthly_rate'), 0, ',', '.') : '' }}"
                           oninput="formatRupiah(this, 'monthly_rate')"
                           class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <input type="hidden" name="monthly_rate" id="monthly_rate" value="{{ old('monthly_rate') }}">
                </div>
                <p class="mt-1 text-xs text-gray-400">Rate per siklus penagihan yang dipilih di bawah</p>
            </div>

            {{-- Billing Cycle --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Siklus Penagihan <span class="text-red-500">*</span>
                </label>
                <div class="flex flex-wrap gap-3">
                    @foreach(['monthly' => 'Bulanan', 'quarterly' => 'Kuartalan (3 bulan)', 'yearly' => 'Tahunan'] as $val => $lbl)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="billing_cycle" value="{{ $val }}"
                               {{ old('billing_cycle', 'monthly') === $val ? 'checked' : '' }}
                               class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">{{ $lbl }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Auto Renew --}}
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div>
                    <p class="text-sm font-medium text-gray-700">Auto Renew</p>
                    <p class="text-xs text-gray-400 mt-0.5">Otomatis perpanjang kontrak saat jatuh tempo</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="auto_renew" value="1"
                           {{ old('auto_renew', '1') == '1' ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer
                                peer-checked:after:translate-x-full peer-checked:after:border-white
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5
                                after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="3" placeholder="Catatan tambahan..."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none">{{ old('notes') }}</textarea>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                    Simpan Kontrak
                </button>
                <a href="{{ route('subscriptions.index') }}"
                   class="bg-gray-100 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function formatRupiah(input, hiddenId) {
    let raw = input.value.replace(/\D/g, '');
    input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
    document.getElementById(hiddenId).value = raw;
}

function subscriptionForm() {
    return {};
}
</script>
@endsection
