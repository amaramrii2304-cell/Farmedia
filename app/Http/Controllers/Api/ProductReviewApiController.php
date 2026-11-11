<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class ProductReviewApiController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $user = $request->user();

        $data = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        // Kalau di web kamu hanya boleh 1 review per produk per user
        $exists = ProductReview::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memberi ulasan untuk produk ini'
            ], 409);
        }

        ProductReview::create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'rating'     => (int) $data['rating'],
            'comment'    => $data['comment'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ulasan berhasil dikirim'
        ]);
    }
}
