<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\CodSettlement;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MitraSalesReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $mitraId = $user->mitra_id ?? null;

        if (!$mitraId) {
            abort(403, 'Akun ini tidak terhubung dengan mitra.');
        }

        // =========================
        // ✅ FIX 1: parse input tanggal web (dd/mm/yyyy) dan api (yyyy-mm-dd)
        // =========================
        $fromInput = $request->input('from');
        $toInput   = $request->input('to');

        $from = $this->parseDateFlexible($fromInput);
        $to   = $this->parseDateFlexible($toInput);

        if (!$from && !$to) {
            $from = now()->subDays(30)->toDateString();
            $to   = now()->toDateString();
        }

        $fromDt = $from ? Carbon::parse($from)->startOfDay() : now()->subDays(30)->startOfDay();
        $toDt   = $to   ? Carbon::parse($to)->endOfDay()     : now()->endOfDay();

        // =========================
        // ✅ FIX 2: status sukses samakan dengan API
        // =========================
        $successStatuses = ['selesai', 'completed', 'done', 'diterima', 'sudah_diterima'];

        // =========================
        // ✅ FIX 3: jangan pakai orders.mitra_id
        // Pakai relasi items.product -> products.mitra_id (sama seperti API)
        // dan range berdasarkan created_at (sama seperti API)
        // =========================
        $baseQuery = Order::with(['items.product', 'user', 'codSettlement'])
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->whereIn('status_order', $successStatuses)
            ->whereHas('items.product', function ($q) use ($mitraId) {
                $q->where('mitra_id', $mitraId);
            });

        // untuk summary + chart (ambil semua data dalam range)
        $allOrders = (clone $baseQuery)->get();

        // =========================
        // ✅ Summary: hitung hanya item yang product.mitra_id == mitraId
        // =========================
        $totalRevenue = $allOrders->sum(function ($order) use ($mitraId) {
            return $order->items
                ->filter(fn($it) => $it->product && (int)$it->product->mitra_id === (int)$mitraId)
                ->sum('subtotal');
        });

        $totalOrders = $allOrders->count();

        $totalItems = $allOrders->sum(function ($order) use ($mitraId) {
            return $order->items
                ->filter(fn($it) => $it->product && (int)$it->product->mitra_id === (int)$mitraId)
                ->sum('jumlah');
        });

        // =========================
        // ✅ Grafik: omzet per hari berdasarkan created_at
        // (lengkap per hari seperti sebelumnya)
        // =========================
        $period = CarbonPeriod::create($fromDt->copy()->startOfDay(), $toDt->copy()->startOfDay());

        $dailyMap = $allOrders
            ->groupBy(fn($o) => optional($o->created_at)->toDateString())
            ->map(function ($orders) use ($mitraId) {
                return $orders->sum(function ($o) use ($mitraId) {
                    return $o->items
                        ->filter(fn($it) => $it->product && (int)$it->product->mitra_id === (int)$mitraId)
                        ->sum('subtotal');
                });
            });

        $chartLabels = collect();
        $chartValues = collect();

        foreach ($period as $date) {
            $key = $date->toDateString();
            $chartLabels->push($date->format('d M'));
            $chartValues->push((float) ($dailyMap[$key] ?? 0));
        }

        // =========================
        // ✅ List transaksi (paginate)
        // =========================
        $orders = (clone $baseQuery)
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        // optional: tambahkan atribut qty/total mitra di setiap order (biar mudah di blade)
        $orders->getCollection()->transform(function ($order) use ($mitraId) {
            $mitraItems = $order->items
                ->filter(fn($it) => $it->product && (int)$it->product->mitra_id === (int)$mitraId);

            $order->mitra_qty_total = (int) $mitraItems->sum('jumlah');
            $order->mitra_total     = (int) round((float) $mitraItems->sum('subtotal'));
            $order->mitra_order_no  = 'ORD-' . $order->id;

            return $order;
        });

        // =========================
        // ✅ Pencairan COD (uang masuk rekening mitra dari admin)
        // FIX: jangan filter order.mitra_id (karena sering tidak ada / tidak sesuai),
        // filter lewat items.product.mitra_id
        // =========================
        $codPayoutTotal = CodSettlement::query()
            ->where('status', 'paid')
            ->whereBetween('payout_at', [$fromDt, $toDt])
            ->whereHas('order', function ($q) use ($mitraId) {
                $q->whereRaw('LOWER(metode_pembayaran) = ?', ['cod'])
                  ->whereHas('items.product', function ($qq) use ($mitraId) {
                      $qq->where('mitra_id', $mitraId);
                  });
            })
            ->sum('net_to_seller');

        $codPayoutCount = CodSettlement::query()
            ->where('status', 'paid')
            ->whereBetween('payout_at', [$fromDt, $toDt])
            ->whereHas('order', function ($q) use ($mitraId) {
                $q->whereRaw('LOWER(metode_pembayaran) = ?', ['cod'])
                  ->whereHas('items.product', function ($qq) use ($mitraId) {
                      $qq->where('mitra_id', $mitraId);
                  });
            })
            ->count();

        return view('mitra.sales.index', [
            'orders'         => $orders,
            'from'           => $from,
            'to'             => $to,
            'totalRevenue'   => $totalRevenue,
            'totalOrders'    => $totalOrders,
            'totalItems'     => $totalItems,
            'chartLabels'    => $chartLabels,
            'chartValues'    => $chartValues,
            'codPayoutTotal' => $codPayoutTotal,
            'codPayoutCount' => $codPayoutCount,
        ]);
    }

    private function parseDateFlexible(?string $s): ?string
    {
        if (!$s) return null;

        try {
            $s = trim($s);

            // yyyy-mm-dd
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
                return Carbon::createFromFormat('Y-m-d', $s)->toDateString();
            }

            // dd/mm/yyyy
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $s)) {
                return Carbon::createFromFormat('d/m/Y', $s)->toDateString();
            }

            // fallback
            return Carbon::parse($s)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
