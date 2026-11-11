<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\CommissionInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class MitraCommissionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'mitra') {
            abort(403, 'Hanya mitra yang dapat mengakses halaman ini.');
        }

        $mitraId = (int) $user->mitra_id;

        // Komisi produk (buat keterangan di UI saja)
        $commissionPercent = 10;

        // =========================
        // ✅ NORMALISASI & HELPER (SAMA DENGAN API)
        // =========================
        $norm = function ($s): string {
            return strtolower(trim((string) $s));
        };

        $isPickupOrder = function ($order) use ($norm): bool {
            $m = $norm($order->metode_pengiriman ?? '');
            return in_array($m, ['pickup', 'ambil_di_toko', 'ambil di toko'], true)
                || str_contains($m, 'ambil')
                || str_contains($m, 'toko');
        };

        $isPayAtStore = function ($order) use ($norm): bool {
            $p = $norm($order->metode_pembayaran ?? '');
            if (in_array($p, ['cash', 'cod', 'tunai', 'bayar_di_toko', 'bayar di toko', 'bayar ditempat', 'bayar di tempat'], true)) {
                return true;
            }
            if (str_contains($p, 'bayar') && (str_contains($p, 'toko') || str_contains($p, 'tempat'))) {
                return true;
            }
            return false;
        };

        // ✅ FALLBACK hitung komisi kalau komisi di DB masih 0 (SAMA DENGAN API)
        $computeCommission = function ($order) use ($commissionPercent): array {
            $komisiProduk = (float) ($order->komisi_produk ?? 0);
            $komisiOngkir = (float) ($order->komisi_ongkir ?? 0);

            // kalau sudah ada, pakai yang ada
            if (($komisiProduk + $komisiOngkir) > 0) {
                return [$komisiProduk, $komisiOngkir];
            }

            // hitung dari item jika ada
            $order->loadMissing(['items']);

            $productSubtotal = 0.0;

            if ($order->relationLoaded('items') && $order->items) {
                foreach ($order->items as $it) {
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
            }

            // fallback terakhir: total_harga - ongkir
            if ($productSubtotal <= 0) {
                $total  = (float) ($order->total_harga ?? 0);
                $ongkir = (float) ($order->ongkir ?? 0);
                $productSubtotal = max(0, $total - $ongkir);
            }

            $komisiProduk = (float) round($productSubtotal * ($commissionPercent / 100));

            // untuk pickup, komisi ongkir biasanya 0 (ikut nilai di DB)
            $komisiOngkir = (float) ($order->komisi_ongkir ?? 0);

            return [$komisiProduk, $komisiOngkir];
        };

        // =======================
        // Periode default: 30 hari terakhir
        // =======================
        $to   = $request->input('to');
        $from = $request->input('from');
        $q    = trim((string) $request->input('q', ''));

        if (!$from || !$to) {
            $to   = now()->toDateString();
            $from = now()->subDays(30)->toDateString();
        }

        // Query dasar: order selesai milik mitra ini, dalam periode
        $baseQuery = Order::with(['user', 'items.product'])
            ->where('mitra_id', $mitraId)
            ->where('status_order', 'selesai')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        // ✅ Search seperti API (opsional)
        $hasOrderNo = Schema::hasColumn('orders', 'order_no');

        if ($q !== '') {
            $baseQuery->where(function ($qq) use ($q, $hasOrderNo) {

                if ($hasOrderNo) {
                    $qq->where('order_no', 'like', "%{$q}%");
                }

                if (is_numeric($q)) {
                    $qq->orWhere('id', (int) $q);
                } else {
                    if (!$hasOrderNo) {
                        $qq->orWhere('id', -999999);
                    }
                }

                $qq->orWhereHas('user', function ($u) use ($q) {
                    $u->where('name', 'like', "%{$q}%");
                });

                $qq->orWhereHas('items.product', function ($p) use ($q) {
                    $p->where('nama_produk', 'like', "%{$q}%");
                });
            });
        }

        // Ambil invoice aktif (kalau ada)
        $activeInvoice = CommissionInvoice::query()
            ->where('mitra_id', $mitraId)
            ->whereIn('status', ['unpaid', 'waiting_verification'])
            ->latest()
            ->first();

        $invoiceStatus   = $activeInvoice?->status;
        $invoicePenalty  = 0;
        $invoiceTotalDue = 0;
        $invoiceDueDate  = $activeInvoice?->due_date;
        $invoiceOverdue  = false;

        if ($activeInvoice) {
            if (method_exists($activeInvoice, 'computedPenalty')) {
                $invoicePenalty  = (float) $activeInvoice->computedPenalty();
                $invoiceTotalDue = (float) $activeInvoice->computedTotalDue();
                $invoiceOverdue  = (bool) $activeInvoice->isOverdue();
            } else {
                $invoicePenalty  = (float) ($activeInvoice->penalty ?? 0);
                $invoiceTotalDue = (float) ($activeInvoice->total_due ?? ((float)$activeInvoice->amount + $invoicePenalty));
                $invoiceOverdue  = $activeInvoice->due_date ? now()->gt($activeInvoice->due_date) : false;
            }
        }

        // Untuk daftar di layar (paginate)
        $orders = (clone $baseQuery)
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        // 🔥 (Opsional tapi berguna) Tambahkan field computed agar tampilan Web ikut “hasil hitung API”
        $orders->getCollection()->transform(function ($order) use ($computeCommission, $isPickupOrder, $isPayAtStore) {
            [$kp, $ko] = $computeCommission($order);
            $komisi = (float) $kp + (float) $ko;

            $productTotal = max(0, (float)$order->total_harga - (float)$order->ongkir);

            $isPickup = $isPickupOrder($order);
            $isCash   = $isPayAtStore($order);

            // ✅ FIX: patokan lunas: komisi_lunas_at ATAU komisi_lunas
            $paidAt = $order->komisi_lunas_at ?? $order->komisi_lunas ?? null;
            $belumLunas = empty($paidAt);

            $isOutstanding = $isPickup && $isCash && $belumLunas;

            // pasang property dinamis (aman untuk Blade)
            $order->computed_product_value    = (float) $productTotal;
            $order->computed_komisi_produk    = (float) $kp;
            $order->computed_komisi_ongkir    = (float) $ko;
            $order->computed_platform_fee     = (float) $komisi;
            $order->computed_net_income       = (float) ((float)$order->total_harga - $komisi);
            $order->computed_is_outstanding   = (bool) $isOutstanding;
            $order->computed_outstanding_amt  = (float) ($isOutstanding ? $komisi : 0);

            return $order;
        });

        // Untuk ringkasan total
        $allOrders = $baseQuery->get();

        // Hutang komisi = pickup + bayar di toko + komisi belum lunas
        $outstandingOrders = $allOrders->filter(function ($o) use ($isPickupOrder, $isPayAtStore) {
            $isPickup = $isPickupOrder($o);
            $isCash   = $isPayAtStore($o);

            // ✅ FIX: patokan lunas: komisi_lunas_at ATAU komisi_lunas
            $paidAt = $o->komisi_lunas_at ?? $o->komisi_lunas ?? null;
            $belumLunas = empty($paidAt);

            return $isPickup && $isCash && $belumLunas;
        });

        // ✅ totalOutstanding pakai fallback computeCommission (SAMA DENGAN API)
        $totalOutstanding = (float) $outstandingOrders->sum(function ($o) use ($computeCommission) {
            [$kp, $ko] = $computeCommission($o);
            return (float) $kp + (float) $ko;
        });

        if ($activeInvoice) {
            $totalOutstanding = (float) $invoiceTotalDue; // hutang ikut invoice aktif
        }

        $totalOrdersCount = $allOrders->count();

        // nilai produk = total - ongkir
        $totalProduct = (float) $allOrders->sum(function ($order) {
            return max(0, (float)$order->total_harga - (float)$order->ongkir);
        });

        // ✅ total komisi pakai fallback computeCommission (SAMA DENGAN API)
        $totalCommission = (float) $allOrders->sum(function ($order) use ($computeCommission) {
            [$kp, $ko] = $computeCommission($order);
            return (float) $kp + (float) $ko;
        });

        // ✅ pendapatan bersih (total - komisi) pakai fallback (SAMA DENGAN API)
        $totalNet = (float) $allOrders->sum(function ($order) use ($computeCommission) {
            [$kp, $ko] = $computeCommission($order);
            return (float)$order->total_harga - ((float)$kp + (float)$ko);
        });

        return view('mitra.commission.index', compact(
            'orders',
            'from',
            'to',
            'q',
            'commissionPercent',
            'totalProduct',
            'totalCommission',
            'totalNet',
            'totalOrdersCount',
            'totalOutstanding',
            // ===== tambahan untuk UI bayar hutang =====
            'activeInvoice',
            'invoiceStatus',
            'invoicePenalty',
            'invoiceTotalDue',
            'invoiceDueDate',
            'invoiceOverdue',
        ));
    }
}
