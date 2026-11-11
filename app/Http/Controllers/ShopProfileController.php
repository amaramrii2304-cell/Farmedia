<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopProfileController extends Controller
{
    public function show(Request $request, Mitra $mitra)
{
    $activeCat = $request->get('cat'); // kategori aktif
    $q         = $request->get('q');   // keyword search

    // Produk toko + hitung rating/ulasan per produk
    $products = Product::query()
        ->where('mitra_id', $mitra->id)
        ->when($activeCat, fn($qq) => $qq->where('kategori_produk', $activeCat))
        ->when($q, fn($qq) => $qq->where('nama_produk', 'like', '%' . $q . '%'))
        ->withAvg('reviews', 'rating')
        ->withCount('reviews')
        ->latest()
        ->paginate(12)
        ->withQueryString();

    // total produk toko (tanpa filter)
    $produkCount = Product::where('mitra_id', $mitra->id)->count();

    // Rating toko (gabungan semua review semua produk)
    $shopReviewsCount = Product::where('mitra_id', $mitra->id)
        ->join('product_reviews', 'products.id', '=', 'product_reviews.product_id')
        ->count();

    $shopAvgRating = Product::where('mitra_id', $mitra->id)
        ->join('product_reviews', 'products.id', '=', 'product_reviews.product_id')
        ->avg('product_reviews.rating');

    $shopAvgRating = $shopAvgRating ? round($shopAvgRating, 1) : 0;

    // Kategori tab (list kategori)
    $categories = Product::where('mitra_id', $mitra->id)
        ->select('kategori_produk')
        ->whereNotNull('kategori_produk')
        ->where('kategori_produk', '!=', '')
        ->distinct()
        ->orderBy('kategori_produk')
        ->pluck('kategori_produk');

    // Badge jumlah produk per kategori (tanpa filter)
    $categoryCounts = Product::where('mitra_id', $mitra->id)
        ->whereNotNull('kategori_produk')
        ->where('kategori_produk', '!=', '')
        ->selectRaw('kategori_produk, COUNT(*) as total')
        ->groupBy('kategori_produk')
        ->pluck('total', 'kategori_produk'); // hasil: ['Sayur' => 12, 'Buah' => 5]

    return view('user.toko.show', compact(
        'mitra',
        'products',
        'produkCount',
        'shopAvgRating',
        'shopReviewsCount',
        'categories',
        'categoryCounts',
        'activeCat',
        'q'
    ));
}
}
