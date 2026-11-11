<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommissionInvoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class MitraCommissionPaymentApiController extends Controller
{
    private function norm($s): string
    {
        return strtolower(trim((string) $s));
    }

    private function isPayAtStoreValue(string $pay): bool
    {
        $p = $this->norm($pay);

        if (in_array($p, [
            'cash',
            'cod',
            'tunai',
            'bayar_di_toko',
            'bayar di toko',
            'bayar ditempat',
            'bayar di tempat',
        ], true)) {
            return true;
        }

        if (str_contains($p, 'bayar') && (str_contains($p, 'toko') || str_contains($p, 'tempat'))) {
            return true;
        }

        return false;
    }

    /**
     * Hitung outstanding komisi:
     * - Prioritas: pakai kolom komisi_produk + komisi_ongkir
     * - Fallback: kalau komisi masih 0, hitung 10% dari subtotal produk (items)
     */
    private function computeOutstandingForOrder(Order $o, int $commissionPercent = 10): float
    {
        $komisiProduk = (float) ($o->komisi_produk ?? 0);
        $komisiOngkir = (float) ($o->komisi_ongkir ?? 0);
        $sum = $komisiProduk + $komisiOngkir;

        if ($sum > 0) return $sum;

        $o->loadMissing(['items']);
        $productSubtotal = 0.0;

        foreach ($o->items as $it) {
            $qty = (int) (
                $it->jumlah ??
                $it->qty ??
                $it->quantity ??
                0
            );

            $harga = (float) (
                $it->harga_satuan ??
                $it->price ??
                $it->harga ??
                0
            );

            $line = 0.0;
            if (isset($it->subtotal) && $it->subtotal !== null) $line = (float) $it->subtotal;
            else if (isset($it->total) && $it->total !== null) $line = (float) $it->total;
            else $line = $harga * $qty;

            $productSubtotal += $line;
        }

        if ($productSubtotal <= 0) {
            $total  = (float) ($o->total_harga ?? 0);
            $ongkir = (float) ($o->ongkir ?? 0);
            $productSubtotal = max(0, $total - $ongkir);
        }

        $komisi = (float) round($productSubtotal * ($commissionPercent / 100));
        return max(0, $komisi);
    }

    /**
     * Query outstanding orders (pickup + bayar di toko + selesai + komisi belum lunas)
     * ✅ patokan lunas: komisi_lunas_at IS NULL (fallback: komisi_lunas IS NULL kalau ada)
     */
    private function getOutstandingOrders(int $mitraId)
    {
        $hasLunasAt = Schema::hasColumn('orders', 'komisi_lunas_at');
        $hasLunas   = Schema::hasColumn('orders', 'komisi_lunas');

        return Order::query()
            ->where('mitra_id', $mitraId)
            ->where('status_order', 'selesai')
            ->where(function ($q) {
                $q->whereRaw("LOWER(COALESCE(metode_pengiriman,'')) IN ('pickup','ambil_di_toko','ambil di toko')")
                    ->orWhereRaw("LOWER(COALESCE(metode_pengiriman,'')) LIKE '%ambil%'")
                    ->orWhereRaw("LOWER(COALESCE(metode_pengiriman,'')) LIKE '%toko%'");
            })
            ->where(function ($q) {
                $q->whereRaw("LOWER(COALESCE(metode_pembayaran,'')) IN ('cash','cod','tunai','bayar_di_toko','bayar di toko','bayar ditempat','bayar di tempat')")
                    ->orWhereRaw("LOWER(COALESCE(metode_pembayaran,'')) LIKE '%bayar%toko%'")
                    ->orWhereRaw("LOWER(COALESCE(metode_pembayaran,'')) LIKE '%bayar%tempat%'");
            })
            ->where(function ($q) use ($hasLunasAt, $hasLunas) {
                if ($hasLunasAt) {
                    $q->whereNull('komisi_lunas_at');
                }
                if ($hasLunas) {
                    if ($hasLunasAt) $q->orWhereNull('komisi_lunas');
                    else $q->whereNull('komisi_lunas');
                }
            })
            ->get();
    }

    /**
     * GET /api/mitra/commission/invoice (atau /invoice/active)
     * Ambil invoice aktif (unpaid / waiting_verification).
     * Jika belum ada invoice, tapi hutang komisi ada -> auto buat invoice.
     * ✅ Jika invoice unpaid masih aktif (belum submit bukti), auto REFRESH agar menggabungkan semua hutang terbaru.
     */
    public function showActiveInvoice(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'mitra') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya mitra yang dapat mengakses data ini.',
                'data' => null,
            ], 403);
        }

        $mitraId = (int) $user->mitra_id;
        $hasOrderNo = Schema::hasColumn('orders', 'order_no');
        $commissionPercent = 10;

        // 1) Cari invoice yang dianggap "aktif" oleh mobile
        $invoice = CommissionInvoice::query()
            ->where('mitra_id', $mitraId)
            ->whereIn('status', ['unpaid', 'waiting_verification'])
            ->latest()
            ->first();

        // 2) Ambil semua order outstanding terbaru
        $outstandingOrders = $this->getOutstandingOrders($mitraId);

        // Hitung total outstanding (pakai fallback)
        $totalOutstanding = 0.0;
        foreach ($outstandingOrders as $o) {
            $totalOutstanding += $this->computeOutstandingForOrder($o, $commissionPercent);
        }

        // ✅ Kalau tidak ada hutang dan tidak ada invoice aktif -> null
        if (!$invoice && $totalOutstanding <= 0) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada invoice aktif.',
                'data' => ['active_invoice' => null],
            ]);
        }

        // ✅ Kalau invoice tidak ada tapi ada hutang -> buat invoice gabungan
        if (!$invoice && $totalOutstanding > 0) {
            $invoice = CommissionInvoice::create([
                'mitra_id' => $mitraId,
                'amount'   => (float) $totalOutstanding,
                'penalty'  => 0,
                'total_due' => (float) $totalOutstanding,
                'due_date' => now()->addDays(7),
                'status'   => 'unpaid',
                'payment_method' => null,
                'payment_proof_path' => null,
                'notes' => null,
                'paid_at' => null,
            ]);

            $invoice->orders()->sync($outstandingOrders->pluck('id')->values()->all());
        }

        // ✅ Kalau invoice ada status unpaid dan belum upload bukti => refresh biar selalu gabung semua hutang terbaru
        if ($invoice && $invoice->status === 'unpaid' && empty($invoice->payment_proof_path)) {

            // Kalau hutang sudah 0 (misal admin sudah approve tapi invoice lama masih nyangkut unpaid) => kembalikan null
            if ($totalOutstanding <= 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada invoice aktif.',
                    'data' => ['active_invoice' => null],
                ]);
            }

            $invoice->orders()->sync($outstandingOrders->pluck('id')->values()->all());
            $invoice->amount = (float) $totalOutstanding;

            if (!$invoice->due_date) {
                $invoice->due_date = now()->addDays(7);
            }

            $invoice->penalty = (float) ($invoice->penalty ?? 0);
            $invoice->total_due = (float) $invoice->amount; // snapshot minimal (penalty dihitung dinamis)

            $invoice->save();
        }

        // Load orders minimal (lebih ringan)
        $invoice->load(['orders' => function ($q) use ($hasOrderNo) {
            if ($hasOrderNo) {
                $q->select(
                    'orders.id',
                    'orders.order_no',
                    'orders.created_at',
                    'orders.metode_pembayaran',
                    'orders.metode_pengiriman',
                    'orders.komisi_produk',
                    'orders.komisi_ongkir',
                    'orders.total_harga',
                    'orders.ongkir'
                );
            } else {
                $q->select(
                    'orders.id',
                    'orders.created_at',
                    'orders.metode_pembayaran',
                    'orders.metode_pengiriman',
                    'orders.komisi_produk',
                    'orders.komisi_ongkir',
                    'orders.total_harga',
                    'orders.ongkir'
                );
            }
        }]);

        $penalty = method_exists($invoice, 'computedPenalty')
            ? (float) $invoice->computedPenalty()
            : (float) ($invoice->penalty ?? 0);

        $totalDue = method_exists($invoice, 'computedTotalDue')
            ? (float) $invoice->computedTotalDue()
            : (float) ($invoice->total_due ?? ((float) $invoice->amount + $penalty));

        $overdue = method_exists($invoice, 'isOverdue')
            ? (bool) $invoice->isOverdue()
            : ($invoice->due_date ? now()->gt($invoice->due_date) : false);

        $bank = [
            'bank_name'      => 'Riau Kepri Syariah (Perseroda)',
            'account_name'   => 'PT. FARMEDIA',
            'account_number' => '1083 1011 48',
        ];

        // orders list dalam invoice
        $orders = [];
        foreach (($invoice->orders ?? []) as $o) {
            // ✅ hitung hutang selalu pakai fallback (biar tidak 0)
            $komisi = (float) $this->computeOutstandingForOrder($o, $commissionPercent);

            // optional safety: hanya tampilkan bayar di toko
            if (!$this->isPayAtStoreValue((string) ($o->metode_pembayaran ?? ''))) {
                continue;
            }

            $safeOrderNo = null;
            if ($hasOrderNo && !empty($o->order_no)) {
                $safeOrderNo = $o->order_no;
            } else {
                $safeOrderNo = 'FM-' . str_pad((string) $o->id, 4, '0', STR_PAD_LEFT);
            }

            $orders[] = [
                'id' => $o->id,
                'order_no' => $safeOrderNo,
                'created_at' => $o->created_at ? $o->created_at->toISOString() : null,
                'date_text' => $o->created_at ? $o->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') : null,
                'outstanding_amount' => (float) $komisi,
            ];
        }

        $proofUrl = null;
        if (!empty($invoice->payment_proof_path)) {
            $proofUrl = Storage::url($invoice->payment_proof_path);
        }

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'active_invoice' => [
                    'id' => $invoice->id,
                    'status' => $invoice->status,
                    'due_date' => $invoice->due_date ? $invoice->due_date->toDateString() : null,
                    'overdue' => (bool) $overdue,

                    'amount' => (float) $invoice->amount,
                    'penalty' => (float) $penalty,
                    'total_due' => (float) $totalDue,

                    'payment_method' => $invoice->payment_method,
                    'notes' => $invoice->notes,
                    'payment_proof_url' => $proofUrl,
                    'updated_at' => $invoice->updated_at ? $invoice->updated_at->toISOString() : null,

                    'bank' => $bank,
                    'bank_name'         => $bank['bank_name'],
                    'bank_account_name' => $bank['account_name'],
                    'bank_account_no'   => $bank['account_number'],

                    'orders' => $orders,
                ],
            ],
        ]);
    }

    /**
     * POST /api/mitra/commission/invoice/{id}/submit-proof
     * Form-data:
     * - method (required)
     * - notes (optional)
     * - proof (required file jpg/png/pdf)
     */
    public function submitProof(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'mitra') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya mitra yang dapat mengakses data ini.',
                'data' => null,
            ], 403);
        }

        $mitraId = (int) $user->mitra_id;

        $request->validate([
            'method' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        // ✅ FIX: izinkan submit ulang saat waiting_verification / rejected
        $invoice = CommissionInvoice::query()
            ->where('id', (int) $id)
            ->where('mitra_id', $mitraId)
            ->whereIn('status', ['unpaid', 'waiting_verification', 'rejected'])
            ->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice tidak ditemukan / bukan milik mitra / status harus unpaid / waiting_verification / rejected.',
                'data' => null,
            ], 404);
        }

        $file = $request->file('proof');
        $path = $file->store('commission_proofs', 'public');

        if (!empty($invoice->payment_proof_path) && Storage::disk('public')->exists($invoice->payment_proof_path)) {
            Storage::disk('public')->delete($invoice->payment_proof_path);
        }

        $invoice->payment_method = $request->input('method');
        $invoice->notes = $request->input('notes');
        $invoice->payment_proof_path = $path;

        // ✅ tetap set waiting_verification (kalau sebelumnya unpaid/rejected)
        $invoice->status = 'waiting_verification';
        $invoice->paid_at = null;
        $invoice->save();

        return response()->json([
            'success' => true,
            'message' => 'Bukti pembayaran berhasil dikirim. Menunggu verifikasi admin.',
            'data' => [
                'invoice_id' => $invoice->id,
                'status' => $invoice->status,
                'payment_proof_url' => Storage::url($path),
            ],
        ]);
    }
}
