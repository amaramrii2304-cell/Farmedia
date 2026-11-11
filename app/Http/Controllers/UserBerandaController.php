<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\OrderItem;
use App\Models\AdSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserBerandaController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $selectedCategory = $request->query('category'); // kategori yg dipilih di beranda

        $productsQuery = Product::with('mitra')
            // rata-rata rating & jumlah ulasan untuk tiap produk
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {

                    // Cari di kolom produk
                    $sub->where('nama_produk', 'like', "%{$q}%")
                        ->orWhere('deskripsi_produk', 'like', "%{$q}%")
                        ->orWhere('kategori_produk', 'like', "%{$q}%");

                })
                // Cari di tabel MITRA juga (relasi)
                ->orWhereHas('mitra', function ($m) use ($q) {
                    $m->where('nama_toko', 'like', "%{$q}%");
                });
            });

        // Tambah filter kategori kalau ada
        if ($selectedCategory) {
            $productsQuery->where('kategori_produk', $selectedCategory);
        }

        $products = $productsQuery
            ->orderBy('created_at', 'desc')
            ->paginate(18)
            ->withQueryString(); // supaya page tidak reset waktu search / filter

        // Ambil daftar kategori unik dari produk
        $categories = Product::select('kategori_produk')
            ->whereNotNull('kategori_produk')
            ->where('kategori_produk', '!=', '')
            ->distinct()
            ->orderBy('kategori_produk')
            ->pluck('kategori_produk');

        $now = now();

// iklan HOME
$homeAdProductIds = AdSubmission::where('status','active')
    ->where('placement','home')
    ->whereNotNull('start_at')->whereNotNull('end_at')
    ->where('start_at','<=',$now)
    ->where('end_at','>=',$now)
    ->pluck('product_id')
    ->unique()
    ->values();

$homeAds = Product::with('mitra')
    ->withAvg('reviews', 'rating')
    ->withCount('reviews')
    ->whereIn('id', $homeAdProductIds)
    ->get();

// iklan CATEGORY (hanya kalau ada selectedCategory)
$categoryAds = collect();
if ($selectedCategory) {
    $catAdProductIds = AdSubmission::where('status','active')
        ->where('placement','category')
        ->where('target_kategori', $selectedCategory)
        ->whereNotNull('start_at')->whereNotNull('end_at')
        ->where('start_at','<=',$now)
        ->where('end_at','>=',$now)
        ->pluck('product_id')
        ->unique()
        ->values();

    $categoryAds = Product::with('mitra')
        ->withAvg('reviews', 'rating')
        ->withCount('reviews')
        ->whereIn('id', $catAdProductIds)
        ->get();
}

        return view('user.beranda', [
            'products'         => $products,
            'q'                => $q,
            'categories'       => $categories,
            'selectedCategory' => $selectedCategory,
            'homeAds'          => $homeAds,
            'categoryAds'      => $categoryAds,
        ]);
    }

    public function show(Product $product)
{
    // load relasi yang dipakai view
    $product->load(['mitra', 'reviews.user', 'reviews']);

    // rating summary
    $avgRating = round((float) $product->reviews()->avg('rating'), 1);
    $reviewsCount = (int) $product->reviews()->count();

    // default
    $canReview = false;

    // hanya pembeli (role user)
    if (Auth::check() && Auth::user()->role === 'user') {
        $userId = Auth::id();

        // 1) harus pernah beli produk ini dan status order selesai
        $hasPurchased = OrderItem::where('product_id', $product->id)
            ->whereHas('order', function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->where('status_order', 'selesai');
            })
            ->exists();

        // 2) belum pernah review produk ini
        $alreadyReviewed = ProductReview::where('product_id', $product->id)
            ->where('user_id', $userId)
            ->exists();

        $canReview = $hasPurchased && !$alreadyReviewed;
    }

    return view('user.produk.show', compact(
        'product',
        'avgRating',
        'reviewsCount',
        'canReview'
    ));
    }
}
