<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\ProductReview;
use App\Models\User;
use App\Models\Mitra;


class OrderController extends Controller
{
    public function index()
    {
        // Ambil user yang sedang login
        $user = Auth::user();

        // Ambil semua pesanan milik user, urut terbaru
        $orders = Order::with(['items.product'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // arahkan ke resources/views/user/orders/index.blade.php
        return view('user.orders.index', compact('orders'));
    }
    public function show(Order $order)
{
    // Pastikan ini pesanan milik user yang login
    if ($order->user_id !== Auth::id()) {
        abort(403);
    }

    $order->load('items.product', 'mitra', 'user');

    // Ambil review milik user untuk produk dalam pesanan ini
    $userReviews = ProductReview::where('user_id', Auth::id())
        ->whereIn('product_id', $order->items->pluck('product_id'))
        ->get()
        ->keyBy('product_id'); // nanti bisa dipakai: $userReviews[$item->product_id]

    return view('user.orders.show', compact('order', 'userReviews'));
}

    public function nextStep(Order $order)
{
    // Flow delivery
    $deliveryFlow = ['menunggu_konfirmasi', 'diproses', 'dikirim', 'selesai'];

    // Flow pickup
    $pickupFlow = ['menunggu_konfirmasi', 'diproses', 'siap_diambil', 'selesai'];

    $flow = $order->metode_pengiriman === 'pickup'
            ? $pickupFlow
            : $deliveryFlow;

    $currentIndex = array_search($order->status_order, $flow);

    if ($currentIndex < count($flow) - 1) {
        $order->status_order = $flow[$currentIndex + 1];
        $order->save();
    }

    return back()->with('success', 'Status pesanan diperbarui!');
}

public function cancel(Order $order)
{
    // Pastikan user pemilik pesanan
    if ($order->user_id !== Auth::id()) {
        abort(403);
    }

    // Kalau status bukan menunggu_konfirmasi / diproses → tidak boleh ajukan cancel lagi
    if (!in_array($order->status_order, ['menunggu_konfirmasi', 'diproses'])) {
        return back()->with('error', 'Pesanan tidak dapat dibatalkan pada status saat ini.');
    }

    // Simpan status lama (buat kalau penjual menolak)
    $order->update([
        'status_order_before' => $order->status_order,
        'status_order'        => 'pending_cancel',
    ]);

    return back()->with('success', 'Permintaan pembatalan telah dikirim ke penjual.');
}

public function updateAddress(Request $request, Order $order)
{
    if ($order->user_id !== Auth::id()) {
        abort(403);
    }

    $request->validate([
        'alamat_pengiriman' => 'required|string|max:500'
    ]);

    // Hanya pesanan yang BELUM dikirim boleh ubah alamat
    if (in_array($order->status_order, ['menunggu_konfirmasi', 'diproses'])) {
        $order->alamat_pengiriman = $request->alamat_pengiriman;
        $order->save();

        return back()->with('success', 'Alamat pengiriman berhasil diperbarui.');
    }

    return back()->with('error', 'Alamat tidak dapat diubah pada status saat ini.');
}

public function requestCancel(Order $order)
{
    // pastikan pemilik
    if ($order->user_id !== auth()->id()) {
        abort(403);
    }

    // hanya bisa minta cancel kalau belum dikirim
    if (! in_array($order->status_order, ['menunggu_konfirmasi','diproses'])) {
        return back()->with('error', 'Pesanan tidak dapat dibatalkan.');
    }

    // simpan status lama + ubah ke pending_cancel
    $order->update([
        'status_order_before' => $order->status_order,
        'status_order'        => 'pending_cancel',
    ]);

    return back()->with('success', 'Permintaan pembatalan telah dikirim ke penjual.');
}


}
