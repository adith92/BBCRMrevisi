@extends('layouts.app')

@section('page-title', 'Detail Persetujuan')

@section('content')
@php
    $user = auth()->user();
    $opp  = $approval->opportunity;
    $canAct = $approval->status === 'pending' && $approval->current_approver_id === $user->id;
@endphp

<div x-data="approvalDetail()" class="space-y-6 max-w-4xl">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('approvals.index') }}" class="hover:text-blue-600 transition-colors cursor-pointer">Antrian Persetujuan</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-900 font-medium">Detail Permintaan</span>
    </nav>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- ===== HEADER CARD ===== --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-mono bg-gray-100 text-gray-600 px-2 py-0.5 rounded">
                        {{ $opp?->opp_number ?? 'N/A' }}
                    </span>
                    @include('approvals._status_badge', ['status' => $approval->status])
                    @php $levelLabels = [1 => 'Level 1 – Manager', 2 => 'Level 2 – GM', 3 => 'Level 3 – Director']; @endphp
                    <span class="text-xs bg-indigo-50 text-indigo-700 border border-indigo-200 px-2 py-0.5 rounded font-semibold">
                        {{ $levelLabels[$approval->level] ?? 'Level '.$approval->level }}
                    </span>
                </div>
                <h1 class="text-xl font-bold text-gray-900">{{ $opp?->title ?? 'Permintaan Persetujuan' }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    Diajukan oleh <span class="font-medium text-gray-700">{{ $approval->requester?->name ?? '-' }}</span>
                    &bull; {{ $approval->created_at->format('d M Y, H:i') }}
                </p>
            </div>

            <a href="{{ route('approvals.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ===== LEFT COLUMN: Opportunity + Discount Details ===== --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Opportunity Summary --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800 text-sm">Ringkasan Opportunity</h2>
                </div>
                <div class="p-5 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Client</p>
                        <p class="font-semibold text-gray-900">{{ $opp?->client?->company_name ?? '-' }}</p>
                        <p class="text-sm text-gray-500">{{ $opp?->client?->pic_name ?? '' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Sales</p>
                        <p class="font-semibold text-gray-900">{{ $opp?->sales?->name ?? '-' }}</p>
                        <span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded">
                            {{ strtoupper($opp?->sales?->role ?? '') }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Produk</p>
                        <p class="font-semibold text-gray-900">{{ $opp?->product?->name ?? '-' }}</p>
                        <p class="text-sm text-gray-500">{{ $opp?->product?->productCategory?->name ?? '' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Stage</p>
                        @if($opp)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold
                            @if($opp->stage === 'won') bg-green-100 text-green-800
                            @elseif($opp->stage === 'lost') bg-red-100 text-red-800
                            @elseif($opp->stage === 'negotiation') bg-orange-100 text-orange-800
                            @elseif($opp->stage === 'proposal') bg-yellow-100 text-yellow-800
                            @else bg-blue-100 text-blue-800 @endif">
                            {{ $opp->stage_label ?? ucfirst($opp->stage) }}
                        </span>
                        @else
                        <span class="text-gray-500">-</span>
                        @endif
                    </div>
                    @if($opp?->notes)
                    <div class="col-span-2">
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Catatan Opportunity</p>
                        <p class="text-sm text-gray-700 bg-gray-50 rounded-lg px-3 py-2">{{ $opp->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Discount Request Details --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800 text-sm">Detail Permintaan Diskon</h2>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-3 gap-4 mb-5">
                        {{-- Original Price --}}
                        <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                            <p class="text-xs text-gray-500 font-medium mb-1">Harga Asli</p>
                            <p class="text-lg font-bold text-gray-900">
                                Rp {{ number_format($approval->original_price, 0, ',', '.') }}
                            </p>
                        </div>

                        {{-- Discount Percent --}}
                        <div class="bg-orange-50 rounded-xl p-4 text-center border border-orange-100">
                            <p class="text-xs text-orange-600 font-medium mb-1">Diskon</p>
                            <p class="text-2xl font-bold text-orange-600">
                                {{ number_format($approval->discount_percent, 1) }}%
                            </p>
                            <p class="text-xs text-orange-500 mt-0.5">
                                - Rp {{ number_format($approval->original_price * ($approval->discount_percent / 100), 0, ',', '.') }}
                            </p>
                        </div>

                        {{-- Final Price --}}
                        <div class="bg-green-50 rounded-xl p-4 text-center border border-green-100">
                            <p class="text-xs text-green-600 font-medium mb-1">Harga Final</p>
                            <p class="text-lg font-bold text-green-700">
                                Rp {{ number_format($approval->final_price, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    @if($approval->notes)
                    <div class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-3">
                        <p class="text-xs font-semibold text-blue-700 mb-1">Catatan Persetujuan</p>
                        <p class="text-sm text-blue-800">{{ $approval->notes }}</p>
                    </div>
                    @endif

                    @if($approval->rejection_reason)
                    <div class="bg-red-50 border border-red-100 rounded-lg px-4 py-3">
                        <p class="text-xs font-semibold text-red-700 mb-1">Alasan Penolakan</p>
                        <p class="text-sm text-red-800">{{ $approval->rejection_reason }}</p>
                    </div>
                    @endif

                    {{-- Timestamps --}}
                    <div class="mt-4 flex flex-wrap gap-4 text-xs text-gray-500">
                        <span>Diajukan: <span class="font-medium text-gray-700">{{ $approval->created_at->format('d M Y, H:i') }}</span></span>
                        @if($approval->approved_at)
                        <span>Disetujui: <span class="font-medium text-green-700">{{ $approval->approved_at->format('d M Y, H:i') }}</span></span>
                        @endif
                        @if($approval->rejected_at)
                        <span>Ditolak: <span class="font-medium text-red-700">{{ $approval->rejected_at->format('d M Y, H:i') }}</span></span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            @if($canAct)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="font-semibold text-gray-800 text-sm mb-4">Ambil Tindakan</h2>
                <div class="flex gap-3">
                    <button @click="showApproveForm = true; showRejectForm = false"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Setujui Permintaan
                    </button>
                    <button @click="showRejectForm = true; showApproveForm = false"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white border-2 border-red-300 hover:border-red-500 text-red-600 hover:text-red-800 rounded-lg text-sm font-semibold transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Tolak Permintaan
                    </button>
                </div>

                {{-- Inline Approve Form --}}
                <div x-show="showApproveForm" x-cloak class="mt-4 bg-green-50 border border-green-200 rounded-xl p-4" style="display:none;">
                    <h3 class="text-sm font-semibold text-green-800 mb-3">Konfirmasi Persetujuan</h3>
                    <form action="{{ route('approvals.approve', $approval) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-xs font-medium text-green-700 mb-1">Catatan (opsional)</label>
                            <textarea name="notes" rows="3" placeholder="Tambahkan catatan..."
                                class="w-full border border-green-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none"></textarea>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" @click="showApproveForm = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 cursor-pointer transition-colors">Batal</button>
                            <button type="submit" class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold cursor-pointer transition-colors">Setujui</button>
                        </div>
                    </form>
                </div>

                {{-- Inline Reject Form --}}
                <div x-show="showRejectForm" x-cloak class="mt-4 bg-red-50 border border-red-200 rounded-xl p-4" style="display:none;">
                    <h3 class="text-sm font-semibold text-red-800 mb-3">Konfirmasi Penolakan</h3>
                    <form action="{{ route('approvals.reject', $approval) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-xs font-medium text-red-700 mb-1">
                                Alasan Penolakan <span class="text-red-500">*</span>
                            </label>
                            <textarea name="rejection_reason" rows="4" required
                                placeholder="Jelaskan alasan penolakan permintaan ini..."
                                class="w-full border border-red-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none"></textarea>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" @click="showRejectForm = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 cursor-pointer transition-colors">Batal</button>
                            <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold cursor-pointer transition-colors">Tolak</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

        </div>

        {{-- ===== RIGHT COLUMN: Approval Chain ===== --}}
        <div class="space-y-5">

            {{-- Chain Visualization --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="font-semibold text-gray-800 text-sm">Rantai Persetujuan</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Jalur approval diperlukan</p>
                </div>
                <div class="p-5">
                    @php
                        $steps = [
                            1 => ['label' => 'L1 Manager',  'icon_bg' => 'bg-blue-100',   'icon_text' => 'text-blue-600'],
                            2 => ['label' => 'L2 GM',       'icon_bg' => 'bg-purple-100',  'icon_text' => 'text-purple-600'],
                            3 => ['label' => 'L3 Director', 'icon_bg' => 'bg-amber-100',   'icon_text' => 'text-amber-600'],
                        ];
                        $chainByLevel = $chainRequests->keyBy('level');
                    @endphp

                    <div class="space-y-1">
                        @for($lvl = 1; $lvl <= 3; $lvl++)
                        @php
                            $step         = $steps[$lvl];
                            $chainReq     = $chainByLevel->get($lvl);
                            $isRequired   = $lvl <= $maxLevel;
                            $isActive     = $chainReq && $chainReq->status === 'pending';
                            $isDone       = $chainReq && $chainReq->status === 'approved';
                            $isRejected   = $chainReq && $chainReq->status === 'rejected';
                            $isSkipped    = !$isRequired;
                            $isCurrent    = $approval->level === $lvl;
                        @endphp

                        <div class="flex items-start gap-3">
                            {{-- Step indicator --}}
                            <div class="flex flex-col items-center">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0
                                    @if($isDone) bg-green-100
                                    @elseif($isRejected) bg-red-100
                                    @elseif($isActive && $isCurrent) {{ $step['icon_bg'] }} ring-2 ring-offset-1 ring-blue-400
                                    @elseif($isRequired) {{ $step['icon_bg'] }}
                                    @else bg-gray-100 @endif">

                                    @if($isDone)
                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @elseif($isRejected)
                                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    @elseif($isSkipped)
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"/>
                                        </svg>
                                    @else
                                        <span class="text-xs font-bold {{ $step['icon_text'] }}">{{ $lvl }}</span>
                                    @endif
                                </div>

                                @if($lvl < 3)
                                <div class="w-0.5 h-4 mt-1
                                    @if($isDone) bg-green-300
                                    @elseif($isSkipped) bg-gray-200
                                    @else bg-gray-200 @endif">
                                </div>
                                @endif
                            </div>

                            {{-- Step content --}}
                            <div class="pb-4 flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="text-sm font-semibold
                                        @if($isDone) text-green-700
                                        @elseif($isRejected) text-red-700
                                        @elseif($isSkipped) text-gray-400
                                        @elseif($isCurrent) text-blue-700
                                        @else text-gray-700 @endif">
                                        {{ $step['label'] }}
                                    </p>
                                    @if($isCurrent && $isActive)
                                    <span class="text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded font-medium">Sekarang</span>
                                    @endif
                                    @if($isSkipped)
                                    <span class="text-xs bg-gray-100 text-gray-400 px-1.5 py-0.5 rounded font-medium">Tidak Diperlukan</span>
                                    @endif
                                </div>

                                @if($chainReq)
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Approver: <span class="font-medium">{{ $chainReq->currentApprover?->name ?? 'Belum ditentukan' }}</span>
                                    </p>
                                    @if($isDone && $chainReq->approved_at)
                                    <p class="text-xs text-green-600 mt-0.5">{{ $chainReq->approved_at->format('d M Y H:i') }}</p>
                                    @endif
                                    @if($isRejected && $chainReq->rejected_at)
                                    <p class="text-xs text-red-600 mt-0.5">{{ $chainReq->rejected_at->format('d M Y H:i') }}</p>
                                    @endif
                                @elseif($isRequired && !$isSkipped)
                                    <p class="text-xs text-gray-400 mt-0.5">Menunggu level sebelumnya</p>
                                @endif
                            </div>
                        </div>
                        @endfor
                    </div>

                    {{-- Summary --}}
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <p class="text-xs text-gray-500">
                            Diperlukan hingga <span class="font-semibold text-gray-700">
                                {{ $steps[$maxLevel]['label'] }}
                            </span>
                            untuk diskon {{ number_format($approval->discount_percent, 1) }}%
                        </p>
                    </div>
                </div>
            </div>

            {{-- Info Card --}}
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-xs font-semibold text-blue-700 mb-2">Aturan Persetujuan</p>
                <ul class="text-xs text-blue-700 space-y-1">
                    <li class="flex items-start gap-1.5">
                        <span class="mt-0.5 w-1.5 h-1.5 rounded-full bg-blue-400 flex-shrink-0"></span>
                        Diskon &le;5% — Cukup Manager
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="mt-0.5 w-1.5 h-1.5 rounded-full bg-blue-400 flex-shrink-0"></span>
                        Diskon 5–15% — Manager &rarr; GM
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="mt-0.5 w-1.5 h-1.5 rounded-full bg-blue-400 flex-shrink-0"></span>
                        Diskon &gt;15% — Manager &rarr; GM &rarr; Director
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="mt-0.5 w-1.5 h-1.5 rounded-full bg-purple-400 flex-shrink-0"></span>
                        Deal &gt;50 juta — Mulai dari GM
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="mt-0.5 w-1.5 h-1.5 rounded-full bg-amber-400 flex-shrink-0"></span>
                        Deal &gt;200 juta — Langsung Director
                    </li>
                </ul>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="//unpkg.com/alpinejs" defer></script>
<script>
function approvalDetail() {
    return {
        showApproveForm: false,
        showRejectForm: false,
    }
}
</script>
@endpush
