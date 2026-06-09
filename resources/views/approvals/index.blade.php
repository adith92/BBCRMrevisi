@extends('layouts.app')

@section('header_title', 'Antrian Persetujuan')

@section('content')
<div x-data="approvalQueue()" class="space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[var(--cc-text)]">Antrian Persetujuan</h1>
            <p class="text-sm text-[var(--cc-text-muted)] mt-1">Kelola permintaan persetujuan diskon dan harga</p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center gap-2 text-sm">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center gap-2 text-sm">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9v4a1 1 0 102 0V9a1 1 0 10-2 0zm0-4a1 1 0 112 0 1 1 0 01-2 0z" clip-rule="evenodd"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- ========== SECTION 1: PENDING APPROVALS (for approvers) ========== --}}
    @if($user->isManager() || $user->isGM() || $user->isDirector())
    <div class="cc-card rounded-xl border border-[var(--cc-border)] shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-[var(--cc-border)] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-[var(--cc-text)]">Menunggu Persetujuan Saya</h2>
                    <p class="text-xs text-[var(--cc-text-muted)]">Permintaan yang memerlukan tindakan Anda</p>
                </div>
            </div>
            @if($pendingApprovals instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <span class="text-sm font-medium text-amber-700 bg-amber-50 border border-amber-200 px-3 py-1 rounded-full">
                {{ $pendingApprovals->total() }} permintaan
            </span>
            @endif
        </div>

        @if($pendingApprovals->isEmpty())
        <div class="px-6 py-12 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-[var(--cc-text-muted)] text-sm font-medium">Tidak ada permintaan yang menunggu persetujuan</p>
            <p class="text-gray-400 text-xs mt-1">Semua sudah ditangani</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[var(--cc-bg-muted)] border-b border-[var(--cc-border)]">
                        <th class="px-4 py-3 text-left font-semibold text-[var(--cc-text-muted)] text-xs uppercase tracking-wide">Opp #</th>
                        <th class="px-4 py-3 text-left font-semibold text-[var(--cc-text-muted)] text-xs uppercase tracking-wide">Client</th>
                        <th class="px-4 py-3 text-left font-semibold text-[var(--cc-text-muted)] text-xs uppercase tracking-wide">Sales</th>
                        <th class="px-4 py-3 text-right font-semibold text-[var(--cc-text-muted)] text-xs uppercase tracking-wide">Diskon</th>
                        <th class="px-4 py-3 text-right font-semibold text-[var(--cc-text-muted)] text-xs uppercase tracking-wide">Nilai Deal</th>
                        <th class="px-4 py-3 text-center font-semibold text-[var(--cc-text-muted)] text-xs uppercase tracking-wide">Level</th>
                        <th class="px-4 py-3 text-center font-semibold text-[var(--cc-text-muted)] text-xs uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3 text-center font-semibold text-[var(--cc-text-muted)] text-xs uppercase tracking-wide">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($pendingApprovals as $approval)
                    <tr class="hover:bg-[var(--cc-row-hover)] transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('approvals.show', $approval) }}" class="font-mono text-blue-600 hover:text-blue-800 font-semibold text-xs cursor-pointer">
                                {{ $approval->opportunity?->opp_number ?? '-' }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-[var(--cc-text)]">{{ $approval->opportunity?->client?->company_name ?? '-' }}</p>
                            <p class="text-xs text-gray-400">{{ $approval->opportunity?->title ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-[var(--cc-text)]">{{ $approval->requester?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-semibold text-orange-600">{{ number_format($approval->discount_percent, 1) }}%</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="text-[var(--cc-text)] font-medium">
                                Rp {{ number_format($approval->original_price, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php $levelLabels = [1 => 'L1 Manager', 2 => 'L2 GM', 3 => 'L3 Director']; @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $levelLabels[$approval->level] ?? 'L'.$approval->level }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @include('approvals._status_badge', ['status' => $approval->status])
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Approve --}}
                                <button
                                    @click="openApprove({{ $approval->id }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-green-600 hover:bg-green-700 text-white transition-colors cursor-pointer">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Setujui
                                </button>
                                {{-- Reject --}}
                                <button
                                    @click="openReject({{ $approval->id }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 transition-colors cursor-pointer">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Tolak
                                </button>
                                {{-- Detail --}}
                                <a href="{{ route('approvals.show', $approval) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-[var(--cc-bg-muted)] hover:bg-gray-200 text-[var(--cc-text)] transition-colors cursor-pointer">
                                    Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($pendingApprovals->hasPages())
        <div class="px-6 py-4 border-t border-[var(--cc-border)]">
            {{ $pendingApprovals->links() }}
        </div>
        @endif
        @endif
    </div>
    @endif

    {{-- ========== SECTION 2: MY SUBMITTED REQUESTS (for sales & managers) ========== --}}
    @if($user->isSales() || $user->isManager())
    <div class="cc-card rounded-xl border border-[var(--cc-border)] shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-[var(--cc-border)] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-[var(--cc-text)]">Permintaan Saya</h2>
                    <p class="text-xs text-[var(--cc-text-muted)]">Permintaan persetujuan yang telah Anda ajukan</p>
                </div>
            </div>
        </div>

        @if($myRequests->isEmpty())
        <div class="px-6 py-12 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-[var(--cc-text-muted)] text-sm font-medium">Belum ada permintaan persetujuan</p>
            <p class="text-gray-400 text-xs mt-1">Permintaan persetujuan diskon akan muncul di sini</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[var(--cc-bg-muted)] border-b border-[var(--cc-border)]">
                        <th class="px-4 py-3 text-left font-semibold text-[var(--cc-text-muted)] text-xs uppercase tracking-wide">Opp #</th>
                        <th class="px-4 py-3 text-left font-semibold text-[var(--cc-text-muted)] text-xs uppercase tracking-wide">Client</th>
                        <th class="px-4 py-3 text-right font-semibold text-[var(--cc-text-muted)] text-xs uppercase tracking-wide">Diskon</th>
                        <th class="px-4 py-3 text-right font-semibold text-[var(--cc-text-muted)] text-xs uppercase tracking-wide">Harga Asli</th>
                        <th class="px-4 py-3 text-right font-semibold text-[var(--cc-text-muted)] text-xs uppercase tracking-wide">Harga Final</th>
                        <th class="px-4 py-3 text-center font-semibold text-[var(--cc-text-muted)] text-xs uppercase tracking-wide">Level</th>
                        <th class="px-4 py-3 text-left font-semibold text-[var(--cc-text-muted)] text-xs uppercase tracking-wide">Approver</th>
                        <th class="px-4 py-3 text-center font-semibold text-[var(--cc-text-muted)] text-xs uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3 text-center font-semibold text-[var(--cc-text-muted)] text-xs uppercase tracking-wide">Diajukan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($myRequests as $req)
                    <tr class="hover:bg-[var(--cc-row-hover)] transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('approvals.show', $req) }}" class="font-mono text-blue-600 hover:text-blue-800 font-semibold text-xs cursor-pointer">
                                {{ $req->opportunity?->opp_number ?? '-' }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-[var(--cc-text)]">{{ $req->opportunity?->client?->company_name ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-semibold text-orange-600">{{ number_format($req->discount_percent, 1) }}%</span>
                        </td>
                        <td class="px-4 py-3 text-right text-[var(--cc-text-muted)] line-through text-xs">
                            Rp {{ number_format($req->original_price, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-[var(--cc-text)]">
                            Rp {{ number_format($req->final_price, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php $levelLabels = [1 => 'L1', 2 => 'L2', 3 => 'L3']; @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                {{ $levelLabels[$req->level] ?? 'L'.$req->level }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-[var(--cc-text)] text-xs">{{ $req->currentApprover?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            @include('approvals._status_badge', ['status' => $req->status])
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-[var(--cc-text-muted)]">
                            {{ $req->created_at->diffForHumans() }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($myRequests->hasPages())
        <div class="px-6 py-4 border-t border-[var(--cc-border)]">
            {{ $myRequests->links() }}
        </div>
        @endif
        @endif
    </div>
    @endif

</div>

{{-- ===== APPROVE MODAL ===== --}}
<div x-show="showApproveModal" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display:none;">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModals()"></div>
    <div class="relative cc-card rounded-2xl shadow-xl w-full max-w-md p-6 z-10">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-[var(--cc-text)]">Setujui Permintaan</h3>
                <p class="text-sm text-[var(--cc-text-muted)]">Konfirmasi persetujuan diskon ini</p>
            </div>
        </div>

        <form :action="'/approvals/' + currentApprovalId + '/approve'" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-[var(--cc-text)] mb-2">Catatan (opsional)</label>
                <textarea name="notes" rows="3" placeholder="Tambahkan catatan persetujuan..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" @click="closeModals()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-[var(--cc-text)] hover:bg-[var(--cc-row-hover)] cursor-pointer transition-colors">
                    Batal
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold cursor-pointer transition-colors">
                    Setujui Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ===== REJECT MODAL ===== --}}
<div x-show="showRejectModal" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display:none;">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModals()"></div>
    <div class="relative cc-card rounded-2xl shadow-xl w-full max-w-md p-6 z-10">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-[var(--cc-text)]">Tolak Permintaan</h3>
                <p class="text-sm text-[var(--cc-text-muted)]">Berikan alasan penolakan yang jelas</p>
            </div>
        </div>

        <form :action="'/approvals/' + currentApprovalId + '/reject'" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-[var(--cc-text)] mb-2">
                    Alasan Penolakan <span class="text-red-500">*</span>
                </label>
                <textarea name="rejection_reason" rows="4" required
                    placeholder="Jelaskan alasan penolakan permintaan ini..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none"></textarea>
                <p class="text-xs text-gray-400 mt-1">Alasan ini akan dilihat oleh pengaju permintaan</p>
            </div>
            <div class="flex gap-3">
                <button type="button" @click="closeModals()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-[var(--cc-text)] hover:bg-[var(--cc-row-hover)] cursor-pointer transition-colors">
                    Batal
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold cursor-pointer transition-colors">
                    Tolak Permintaan
                </button>
            </div>
        </form>
    </div>

    @include('approvals.charts')
</div>
@endsection

@push('scripts')
<script src="//unpkg.com/alpinejs" defer></script>
<script>
function approvalQueue() {
    return {
        showApproveModal: false,
        showRejectModal: false,
        currentApprovalId: null,

        openApprove(id) {
            this.currentApprovalId = id;
            this.showApproveModal = true;
            this.showRejectModal = false;
        },

        openReject(id) {
            this.currentApprovalId = id;
            this.showRejectModal = true;
            this.showApproveModal = false;
        },

        closeModals() {
            this.showApproveModal = false;
            this.showRejectModal = false;
            this.currentApprovalId = null;
        }
    }
}
</script>
@endpush
