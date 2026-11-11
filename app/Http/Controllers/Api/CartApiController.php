<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Product;

class CartApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user(); // auth:sanctum
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $items = CartItem::with('product')
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($it) {
                return [
                    'id' => $it->id,
                    'product_id' => $it->product_id,
                    'qty' => (int) ($it->qty ?? 1),

                    // ✅ default kalau null: false (biar pas buka keranjang tidak auto ceklis)
                    'selected' => (bool) ($it->selected ?? 0),

                    'product' => $it->product,
                ];
            });

        return response()->json(['data' => $items]);
    }

    public function add(Request $request, Product $product)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $item = CartItem::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($item) {
            $item->qty = ((int) $item->qty) + 1;
            // ✅ jangan ubah selected kalau item sudah ada
        } else {
            $item = new CartItem();
            $item->user_id = $user->id;
            $item->product_id = $product->id;
            $item->qty = 1;

            // ✅ item baru default: tidak diceklis
            $item->selected = 0;
        }

        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Produk ditambahkan ke keranjang',
            'data' => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'qty' => (int) $item->qty,
                'selected' => (bool) ($item->selected ?? 0),
            ]
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $item = CartItem::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item keranjang tidak ditemukan'], 404);
        }

        if ($request->has('qty')) {
            $qty = (int) $request->input('qty');
            $item->qty = max(1, $qty);
        }

        if ($request->has('selected')) {
            // ✅ paling aman untuk boolean dari JSON
            $item->selected = $request->boolean('selected') ? 1 : 0;
        }

        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Keranjang diperbarui',
            'data' => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'qty' => (int) $item->qty,
                'selected' => (bool) ($item->selected ?? 0),
            ]
        ]);
    }

    public function remove(Request $request, Product $product)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        CartItem::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item dihapus dari keranjang'
        ]);
    }

    public function clear(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        CartItem::where('user_id', $user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Keranjang dikosongkan'
        ]);
    }
}
