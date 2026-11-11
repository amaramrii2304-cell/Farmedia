<?php

namespace App\Http\Controllers;

use App\Models\CommissionInvoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminCommissionInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'waiting_verification');

        $invoices = CommissionInvoice::query()
            ->with(['mitra'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.commission_invoices.index', compact('invoices', 'status'));
    }

    public function show(CommissionInvoice $invoice)
    {
        $invoice->load(['mitra', 'orders.items.product', 'orders.user']);
        return view('admin.commission_invoices.show', compact('invoice'));
    }

    public function approve(CommissionInvoice $invoice)
    {
        if ($invoice->status !== 'waiting_verification') {
            return back()->with('error', 'Invoice tidak dalam status verifikasi.');
        }

        DB::transaction(function () use ($invoice) {
            $invoice->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);

            $orderIds = $invoice->orders()->pluck('orders.id')->all();
            if (empty($orderIds)) return;

            $payload = [];
            // ✅ update kolom yang benar-benar ada di DB
            if (Schema::hasColumn('orders', 'komisi_lunas_at')) {
                $payload['komisi_lunas_at'] = $invoice->paid_at;
            }
            if (Schema::hasColumn('orders', 'komisi_lunas')) {
                $payload['komisi_lunas'] = $invoice->paid_at;
            }

            if (!empty($payload)) {
                Order::whereIn('id', $orderIds)->update($payload);
            }
        });

        return redirect()->route('admin.commission_invoices.index', ['status' => 'waiting_verification'])
            ->with('success', 'Pembayaran disetujui. Komisi ditandai lunas.');
    }

    public function reject(Request $request, CommissionInvoice $invoice)
    {
        if ($invoice->status !== 'waiting_verification') {
            return back()->with('error', 'Invoice tidak dalam status verifikasi.');
        }

        $request->validate([
            'notes' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $invoice) {
            $invoice->update([
                'status' => 'unpaid',
                'notes'  => $request->notes,
            ]);

            $orderIds = $invoice->orders()->pluck('orders.id')->all();
            if (empty($orderIds)) return;

            $payload = [];
            if (Schema::hasColumn('orders', 'komisi_lunas_at')) {
                $payload['komisi_lunas_at'] = null;
            }
            if (Schema::hasColumn('orders', 'komisi_lunas')) {
                $payload['komisi_lunas'] = null;
            }

            if (!empty($payload)) {
                Order::whereIn('id', $orderIds)->update($payload);
            }
        });

        return back()->with('success', 'Pembayaran ditolak. Invoice dikembalikan ke status unpaid.');
    }
}
