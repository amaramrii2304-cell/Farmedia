<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\CodSettlement;

class AdminCommissionController extends Controller
{
    public function index(Request $request)
    {
        // Komisi produk (untuk info di UI saja)
        $commissionRate = 0.10; // 10%

        // Ambil semua order selesai (seluruh mitra) + relasi
        $baseQuery = Order::with(['user', 'mitra', 'items'])
            ->where('status_order', 'selesai');

        // Untuk ringkasan & komisi per mitra (tanpa paginate)
        $allCompleted = (clone $baseQuery)->get();

        /**
         * ✅ 1) Hutang komisi (TOTAL) = selesai + cash + ambil di toko/pickup + komisi belum lunas
         *    Catatan: kita buat kompatibel untuk data yang kadang pakai "pickup" atau "ambil_di_toko"
         */
        $hutangOrders = $allCompleted->filter(function ($o) {
            $shipping = strtolower((string) $o->metode_pengiriman);
            $payment  = strtolower((string) $o->metode_pembayaran);

            $isPickup = in_array($shipping, ['pickup', 'ambil_di_toko']);
            $isCash   = ($payment === 'cash');

            $belumLunas = empty($o->komisi_lunas) || $o->komisi_lunas == false;

            return $isPickup && $isCash && $belumLunas;
        });

        // Total hutang komisi (produk + ongkir)
        $totalOutstanding = $hutangOrders->sum(function ($o) {
            return (float) $o->komisi_produk + (float) $o->komisi_ongkir;
        });

        // Total nilai produk (tanpa ongkir) = sum subtotal item
        $totalBruto = $allCompleted->sum(function ($order) {
            return $order->items->sum('subtotal');
        });

        // 🔥 PAKAI KOLOM KOMISI DI TABEL ORDERS
        $totalKomisiProduk = $allCompleted->sum('komisi_produk');
        $totalKomisiOngkir = $allCompleted->sum('komisi_ongkir');
        $totalCommission   = $totalKomisiProduk + $totalKomisiOngkir;

        // Uang bersih untuk semua mitra
        $totalNetForMitra = $allCompleted->sum(function ($o) {
            return $o->total_harga - ((float)$o->komisi_produk + (float)$o->komisi_ongkir);
        });

        // COD settlement (income admin)
        $codPlatformPaid = CodSettlement::query()
            ->where('status', 'paid')
            ->sum('platform_fee');

        $codServicePaid = CodSettlement::query()
            ->where('status', 'paid')
            ->sum('service_fee');

        $codAdminIncomePaid = (float)$codPlatformPaid + (float)$codServicePaid;

        /**
         * ✅ 2) Komisi per mitra + hutang per mitra (PAKAI KONDISI YANG SAMA)
         */
        $perMitra = $allCompleted
            ->groupBy('mitra_id')
            ->map(function ($orders, $mitraId) {

                $mitra = optional($orders->first()->mitra);

                $omzetProduk = $orders->sum(function ($order) {
                    return $order->items->sum('subtotal');
                });

                $komisiProduk = $orders->sum('komisi_produk');
                $komisiOngkir = $orders->sum('komisi_ongkir');
                $commission   = $komisiProduk + $komisiOngkir;

                // ✅ Hutang komisi per mitra: selesai + cash + pickup/ambil_di_toko + belum lunas
                $outstanding = $orders->filter(function ($o) {
                    $shipping = strtolower((string) $o->metode_pengiriman);
                    $payment  = strtolower((string) $o->metode_pembayaran);

                    $isPickup = in_array($shipping, ['pickup', 'ambil_di_toko']);
                    $isCash   = ($payment === 'cash');

                    $belumLunas = empty($o->komisi_lunas) || $o->komisi_lunas == false;

                    return $isPickup && $isCash && $belumLunas;
                })->sum(function ($o) {
                    return (float) $o->komisi_produk + (float) $o->komisi_ongkir;
                });

                return [
                    'mitra_id'      => $mitraId,
                    'nama_toko'     => $mitra->nama_toko ?? 'Mitra #' . $mitraId,
                    'omzet'         => $omzetProduk,
                    'komisi_produk' => $komisiProduk,
                    'komisi_ongkir' => $komisiOngkir,
                    'commission'    => $commission,
                    'outstanding'   => $outstanding,
                ];
            })
            ->values();

        // Daftar order terakhir + paginate untuk detail bawah
        $latestOrders = $baseQuery
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('admin.commissions.index', [
            'commissionRate'      => $commissionRate,
            'totalBruto'          => $totalBruto,
            'totalCommission'     => $totalCommission,
            'totalNetForMitra'    => $totalNetForMitra,
            'perMitra'            => $perMitra,
            'latestOrders'        => $latestOrders,
            'totalOutstanding'    => $totalOutstanding,
            'codPlatformPaid'     => $codPlatformPaid,
            'codServicePaid'      => $codServicePaid,
            'codAdminIncomePaid'  => $codAdminIncomePaid,
        ]);
    }
}
