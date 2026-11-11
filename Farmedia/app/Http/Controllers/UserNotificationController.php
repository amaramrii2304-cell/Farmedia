<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserNotificationController extends Controller
{
    /**
     * Halaman notifikasi pembeli.
     * Sumber data: tabel orders (REAL), bukan dummy.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Ambil maksimal 20 pesanan terakhir milik user ini
        $orders = Order::with(['mitra', 'items.product'])
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->take(20)
            ->get();

        return view('user.notifications.index', compact('orders'));
    }
}
