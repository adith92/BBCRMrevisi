@extends('layouts.app')

@section('header_title', 'Buat Voucher')

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => route('vouchers.index'), 'label' => 'E-Voucher'],
    ['url' => '#', 'label' => 'Buat Voucher'],
]" />

<div class="max-w-3xl mx-auto space-y-6">

    {{-- Single Voucher Form --}}
    <div class="cc-card rounded-lg shadow p-6" x-data="voucherForm()">
        <h2 class="text-xl font-semibold text-gray-900 mb-1">Buat Voucher</h2>
        <p class="text-sm text-gray-500 mb-6">Isi form di bawah untuk membuat satu voucher baru</p>

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

        <form method="POST" action="{{ route('vouchers.store') }}" class="space-y-5">
            @csrf

            {{-- Voucher Code --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Kode Voucher <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2">
                    <input type="text" name="voucher_code" id="voucher_code" required
                           value="{{ old('voucher_code') }}"
                           placeholder="Contoh: VCH-ABCD1234"
                           maxlength="50"
                           class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono uppercase
                                  focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           oninput="this.value = this.value.toUpperCase()">
                    <button type="button" onclick="generateCode()"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium whitespace-nowrap">
                        🔄 Generate
                    </button>
                </div>
                <p class="mt-1 text-xs text-gray-400">Kode unik yang akan tertera di voucher (untuk scanning)</p>
            </div>

            {{-- Title --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Judul Voucher <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" required
                       value="{{ old('title') }}"
                       placeholder="Contoh: Voucher Transportasi Kantor Q1 2025"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            {{-- Client & Product --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Client <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <select name="client_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">— Umum (tidak terikat client) —</option>
                        @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->company_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Produk <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <select name="product_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">— Tidak Terikat Produk —</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} ({{ $product->sku }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Denomination & Purchase Price --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Denominasi (Nilai Voucher) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">Rp</span>
                        <input type="text" id="denomination_display" placeholder="0"
                               value="{{ old('denomination') ? number_format((float)old('denomination'), 0, ',', '.') : '' }}"
                               oninput="formatRupiah(this, 'denomination')"
                               class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <input type="hidden" name="denomination" id="denomination" value="{{ old('denomination') }}">
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Nilai yang tertera di voucher</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Harga Jual (Purchase Price) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">Rp</span>
                        <input type="text" id="purchase_price_display" placeholder="0"
                               value="{{ old('purchase_price') ? number_format((float)old('purchase_price'), 0, ',', '.') : '' }}"
                               oninput="formatRupiah(this, 'purchase_price')"
                               class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <input type="hidden" name="purchase_price" id="purchase_price" value="{{ old('purchase_price') }}">
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Harga yang dibayar client</p>
                </div>
            </div>

            {{-- Valid Period --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Berlaku Mulai <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="valid_from" required
                           value="{{ old('valid_from', today()->toDateString()) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Berlaku Sampai <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="valid_until" required
                           value="{{ old('valid_until') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="3" placeholder="Catatan penggunaan voucher..."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none">{{ old('notes') }}</textarea>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                    🎫 Buat Voucher
                </button>
                <a href="{{ route('vouchers.index') }}"
                   class="bg-gray-100 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
                    Batal
                </a>
            </div>
        </form>
    </div>

    {{-- Bulk Generate Section --}}
    <div class="cc-card rounded-lg shadow p-6" x-data="{ expanded: false }">
        <button type="button" @click="expanded = !expanded"
                class="w-full flex items-center justify-between text-left">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Bulk Generate Voucher</h3>
                <p class="text-sm text-gray-500 mt-0.5">Buat banyak voucher sekaligus dengan kode otomatis</p>
            </div>
            <span class="text-gray-400 text-xl transition-transform" :class="expanded ? 'rotate-180' : ''">▼</span>
        </button>

        <div x-show="expanded" x-collapse class="mt-6 pt-6 border-t border-gray-200">
            <form method="POST" action="{{ route('vouchers.bulk') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Prefix --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Prefix Kode
                        </label>
                        <input type="text" name="code_prefix" value="{{ old('code_prefix', 'VCH') }}"
                               maxlength="10" placeholder="VCH"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono uppercase
                                      focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               oninput="this.value = this.value.toUpperCase()">
                        <p class="mt-1 text-xs text-gray-400">Contoh: "VCH" → VCH-AB12CD34</p>
                    </div>

                    {{-- Quantity --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Jumlah Voucher <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="quantity" required min="1" max="500"
                               value="{{ old('quantity', 10) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-400">Maksimal 500 voucher sekaligus</p>
                    </div>
                </div>

                {{-- Title --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Judul Voucher <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" required
                           value="{{ old('title') }}"
                           placeholder="Contoh: Voucher Transportasi Batch Q1 2025"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                {{-- Client & Product --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Client (opsional)</label>
                        <select name="client_id"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">— Umum —</option>
                            @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Produk (opsional)</label>
                        <select name="product_id"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">— Tidak Terikat —</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Denomination & Purchase Price --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Denominasi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                            <input type="text" id="bulk_denom_display" placeholder="0"
                                   oninput="formatRupiah(this, 'bulk_denomination')"
                                   class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <input type="hidden" name="denomination" id="bulk_denomination">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Harga Jual <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                            <input type="text" id="bulk_price_display" placeholder="0"
                                   oninput="formatRupiah(this, 'bulk_purchase_price')"
                                   class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <input type="hidden" name="purchase_price" id="bulk_purchase_price">
                        </div>
                    </div>
                </div>

                {{-- Valid Period --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Berlaku Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="valid_from" required
                               value="{{ today()->toDateString() }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Berlaku Sampai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="valid_until" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Catatan batch voucher..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"></textarea>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm text-amber-700">
                    ⚠ Proses bulk generate tidak dapat dibatalkan. Pastikan semua data sudah benar.
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                            onclick="return confirm('Generate voucher dalam jumlah banyak? Proses ini tidak dapat dibatalkan.')"
                            class="bg-green-600 text-white px-6 py-2.5 rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                        🚀 Bulk Generate
                    </button>
                    <button type="button" @click="expanded = false"
                            class="bg-gray-100 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
                        Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function formatRupiah(input, hiddenId) {
    let raw = input.value.replace(/\D/g, '');
    input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
    document.getElementById(hiddenId).value = raw;
}

function generateCode() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let code = 'VCH-';
    for (let i = 0; i < 8; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('voucher_code').value = code;
}
</script>
@endsection
