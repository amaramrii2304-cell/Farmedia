<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\CartItem;

class CartController extends Controller
{
    // Tampilkan keranjang (WEB) -> ambil dari DB cart_items (sinkron API)
    public function index()
    {
        $userId = Auth::id();

        $items = CartItem::with('product')
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();

        // ✅ bentuk ulang agar sama seperti format SESSION lama
        // $cart[productId] = ['nama','harga','foto','qty','mitra_id','selected']
        $cart = [];
        foreach ($items as $it) {
            if (!$it->product) continue;

            $pid = $it->product_id;

            $cart[$pid] = [
                'nama' => $it->product->nama_produk,
                'harga' => $it->product->harga,
                'foto' => $it->product->foto_produk,
                'qty' => (int) ($it->qty ?? 1),
                'mitra_id' => $it->product->mitra_id,
                'selected' => (int) ($it->selected ?? 0),
            ];
        }

        return view('user.cart.index', compact('cart'));
    }

    // Tambah ke keranjang (WEB) -> DB cart_items
    public function add(Product $product)
    {
        $userId = Auth::id();

        $item = CartItem::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->first();

        if ($item) {
            $item->qty = ((int) $item->qty) + 1;
            // ✅ jangan paksa selected jadi 1
            if ($item->selected === null) $item->selected = 0;
        } else {
            $item = new CartItem();
            $item->user_id = $userId;
            $item->product_id = $product->id;
            $item->qty = 1;
            // ✅ default: tidak tercentang
            $item->selected = 0;
        }

        $item->save();

        return back()->with('success', 'Produk ditambahkan ke keranjang!');
    }

    // Hapus item dari keranjang (WEB) -> DB cart_items
    // Route kamu: POST /keranjang/remove/{id}
    // Dulu {id} itu product_id (key session). Jadi kita pertahankan: anggap {id} = product_id
    public function remove($id)
    {
        $userId = Auth::id();

        CartItem::where('user_id', $userId)
            ->where('product_id', (int) $id)
            ->delete();

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }
}
