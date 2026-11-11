<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Order;

class AdminOrderApiController extends Controller
{
    private function ensureAdmin(): void
    {
        $user = Auth::user();
        $role = strtolower(trim((string) ($user->role ?? '')));
        if ($role !== 'admin') {
            abort(403, 'Forbidden (admin only)');
        }
    }

    // ✅ izinkan admin ATAU mitra pemilik order
    private function ensureAdminOrOwnerMitra(Order $order): void
    {
        $user = Auth::user();
        $role = strtolower(trim((string) ($user->role ?? '')));

        if ($role === 'admin') return;

        $mitraId = (int) ($user->mitra_id ?? 0);
        if ($mitraId > 0 && (int) $order->mitra_id === $mitraId) return;

        abort(403, 'Forbidden');
    }

    private function norm($s): string
    {
        return strtolower(trim((string) $s));
    }

    private function isDelivery(Order $order): bool
    {
        $m = $this->norm($order->metode_pengiriman);
        return str_contains($m, 'dikirim') || str_contains($m, 'alamat') || $m === 'delivery';
    }

    private function isPickup(Order $order): bool
    {
        $m = $this->norm($order->metode_pengiriman);
        return str_contains($m, 'ambil') || str_contains($m, 'toko') || $m === 'pickup';
    }

    private function isPayAtStore(Order $order): bool
    {
        $p = $this->norm($order->metode_pembayaran);

        if (in_array($p, [
            'cash',
            'cod',
            'tunai',
            'bayar_di_toko',
            'bayar di toko',
            'bayar ditempat',
            'bayar di tempat'
        ], true)) return true;

        if (str_contains($p, 'bayar') && (str_contains($p, 'toko') || str_contains($p, 'tempat'))) return true;

        return false;
    }

    private function isTransfer(Order $order): bool
    {
        $p = $this->norm($order->metode_pembayaran);
        return str_contains($p, 'transfer') || $p === 'transfer';
    }

    private function transferColumnsExist(): bool
    {
        return Schema::hasColumn('orders', 'transfer_status')
            && Schema::hasColumn('orders', 'transfer_deadline_at')
            && Schema::hasColumn('orders', 'transfer_proof_path');
    }

    /**
     * ✅ Saat pickup + bayar di toko, sistem harus catat "utang komisi"
     * Aman: kalau relasi items tidak ada -> fallback total-ongkir (biar tidak 500)
     */
    private function ensureDebtForPayAtStore(Order $order, int $commissionPercent = 10): void
    {
        if (!$this->isPickup($order)) return;
        if (!$this->isPayAtStore($order)) return;

        $productSubtotal = 0;

        try {
            if (method_exists($order, 'items')) {
                $order->loadMissing(['items']);

                foreach ($order->items as $it) {
                    $qty = (int) ($it->jumlah ?? 0);
                    $harga = (int) ($it->harga_satuan ?? 0);

                    $line = isset($it->subtotal) && $it->subtotal !== null
                        ? (int) $it->subtotal
                        : ($harga * $qty);

                    $productSubtotal += $line;
                }
            } else {
                throw new \RuntimeException("Order model has no 'items' relation");
            }
        } catch (\Throwable $e) {
            if (Schema::hasTable('order_items')) {
                $rows = DB::table('order_items')->where('order_id', $order->id)->get();
                foreach ($rows as $it) {
                    $qty = (int) (($it->jumlah ?? $it->qty) ?? 0);
                    $harga = (int) (($it->harga_satuan ?? $it->price) ?? 0);

                    $line = isset($it->subtotal) && $it->subtotal !== null
                        ? (int) $it->subtotal
                        : ($harga * $qty);

                    $productSubtotal += $line;
                }
            }
        }

        if ($productSubtotal <= 0) {
            $total = (int) ($order->total_harga ?? 0);
            $ongkir = (int) ($order->ongkir ?? 0);
            $productSubtotal = max(0, $total - $ongkir);
        }

        $komisiProduk = (int) round($productSubtotal * ($commissionPercent / 100));

        $update = [];

        if (Schema::hasColumn('orders', 'komisi_produk')) {
            $update['komisi_produk'] = $komisiProduk;
        }

        if (Schema::hasColumn('orders', 'komisi_ongkir')) {
            $update['komisi_ongkir'] = (int) ($order->komisi_ongkir ?? 0);
        }

        if (Schema::hasColumn('orders', 'komisi_lunas')) {
            $update['komisi_lunas'] = null;
        }

        if (!empty($update)) {
            $order->update($update);
        }
    }

