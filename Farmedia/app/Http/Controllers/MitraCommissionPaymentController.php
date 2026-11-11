<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\CommissionInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MitraCommissionPaymentController extends Controller
{
    private int $commissionPercent = 10;

    private function norm($s): string
    {
        return strtolower(trim((string) $s));
    }

    private function isPickupOrderValue(string $ship): bool
    {
        $m = $this->norm($ship);
        return in_array($m, ['pickup', 'ambil_di_toko', 'ambil di toko'], true)
            || str_contains($m, 'ambil')
            || str_contains($m, 'toko');
    }

    private function isPayAtStoreValue(string $pay): bool
    {
        $p = $this->norm($pay);

        if (in_array($p, [
            'cash', 'cod', 'tunai',
            'bayar_di_toko', 'bayar di toko',
            'bayar ditempat', 'bayar di tempat',
        ], true)) {
            return true;
        }

        return str_contains($p, 'bayar') && (str_contains($p, 'toko') || str_contains($p, 'tempat'));
    }

    /**
     * Sama seperti API: pakai komisi DB kalau ada, kalau 0 -> hitung 10% dari subtotal produk (items).
     */
    private function computeOutstandingForOrder(Order $o): float
    {
        $kp = (float) ($o->komisi_produk ?? 0);
        $ko = (float) ($o->komisi_ongkir ?? 0);
        $sum = $kp + $ko;

        if ($sum > 0) return $sum;

        $o->loadMissing(['items']);
        $productSubtotal = 0.0;

        foreach ($o->items as $it) {
            $qty = (int) ($it->jumlah ?? $it->qty ?? $it->quantity ?? 0);
            $harga = (float) ($it->harga_satuan ?? $it->price ?? $it->harga ?? 0);

            $line = 0.0;
            if (isset($it->subtotal) && $it->subtotal !== null) $line = (float) $it->subtotal;
            else if (isset($it->total) && $it->total !== null) $line = (float) $it->total;
            else $line = $harga * $qty;

            $productSubtotal += $line;
        }

        if ($productSubtotal <= 0) {
            $productSubtotal = max(0, (float)$o->total_harga - (float)$o->ongkir);
        }

        return (float) round($productSubtotal * ($this->commissionPercent / 100));
    }

    private function outstandingOrdersQuery(int $mitraId)
    {
        return Order::query()
            ->where('mitra_id', $mitraId)
            ->where('status_order', 'selesai')
            // ✅ pickup fleksibel (sama API)
            ->where(function ($q) {
                $q->whereRaw("LOWER(COALESCE(metode_pengiriman,'')) IN ('pickup','ambil_di_toko','ambil di toko')")
                  ->orWhereRaw("LOWER(COALESCE(metode_pengiriman,'')) LIKE '%ambil%'")
                  ->orWhereRaw("LOWER(COALESCE(metode_pengiriman,'')) LIKE '%toko%'");
            })
            // ✅ bayar di toko fleksibel (sama API)
            ->where(function ($q) {
                $q->whereRaw("LOWER(COALESCE(metode_pembayaran,'')) IN ('cash','cod','tunai','bayar_di_toko','bayar di toko','bayar ditempat','bayar di tempat')")
                  ->orWhereRaw("LOWER(COALESCE(metode_pembayaran,'')) LIKE '%bayar%toko%'")
                  ->orWhereRaw("LOWER(COALESCE(metode_pembayaran,'')) LIKE '%bayar%tempat%'");
            })
            // ✅ FIX: lunas pakai komisi_lunas_at
            ->whereNull('komisi_lunas_at');
    }

    private function getOrCreateActiveInvoice(int $mitraId): ?CommissionInvoice
    {
        $active = CommissionInvoice::query()
            ->where('mitra_id', $mitraId)
            ->whereIn('status', ['unpaid', 'waiting_verification'])
            ->latest()
            ->first();

        // kalau masih ada aktif, pakai itu
        if ($active) return $active;

        $orders = $this->outstandingOrdersQuery($mitraId)->with('items')->get();
        if ($orders->isEmpty()) return null;

        // ✅ amount pakai fallback hitung (bukan sum kolom DB doang)
        $amount = (float) $orders->sum(fn($o) => $this->computeOutstandingForOrder($o));

        // due date: max(selesai_at) + 3 hari (fallback updated_at/created_at)
        $maxFinish = $orders->max(fn($o) => $o->selesai_at ?? $o->updated_at ?? $o->created_at);
        $dueDate = ($maxFinish ? \Carbon\Carbon::parse($maxFinish) : now())->addDays(3);

        return DB::transaction(function () use ($mitraId, $orders, $amount, $dueDate) {
            $invoice = CommissionInvoice::create([
                'mitra_id'  => $mitraId,
                'amount'    => (float) $amount,
                'penalty'   => 0,
                'total_due' => (float) $amount,
                'due_date'  => $dueDate,
                'status'    => 'unpaid',
            ]);

            $invoice->orders()->attach($orders->pluck('id')->all());
            return $invoice;
        });
    }

    // GET /mitra/komisi/bayar
    public function payForm()
    {
        $mitraId = (int) Auth::user()->mitra_id;

        $invoice = $this->getOrCreateActiveInvoice($mitraId);

        if (!$invoice) {
            return redirect()->route('mitra.commission.index')
                ->with('success', 'Tidak ada hutang komisi yang perlu dibayar.');
        }

        // update snapshot penalty+total_due supaya fix saat mitra submit
        $penalty = method_exists($invoice, 'computedPenalty') ? (float)$invoice->computedPenalty() : (float)($invoice->penalty ?? 0);
        $invoice->update([
            'penalty'   => (float) $penalty,
            'total_due' => (float) $invoice->amount + (float) $penalty,
        ]);

        $invoice->load(['orders.items.product']);

        // ✅ inject computed hutang per order agar blade tidak pakai kolom DB yang bisa 0
        $invoice->orders->transform(function ($o) {
            $o->computed_hutang = (float) $this->computeOutstandingForOrder($o);
            return $o;
        });

        $bank = [
            'nama'    => 'Riau Kepri Syariah (Perseroda)',
            'pemilik' => 'PT. FARMEDIA',
            'nomor'   => '1083 1011 48',
        ];

        return view('mitra.commission.pay', compact('invoice', 'bank'));
    }

    // POST /mitra/komisi/bayar
    public function submitPayment(Request $request)
    {
        $mitraId = (int) Auth::user()->mitra_id;

        $invoice = CommissionInvoice::query()
            ->where('mitra_id', $mitraId)
            ->whereIn('status', ['unpaid', 'waiting_verification'])
            ->latest()
            ->firstOrFail();

        $request->validate([
            'payment_method' => ['required', 'in:transfer'],
            'payment_proof'  => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        $path = $request->file('payment_proof')->store('commission_proofs', 'public');

        $penalty = method_exists($invoice, 'computedPenalty') ? (float)$invoice->computedPenalty() : (float)($invoice->penalty ?? 0);
        $totalDue = (float)$invoice->amount + (float)$penalty;

        $invoice->update([
            'payment_method'     => $request->payment_method,
            'payment_proof_path' => $path,
            'notes'              => $request->notes,
            'penalty'            => $penalty,
            'total_due'          => $totalDue,
            'status'             => 'waiting_verification',
        ]);

        return redirect()->route('mitra.commission.index')
            ->with('success', 'Bukti pembayaran terkirim. Menunggu verifikasi admin.');
    }
}
