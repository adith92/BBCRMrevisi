<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Booking;
use App\Models\Client;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'invoices');

        $invoices = Invoice::with(['booking', 'client', 'payments'])
            ->latest()
            ->paginate(15);

        $payments = Payment::with('invoice.client')
            ->latest()
            ->paginate(15);

        $purchaseOrders = PurchaseOrder::latest()->paginate(15);

        return view('finance.index', compact('tab', 'invoices', 'payments', 'purchaseOrders'));
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'invoice');
        
        if ($type === 'invoice') {
            $bookings = Booking::whereDoesntHave('invoice')->where('status', 'completed')->get();
            $clients = Client::where('status', 'active')->get();
            return view('finance.create', compact('type', 'bookings', 'clients'));
        } elseif ($type === 'payment') {
            $invoices = Invoice::where('status', 'sent')->with('client')->get();
            return view('finance.create', compact('type', 'invoices'));
        } else {
            return view('finance.create', compact('type'));
        }
    }

    public function store(Request $request)
    {
        $type = $request->get('type', 'invoice');

        if ($type === 'invoice') {
            $validated = $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'client_id' => 'required|exists:clients,id',
                'amount' => 'required|numeric|min:0',
                'due_date' => 'required|date',
                'notes' => 'nullable|string',
            ]);

            $validated['amount'] = str_replace('.', '', $validated['amount']);
            $validated['invoice_number'] = 'INV-' . now()->format('YmdHis');
            $validated['status'] = 'draft';

            $invoice = Invoice::create($validated);

            return redirect()->route('finance.index', ['tab' => 'invoices'])
                ->with('success', 'Invoice berhasil dibuat: ' . $invoice->invoice_number);

        } elseif ($type === 'payment') {
            $validated = $request->validate([
                'invoice_id' => 'required|exists:invoices,id',
                'amount' => 'required|numeric|min:0',
                'method' => 'required|in:transfer,cash,giro',
                'payment_date' => 'required|date',
                'notes' => 'nullable|string',
            ]);

            $validated['amount'] = str_replace('.', '', $validated['amount']);
            $validated['payment_number'] = 'PAY-' . now()->format('YmdHis');

            $payment = Payment::create($validated);

            // Update invoice status if fully paid
            $invoice = Invoice::find($validated['invoice_id']);
            $totalPaid = $invoice->payments()->sum('amount') + $payment->amount;
            if ($totalPaid >= $invoice->amount) {
                $invoice->update(['status' => 'paid', 'paid_at' => now()]);
            }

            return redirect()->route('finance.index', ['tab' => 'payments'])
                ->with('success', 'Payment berhasil dicatat.');

        } else {
            $validated = $request->validate([
                'vendor' => 'required|string|max:255',
                'item_description' => 'required|string',
                'amount' => 'required|numeric|min:0',
                'status' => 'required|in:pending,approved,received',
                'notes' => 'nullable|string',
            ]);

            $validated['amount'] = str_replace('.', '', $validated['amount']);
            $validated['po_number'] = 'PO-' . now()->format('YmdHis');

            PurchaseOrder::create($validated);

            return redirect()->route('finance.index', ['tab' => 'purchase-orders'])
                ->with('success', 'Purchase Order berhasil dibuat.');
        }
    }

    public function show(Request $request, $id)
    {
        $type = $request->get('type', 'invoice');
        
        if ($type === 'invoice') {
            $invoice = Invoice::with(['booking', 'client', 'payments'])->findOrFail($id);
            return view('finance.show', compact('type', 'invoice'));
        } elseif ($type === 'payment') {
            $payment = Payment::with('invoice.client')->findOrFail($id);
            return view('finance.show', compact('type', 'payment'));
        } else {
            $po = PurchaseOrder::findOrFail($id);
            return view('finance.show', compact('type', 'po'));
        }
    }

    public function destroy(Request $request, $id)
    {
        $type = $request->get('type', 'invoice');
        
        if ($type === 'invoice') {
            Invoice::findOrFail($id)->delete();
        } elseif ($type === 'payment') {
            Payment::findOrFail($id)->delete();
        } else {
            PurchaseOrder::findOrFail($id)->delete();
        }

        return redirect()->route('finance.index', ['tab' => $type === 'invoice' ? 'invoices' : ($type === 'payment' ? 'payments' : 'purchase-orders')])
            ->with('success', 'Data berhasil dihapus.');
    }

    public function apiInvoiceShow(Invoice $invoice)
    {
        $invoice->load(['booking', 'client', 'payments']);
        
        return response()->json([
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'client' => $invoice->client,
            'amount_formatted' => formatIDR($invoice->amount),
            'status' => $invoice->status,
            'due_date' => $invoice->due_date,
            'paid_at' => $invoice->paid_at,
            'payments' => $invoice->payments,
        ]);
    }
}