    // =========================
    // ✅ ADMIN: MARK COMPLETED (sama seperti kamu)
    // =========================
    public function markCompleted(Order $order)
    {
        $this->ensureAdminOrOwnerMitra($order);

        $current = $this->norm($order->status_order);

        if ($this->isDelivery($order)) {
            if (!in_array($current, ['dikirim', 'shipped', 'shipping', 'siap_dikirim', 'ready_to_ship'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selesai (delivery) hanya bisa dari status dikirim / siap_dikirim.',
                ], 422);
            }
        } elseif ($this->isPickup($order)) {
            if (!in_array($current, ['siap_diambil', 'siap diambil', 'ready_pickup'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selesai (pickup) hanya bisa dari status siap_diambil.',
                ], 422);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Metode pengiriman tidak dikenali.',
            ], 422);
        }

        $order->update(['status_order' => 'selesai']);

        $this->ensureDebtForPayAtStore($order, 10);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan ditandai selesai.',
            'data' => [
                'order_id' => (int) $order->id,
                'status_order' => $order->status_order,
            ],
        ]);
    }

    /**
     * ✅ ADMIN VERIFIKASI PEMBAYARAN TRANSFER
     * POST /api/admin/orders/{order}/verify-transfer-payment
     * body: { action: "approve"|"reject", notes?: "..." }
     *
     * Pakai transfer_* sesuai migration/DB kamu.
     */
    public function verifyTransferPayment(Request $request, Order $order)
    {
        $this->ensureAdmin();

        if (!$this->isTransfer($order)) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini bukan metode TRANSFER.',
            ], 422);
        }

        if (!$this->transferColumnsExist()) {
            return response()->json([
                'success' => false,
                'message' => 'Kolom transfer_* belum tersedia (transfer_status, transfer_deadline_at, transfer_proof_path).',
            ], 500);
        }

        $request->validate([
            'action' => 'required|string|in:approve,reject',
            'notes'  => 'nullable|string|max:5000',
        ]);

        $action = $this->norm($request->input('action'));
        $notes  = $request->input('notes');

        if (empty($order->transfer_proof_path) && $action === 'approve') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada bukti transfer yang diupload.',
            ], 422);
        }

        $update = [
            'transfer_status' => $action === 'approve' ? 'verified' : 'rejected',
        ];

        if (Schema::hasColumn('orders', 'transfer_notes')) {
            $update['transfer_notes'] = $notes;
        }

        if ($action === 'approve') {
            if (Schema::hasColumn('orders', 'transfer_verified_at')) $update['transfer_verified_at'] = now();
            if (Schema::hasColumn('orders', 'transfer_rejected_at')) $update['transfer_rejected_at'] = null;
        } else {
            if (Schema::hasColumn('orders', 'transfer_rejected_at')) $update['transfer_rejected_at'] = now();
            if (Schema::hasColumn('orders', 'transfer_verified_at')) $update['transfer_verified_at'] = null;
        }

        $order->update($update);

        return response()->json([
            'success' => true,
            'message' => $action === 'approve'
                ? 'Pembayaran disetujui (verified).'
                : 'Pembayaran ditolak (rejected).',
            'data' => [
                'order_id' => (int) $order->id,
                'transfer_status' => (string) $order->transfer_status,
            ],
        ]);
    }

    // printResi kamu biarkan seperti yang sudah ada (tidak terkait transfer)
    public function printResi(Order $order)
    {
        $this->ensureAdmin();

        // (isi printResi kamu tidak aku ubah)
        return response()->json([
            'success' => false,
            'message' => 'printResi tidak diubah di perbaikan transfer ini (pakai versi kamu).'
        ], 501);
    }
}
