<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Product;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:gm,finance,manager')->only(['create', 'store', 'expire']);
    }

    public function index()
    {
        $status = request('status');

        $vouchers = Voucher::with(['client', 'issuedBy'])
            ->when($status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Quick stats
        $stats = [
            'available'       => Voucher::where('status', 'available')->count(),
            'used'            => Voucher::where('status', 'used')->count(),
            'value_available' => Voucher::where('status', 'available')->sum('denomination'),
            'expired'         => Voucher::where('status', 'expired')->count(),
        ];

        return view('vouchers.index', compact('vouchers', 'stats', 'status'));
    }

    public function create()
    {
        $clients  = Client::where('status', 'active')->orderBy('company_name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('vouchers.create', compact('clients', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'voucher_code'   => 'required|string|max:50|unique:vouchers,voucher_code',
            'client_id'      => 'nullable|exists:clients,id',
            'product_id'     => 'nullable|exists:products,id',
            'title'          => 'required|string|max:255',
            'denomination'   => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'valid_from'     => 'required|date',
            'valid_until'    => 'required|date|after_or_equal:valid_from',
            'notes'          => 'nullable|string',
        ]);

        $validated['issued_by'] = auth()->id();
        $validated['status']    = 'available';

        $voucher = Voucher::create($validated);

        return redirect()
            ->route('vouchers.show', $voucher)
            ->with('success', 'Voucher ' . $voucher->voucher_code . ' berhasil dibuat.');
    }

    /**
     * Bulk generate vouchers.
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'client_id'      => 'nullable|exists:clients,id',
            'product_id'     => 'nullable|exists:products,id',
            'title'          => 'required|string|max:255',
            'denomination'   => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'valid_from'     => 'required|date',
            'valid_until'    => 'required|date|after_or_equal:valid_from',
            'quantity'       => 'required|integer|min:1|max:500',
            'code_prefix'    => 'nullable|string|max:10',
            'notes'          => 'nullable|string',
        ]);

        $quantity  = $validated['quantity'];
        $prefix    = strtoupper($validated['code_prefix'] ?? 'VCH');
        $created   = 0;

        for ($i = 0; $i < $quantity; $i++) {
            $code = $prefix . '-' . strtoupper(substr(md5(uniqid($prefix, true)), 0, 8));

            // Ensure uniqueness (retry once on collision)
            while (Voucher::where('voucher_code', $code)->exists()) {
                $code = $prefix . '-' . strtoupper(substr(md5(uniqid($prefix, true)), 0, 8));
            }

            Voucher::create([
                'voucher_code'   => $code,
                'client_id'      => $validated['client_id'] ?? null,
                'product_id'     => $validated['product_id'] ?? null,
                'title'          => $validated['title'],
                'denomination'   => $validated['denomination'],
                'purchase_price' => $validated['purchase_price'],
                'valid_from'     => $validated['valid_from'],
                'valid_until'    => $validated['valid_until'],
                'status'         => 'available',
                'issued_by'      => auth()->id(),
                'notes'          => $validated['notes'] ?? null,
            ]);

            $created++;
        }

        return redirect()
            ->route('vouchers.index')
            ->with('success', "{$created} voucher berhasil dibuat.");
    }

    public function show(Voucher $voucher)
    {
        $voucher->load(['client', 'product', 'issuedBy', 'usedByBooking']);

        return view('vouchers.show', compact('voucher'));
    }

    public function redeem(Request $request, Voucher $voucher)
    {
        if ($voucher->status !== 'available') {
            return back()->with('error', 'Voucher tidak dapat digunakan. Status: ' . $voucher->status);
        }

        if ($voucher->valid_until < today()) {
            return back()->with('error', 'Voucher sudah kedaluwarsa.');
        }

        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $voucher->update([
            'status'              => 'used',
            'used_at'             => now(),
            'used_by_booking_id'  => $validated['booking_id'],
        ]);

        return back()->with('success', 'Voucher ' . $voucher->voucher_code . ' berhasil digunakan.');
    }

    public function expire(Voucher $voucher)
    {
        if (!in_array($voucher->status, ['available'])) {
            return back()->with('error', 'Voucher tidak dapat diexpire. Status saat ini: ' . $voucher->status);
        }

        $voucher->update(['status' => 'expired']);

        return back()->with('success', 'Voucher ' . $voucher->voucher_code . ' telah di-expire.');
    }
}
