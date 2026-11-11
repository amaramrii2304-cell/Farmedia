<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    /**
     * Halaman list notifikasi admin:
     * pesanan yang BUTUH perhatian admin.
     */
    public function index(Request $request)
    {
        $orders = Order::with(['user', 'mitra', 'items.product'])
            ->whereIn('status_order', [
                'menunggu_konfirmasi', // pesanan baru masuk
                'pending_cancel',      // pembeli ajukan pembatalan
                'siap_dikirim',        // mitra sudah siap kirim -> admin harus ship + generate resi
            ])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.notifications.index', compact('orders'));
    }

    /**
     * Endpoint count untuk bell icon
     */
    public function count()
    {
        $total = Order::whereIn('status_order', [
            'menunggu_konfirmasi',
            'pending_cancel',
            'siap_dikirim',
        ])->count();

        return response()->json(['total' => $total]);
    }
}
