<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MitraDashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1) Ambil user login + mitra_id
        $user = Auth::user();
        $mitraId = $user?->mitra_id;

        // Validasi akses mitra
        if (!$mitraId) {
            // kalau dipanggil via API, balas JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'User bukan Mitra'
                ], 403);
            }

            abort(403, 'Akses ditolak. Anda bukan Mitra.');
        }

        // Pakai 1 "now" supaya konsisten
        $now = Carbon::now();

        // 2) Statistik utama (riil DB)
        $totalSales = Order::where('mitra_id', $mitraId)
            ->where('status_order', 'selesai')
            ->sum('total_harga');

        $totalOrders = Order::where('mitra_id', $mitraId)
            ->where('status_order', 'selesai')
            ->count();

        $activeProducts = Product::where('mitra_id', $mitraId)
            ->where('stok', '>', 0)
            ->count();

        $commissionPercent = 10;
        $netRevenue = $totalSales * (1 - ($commissionPercent / 100));

        $totalQty = Product::where('mitra_id', $mitraId)->sum('stok');

        // Tanggal hari ini (untuk header)
        $periodText = $now->translatedFormat('d F Y');

        // 3) Data grafik 7 hari (selalu isi 7 titik, walau 0)
        $chartLabels = [];
        $chartSales  = [];
        $chartOrders = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);

            $chartLabels[] = $date->translatedFormat('D');

            $sales = Order::where('mitra_id', $mitraId)
                ->where('status_order', 'selesai')
                ->whereDate('created_at', $date)
                ->sum('total_harga');

            $ordersCount = Order::where('mitra_id', $mitraId)
                ->where('status_order', 'selesai')
                ->whereDate('created_at', $date)
                ->count();

            $chartSales[]  = (int) $sales;
            $chartOrders[] = (int) $ordersCount;
        }

        // 4) Pesanan menunggu konfirmasi
        // NOTE: items_count akan 0 kalau tabel order_items kosong
        $pendingOrders = Order::with(['user', 'items'])
            ->where('mitra_id', $mitraId)
            ->where('status_order', 'menunggu_konfirmasi')
            ->latest()
            ->get();

        // 5) Kalau dipanggil dari API -> JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'user_name'       => $user->name,

                    'total_sales'     => 'Rp ' . number_format($totalSales, 0, ',', '.'),
                    'total_orders'    => $totalOrders . ' Pesanan',
                    'active_products' => $activeProducts . ' Produk',
                    'net_revenue'     => 'Rp ' . number_format($netRevenue, 0, ',', '.'),
                    'total_qty'       => number_format($totalQty, 0, ',', '.'),
                    'period_text'     => $periodText,

                    // ✅ data grafik untuk Android
                    'chart_labels'    => $chartLabels,
                    'chart_sales'     => $chartSales,
                    'chart_orders'    => $chartOrders,

                    // ✅ pending orders (items_count FIX pakai "jumlah")
                    'pending_orders'  => $pendingOrders->map(function ($order) {
                        return [
                            'order_number'  => '#' . $order->id,
                            'customer_name' => $order->user->name ?? 'Pembeli',
                            'total_price'   => 'Rp ' . number_format($order->total_harga, 0, ',', '.'),
                            'items_count'   => ((int) ($order->items?->sum('jumlah') ?? 0)) . ' Item',
                            'time_ago'      => $order->created_at->diffForHumans(),
                        ];
                    })->values(),
                ],
            ]);
        }

        // 6) Kalau untuk WEB (Blade)
        return view('mitra.dashboard', compact(
            'user',
            'totalSales',
            'totalOrders',
            'activeProducts',
            'netRevenue',
            'pendingOrders',
            'periodText',
            'totalQty',
            'commissionPercent',
            'chartLabels',
            'chartSales',
            'chartOrders'
        ));
    }
}
