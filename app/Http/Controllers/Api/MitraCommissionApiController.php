<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\CommissionInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class MitraCommissionApiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'mitra') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya mitra yang dapat mengakses data komisi.',
                'data'    => null,
            ], 403);
        }

        $mitraId = (int) $user->mitra_id;

        // keterangan UI saja
        $commissionPercent = 10;

        // =========================
        // ✅ NORMALISASI & HELPER
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

        // ✅ FALLBACK hitung komisi kalau komisi di DB masih 0
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

            // untuk pickup, komisi ongkir biasanya 0
            $komisiOngkir = (float) ($order->komisi_ongkir ?? 0);

            return [$komisiProduk, $komisiOngkir];
        };

        // Query param: from/to/q (YYYY-MM-DD)
        $to   = $request->query('to');
        $from = $request->query('from');
        $q    = trim((string) $request->query('q', ''));

        // Default 30 hari terakhir
        if (!$from || !$to) {
            $to   = now()->toDateString();
            $from = now()->subDays(30)->toDateString();
        }

        $perPage = (int) $request->query('per_page', 10);
        if ($perPage <= 0) $perPage = 10;
        if ($perPage > 100) $perPage = 100;

        // Query dasar
        $baseQuery = Order::with(['user', 'items.product'])
            ->where('mitra_id', $mitraId)
            ->where('status_order', 'selesai')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        // ✅ cek kolom order_no ada atau tidak
        $hasOrderNo = Schema::hasColumn('orders', 'order_no');

        // Search q: order_no (kalau ada) / id / nama pembeli / nama produk
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

        // Invoice aktif (jika ada)
        $activeInvoice = CommissionInvoice::query()
            ->where('mitra_id', $mitraId)
            ->whereIn('status', ['unpaid', 'waiting_verification'])
            ->latest()
            ->first();

        $invoice = null;
        if ($activeInvoice) {
            $penalty  = 0.0;
            $totalDue = 0.0;
            $overdue  = false;

            if (method_exists($activeInvoice, 'computedPenalty')) {
                $penalty  = (float) $activeInvoice->computedPenalty();
                $totalDue = (float) $activeInvoice->computedTotalDue();
                $overdue  = (bool)  $activeInvoice->isOverdue();
            } else {
                $penalty  = (float) ($activeInvoice->penalty ?? 0);
                $totalDue = (float) ($activeInvoice->total_due ?? ((float)$activeInvoice->amount + $penalty));
                $overdue  = $activeInvoice->due_date ? now()->gt($activeInvoice->due_date) : false;
            }

            $invoice = [
                'id'        => $activeInvoice->id,
                'status'    => $activeInvoice->status,
                'amount'    => (float) $activeInvoice->amount,
                'penalty'   => (float) $penalty,
                'total_due' => (float) $totalDue,
                'due_date'  => $activeInvoice->due_date ? $activeInvoice->due_date->toDateString() : null,
                'overdue'   => (bool) $overdue,
            ];
        }

        // List (paginate)
        $ordersPage = (clone $baseQuery)
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        // Summary (mengikuti filter)
        $allOrders = $baseQuery->get();

        // Hutang komisi = pickup + bayar di toko + komisi belum lunas (patokan: komisi_lunas_at ATAU komisi_lunas)
        $outstandingOrders = $allOrders->filter(function ($o) use ($isPickupOrder, $isPayAtStore) {
            $isPickup = $isPickupOrder($o);
            $isCash   = $isPayAtStore($o);

            $paidAt = $o->komisi_lunas_at ?? $o->komisi_lunas ?? null;
            $belumLunas = empty($paidAt);

            return $isPickup && $isCash && $belumLunas;
        });

        // totalOutstanding pakai fallback computeCommission
        $totalOutstanding = (float) $outstandingOrders->sum(function ($o) use ($computeCommission) {
            [$kp, $ko] = $computeCommission($o);
            return (float) $kp + (float) $ko;
        });

        // ✅ kalau ada invoice aktif, hutang yang ditampilkan sebaiknya ikut invoice (yang akan dibayar)
        if ($invoice) {
            $totalOutstanding = (float) $invoice['total_due']; // atau amount kalau mau tanpa denda
        }

        $totalOrdersCount = $allOrders->count();

        // nilai produk = total - ongkir
        $totalProduct = (float) $allOrders->sum(function ($order) {
            return max(0, (float)$order->total_harga - (float)$order->ongkir);
        });

        // total komisi pakai fallback computeCommission
        $totalCommission = (float) $allOrders->sum(function ($order) use ($computeCommission) {
            [$kp, $ko] = $computeCommission($order);
            return (float) $kp + (float) $ko;
        });

        // pendapatan bersih (total - komisi)
        $totalNet = (float) $allOrders->sum(function ($order) use ($computeCommission) {
            [$kp, $ko] = $computeCommission($order);
            return (float)$order->total_harga - ((float)$kp + (float)$ko);
        });

        // Map orders untuk Android
        $mappedOrders = $ordersPage->getCollection()->map(function ($order) use ($hasOrderNo, $isPickupOrder, $isPayAtStore, $computeCommission) {
            [$komisiProduk, $komisiOngkir] = $computeCommission($order);
            $komisi = (float)$komisiProduk + (float)$komisiOngkir;

            $productTotal = max(0, (float)$order->total_harga - (float)$order->ongkir);

            $isPickup = $isPickupOrder($order);
            $isCash   = $isPayAtStore($order);

            $paidAt = $order->komisi_lunas_at ?? $order->komisi_lunas ?? null;
            $belumLunas = empty($paidAt);

            $isOutstanding = $isPickup && $isCash && $belumLunas;

            $items = [];
            if ($order->relationLoaded('items')) {
                foreach ($order->items as $it) {
                    $items[] = [
                        'product_name' => $it->product->nama_produk ?? ($it->nama_produk ?? 'Produk dihapus'),
                        'qty'          => (int) ($it->jumlah ?? $it->qty ?? $it->quantity ?? 0),
                    ];
                }
            }

            // ✅ order_no aman
            $safeOrderNo = null;
            if ($hasOrderNo && !empty($order->order_no)) {
                $safeOrderNo = $order->order_no;
            } else {
                $safeOrderNo = 'FM-' . str_pad((string)$order->id, 4, '0', STR_PAD_LEFT);
            }

            $paidAtIso = null;
            if (!empty($paidAt)) {
                $paidAtIso = $paidAt instanceof \Carbon\Carbon ? $paidAt->toISOString() : (string) $paidAt;
            }

            return [
                'id' => $order->id,
                'order_no' => $safeOrderNo,

                'created_at' => $order->created_at ? $order->created_at->toISOString() : null,
                'date_text'  => $order->created_at ? $order->created_at->format('d/m/Y, H:i') : '-',

                'buyer_name' => $order->user->name ?? '-',
                'metode_pembayaran' => $order->metode_pembayaran,
                'metode_pengiriman' => $order->metode_pengiriman,

                'total_harga'    => (float) $order->total_harga,
                'ongkir'         => (float) $order->ongkir,
                'product_value'  => (float) $productTotal,

                'komisi_produk'  => (float) $komisiProduk,
                'komisi_ongkir'  => (float) $komisiOngkir,
                'platform_fee'   => (float) $komisi,
                'net_income'     => (float) ((float)$order->total_harga - $komisi),

                // ✅ FIX: lunas dari komisi_lunas_at ATAU komisi_lunas
                'komisi_lunas'    => !empty($paidAt),
                'komisi_lunas_at' => $paidAtIso,

                'is_outstanding'     => (bool) $isOutstanding,
                'outstanding_amount' => (float) ($isOutstanding ? $komisi : 0),

                'items' => $items,
            ];
        });

        $ordersPage->setCollection($mappedOrders);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'commission_percent' => $commissionPercent,
                'from' => $from,
                'to' => $to,
                'q' => $q,
                'summary' => [
                    'total_product'       => (float) $totalProduct,
                    'total_commission'    => (float) $totalCommission,
                    'total_outstanding'   => (float) $totalOutstanding,
                    'total_net'           => (float) $totalNet,
                    'total_orders_count'  => (int) $totalOrdersCount,
                ],
                'active_invoice' => $invoice,
                'orders' => $ordersPage,
            ],
        ]);
    }
}
    