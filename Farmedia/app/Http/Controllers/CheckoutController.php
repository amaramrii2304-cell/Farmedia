<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function show(Product $product)
    {
        return view('user.checkout.show', [
            'product' => $product,
            'user'    => Auth::user(),
        ]);
    }

    public function store(Request $request, Product $product)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'jumlah'             => 'required|integer|min:1|max:' . $product->stok,
            'alamat_pengiriman'  => 'required|string|max:500',
            'metode_pengiriman'  => 'required|in:delivery,pickup',
            'metode_pembayaran'  => 'required|string',
            'ongkir'             => 'nullable|integer|min:0'
        ]);

        // VALIDASI KOMBINASI
        if (
            $validated['metode_pengiriman'] === 'pickup' &&
            !in_array($validated['metode_pembayaran'], ['cash', 'transfer', 'qris'])
        ) {
            return back()->withErrors(['metode_pembayaran' => 'COD tidak diperbolehkan untuk pickup.']);
        }

        if (
            $validated['metode_pengiriman'] === 'delivery' &&
            !in_array($validated['metode_pembayaran'], ['cod', 'transfer', 'qris'])
        ) {
            return back()->withErrors(['metode_pembayaran' => 'Metode pembayaran tidak valid.']);
        }

        $jumlah   = $validated['jumlah'];
        $subtotal = $product->harga * $jumlah;
        $ongkir   = $validated['metode_pengiriman'] === 'pickup' ? 0 : ($validated['ongkir'] ?? 0);
        $total    = $subtotal + $ongkir;

        $komisiProduk = $subtotal * 0.10;
        $komisiOngkir = $ongkir > 0 ? $ongkir * 0.05 : 0;

        // =====================
        // PAYMENT STATUS FIX
        // =====================
        $paymentStatus   = match ($validated['metode_pembayaran']) {
            'transfer' => 'pending',
            'cod'      => 'cod',
            'cash'     => 'paid',
            'qris'     => 'paid',
            default    => 'pending',
        };

        $paymentDeadline = $validated['metode_pembayaran'] === 'transfer'
            ? now()->addDay()
            : null;

        $order = Order::create([
            'user_id'            => $user->id,
            'mitra_id'           => $product->mitra_id,
            'total_harga'        => $total,
            'ongkir'             => $ongkir,
            'metode_pembayaran'  => $validated['metode_pembayaran'],
            'alamat_pengiriman'  => $validated['alamat_pengiriman'],
            'metode_pengiriman'  => $validated['metode_pengiriman'],
            'status_order'       => 'menunggu_konfirmasi',
            'payment_status'     => $paymentStatus,
            'payment_deadline'   => $paymentDeadline,
            'komisi_produk'      => $komisiProduk,
            'komisi_ongkir'      => $komisiOngkir,
        ]);

        OrderItem::create([
            'order_id'     => $order->id,
            'product_id'   => $product->id,
            'jumlah'       => $jumlah,
            'harga_satuan' => $product->harga,
            'subtotal'     => $subtotal,
        ]);

        $product->decrement('stok', $jumlah);

        return redirect()->route('orders.index')
            ->with('success', 'Pesanan berhasil dibuat!');
    }

    // =============================
    // CART CHECKOUT (DISINKRONKAN)
    // =============================
    public function processCart(Request $request)
    {
        $validated = $request->validate([
            'alamat_pengiriman' => 'required|string|max:500',
            'metode_pengiriman' => 'required|in:delivery,pickup',
            'metode_pembayaran' => 'required|string',
            'ongkir'            => 'nullable|integer|min:0',
            'selected_ids'      => 'required|array',
        ]);

        $cart  = session('cart', []);
        $items = [];

        foreach ($validated['selected_ids'] as $id) {
            if (isset($cart[$id])) {
                $items[$id] = $cart[$id];
            }
        }

        if (empty($items)) {
            return back()->with('error', 'Keranjang tidak valid.');
        }

        if (
            $validated['metode_pengiriman'] === 'pickup' &&
            !in_array($validated['metode_pembayaran'], ['cash', 'transfer', 'qris'])
        ) {
            return back()->withErrors(['metode_pembayaran' => 'COD tidak diperbolehkan untuk pickup.']);
        }

        if (
            $validated['metode_pengiriman'] === 'delivery' &&
            !in_array($validated['metode_pembayaran'], ['cod', 'transfer', 'qris'])
        ) {
            return back()->withErrors(['metode_pembayaran' => 'Metode pembayaran tidak valid.']);
        }

        $barangTotal = collect($items)->sum(fn ($i) => $i['qty'] * $i['harga']);
        $ongkir      = $validated['metode_pengiriman'] === 'pickup' ? 0 : ($validated['ongkir'] ?? 0);
        $total       = $barangTotal + $ongkir;

        $paymentStatus = match ($validated['metode_pembayaran']) {
            'transfer' => 'pending',
            'cod'      => 'cod',
            'cash'     => 'paid',
            'qris'     => 'paid',
            default    => 'pending',
        };

        $paymentDeadline = $validated['metode_pembayaran'] === 'transfer'
            ? now()->addDay()
            : null;

        $firstProduct = Product::find(array_key_first($items));

        $order = Order::create([
            'user_id'           => auth()->id(),
            'mitra_id'          => $firstProduct?->mitra_id,
            'total_harga'       => $total,
            'ongkir'            => $ongkir,
            'metode_pembayaran' => $validated['metode_pembayaran'],
            'alamat_pengiriman' => $validated['alamat_pengiriman'],
            'metode_pengiriman' => $validated['metode_pengiriman'],
            'status_order'      => 'menunggu_konfirmasi',
            'payment_status'    => $paymentStatus,
            'payment_deadline'  => $paymentDeadline,
            'komisi_produk'     => $barangTotal * 0.10,
            'komisi_ongkir'     => $ongkir > 0 ? $ongkir * 0.05 : 0,
        ]);

        foreach ($items as $productId => $item) {
            OrderItem::create([
                'order_id'     => $order->id,
                'product_id'   => $productId,
                'jumlah'       => $item['qty'],
                'harga_satuan' => $item['harga'],
                'subtotal'     => $item['qty'] * $item['harga'],
            ]);

            Product::where('id', $productId)->decrement('stok', $item['qty']);
            unset($cart[$productId]);
        }

        session()->put('cart', $cart);

        return redirect()->route('orders.index')
            ->with('success', 'Pesanan berhasil dibuat.');
    }
}
