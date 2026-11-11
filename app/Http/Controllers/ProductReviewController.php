<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductReviewController extends Controller
{
    /**
     * Simpan review baru dari pembeli.
     */
    public function store(Request $request, Product $product)
    {
        $user = Auth::user();

        // Hanya role "user" (pembeli)
        if ($user->role !== 'user') {
            abort(403, 'Hanya pembeli yang dapat memberi ulasan.');
        }

        // Validasi input
        $data = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        // Cek: user pernah beli produk ini dan status pesanan "selesai"
        $hasPurchased = OrderItem::where('product_id', $product->id)
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)   // <--- DI SINI diganti
                  ->where('status_order', 'selesai');
            })
            ->exists();

        if (! $hasPurchased) {
            return back()->with('error', 'Anda hanya bisa mengulas produk yang sudah pernah Anda beli dan selesai.');
        }

        // Cek: user sudah pernah review produk ini belum
        $alreadyReviewed = ProductReview::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyReviewed) {
            return back()->with('error', 'Anda sudah pernah memberikan ulasan untuk produk ini.');
        }

        // Ambil salah satu order "selesai" untuk disimpan sebagai referensi
        $orderItem = OrderItem::where('product_id', $product->id)
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)   // <--- DI SINI juga diganti
                  ->where('status_order', 'selesai');
            })
            ->orderByDesc('id')
            ->first();

        ProductReview::create([
            'product_id' => $product->id,
            'user_id'    => $user->id,
            'order_id'   => $orderItem?->order_id,
            'rating'     => $data['rating'],
            'comment'    => $data['comment'] ?? null,
        ]);

        return back()->with('success', 'Terima kasih, ulasan Anda berhasil disimpan.');
    }
}
