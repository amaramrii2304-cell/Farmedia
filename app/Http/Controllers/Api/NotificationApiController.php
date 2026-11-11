<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotificationApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // ====== DETEKSI TABEL MITRA ======
        $mitraTable = null;
        if (Schema::hasTable('mitras')) $mitraTable = 'mitras';
        else if (Schema::hasTable('mitra')) $mitraTable = 'mitra';

        // kolom nama toko
        $mitraNameCol = null;
        if ($mitraTable) {
            if (Schema::hasColumn($mitraTable, 'nama_toko')) $mitraNameCol = 'nama_toko';
            else if (Schema::hasColumn($mitraTable, 'nama')) $mitraNameCol = 'nama';
            else if (Schema::hasColumn($mitraTable, 'name')) $mitraNameCol = 'name';
        }

        // ====== DETEKSI KOLOM NAMA PRODUK ======
        $productNameCol = null;
        if (Schema::hasTable('products')) {
            if (Schema::hasColumn('products', 'nama_produk')) $productNameCol = 'nama_produk';
            else if (Schema::hasColumn('products', 'nama')) $productNameCol = 'nama';
            else if (Schema::hasColumn('products', 'name')) $productNameCol = 'name';
        }

        // ====== SUBQUERY: PRODUK PER ORDER (AMAN DI STRICT GROUP BY) ======
        $productAgg = DB::table('order_items')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->select(
                'order_items.order_id',
                DB::raw(
                    $productNameCol
                        ? "GROUP_CONCAT(products.$productNameCol SEPARATOR ', ') as product_name"
                        : "'-' as product_name"
                )
            )
            ->groupBy('order_items.order_id');

        // ====== QUERY UTAMA ORDERS ======
        $q = DB::table('orders')
            ->leftJoinSub($productAgg, 'p', function ($join) {
                $join->on('p.order_id', '=', 'orders.id');
            });

        if ($mitraTable) {
            $q->leftJoin($mitraTable, "$mitraTable.id", '=', 'orders.mitra_id');
        }

        $rows = $q->where('orders.user_id', $user->id)
            ->select(
                'orders.id',
                'orders.status_order',
                'orders.created_at',
                'orders.updated_at',
                DB::raw("COALESCE(p.product_name, '-') as product_name"),
                DB::raw(
                    ($mitraTable && $mitraNameCol)
                        ? "COALESCE($mitraTable.$mitraNameCol, '-') as store_name"
                        : "'-' as store_name"
                )
            )
            ->orderByDesc('orders.id')
            ->limit(50)
            ->get();

        $data = $rows->map(function ($o) {
            $status = strtolower($o->status_order ?? 'menunggu_konfirmasi');

            $title = match ($status) {
                'selesai' => 'Pesanan Selesai',
                'diproses' => 'Pesanan Sedang diproses',
                'dikirim' => 'Pesanan Dikirim',
                'siap_diambil' => 'Pesanan Siap diambil',
                'dibatalkan' => 'Pesanan dibatalkan',
                'menunggu_pembatalan' => 'Menunggu Pembatalan',
                default => 'Menunggu Konfirmasi',
            };

            $message = match ($status) {
                'selesai' => 'Pesanan selesai. Terima kasih sudah berbelanja di Farmedia!',
                'diproses' => 'Mitra sedang menyiapkan pesanan Anda.',
                'dikirim' => 'Pesanan sedang dikirim.',
                'siap_diambil' => 'Pesanan siap diambil.',
                'dibatalkan' => 'Pesanan dibatalkan.',
                'menunggu_pembatalan' => 'Pesanan menunggu pembatalan.',
                default => 'Pesanan menunggu konfirmasi mitra.',
            };

            // ✅ samakan seperti web
            $orderNo = 'ORD-' . $o->id;

            // time_text sederhana
            $timeText = '';
            try {
                $dt = new \DateTime($o->updated_at ?: $o->created_at);
                $now = new \DateTime();
                $diff = $now->getTimestamp() - $dt->getTimestamp();
                if ($diff < 3600) $timeText = floor($diff / 60) . ' minutes ago';
                else if ($diff < 86400) $timeText = floor($diff / 3600) . ' hours ago';
                else $timeText = floor($diff / 86400) . ' days ago';
            } catch (\Throwable $e) {
                $timeText = '';
            }

            return [
                'id' => (string) $o->id,
                'title' => $title,
                'status' => strtoupper($status),

                // ✅ field buat Android (biar sama web)
                'order_no' => $orderNo,
                'store_name' => $o->store_name ?? '-',
                'product_name' => $o->product_name ?? '-',
                'message' => $message,
                'time_text' => $timeText,

                // (opsional kompatibilitas lama)
                'detail' => $orderNo . ' | ' . ($o->product_name ?? '-'),
                'store' => 'Toko : ' . ($o->store_name ?? '-'),
            ];
        });

        return response()->json(['data' => $data]);
    }
}
