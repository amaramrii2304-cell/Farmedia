<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MitraReviewApiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $mitraId = $user?->mitra_id;

        if (!$mitraId) {
            return response()->json([
                'success' => false,
                'message' => 'User bukan Mitra'
            ], 403);
        }

        $q      = trim((string) $request->query('q', ''));
        $rating = $request->query('rating'); // 1..5 atau null

        // Produk milik mitra
        $productsQuery = Product::query()->where('mitra_id', $mitraId);

        if ($q !== '') {
            $productsQuery->where('nama_produk', 'like', "%{$q}%");
        }

        $productIds = $productsQuery->pluck('id')->values();

        if ($productIds->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'avg_rating' => 0.0,
                    'total_reviews' => 0,
                    'total_5_star' => 0,
                    'need_attention_count' => 0, // 1-2★
                    'distribution' => [
                        'r5' => 0, 'r4' => 0, 'r3' => 0, 'r2' => 0, 'r1' => 0
                    ],
                    'items' => [],
                ],
            ]);
        }

        // Semua review untuk produk-produk mitra
        $allReviewsQuery = ProductReview::query()->whereIn('product_id', $productIds);

        $totalReviews = (int) $allReviewsQuery->count();
        $avgRating    = (float) ($totalReviews > 0 ? $allReviewsQuery->avg('rating') : 0);

        // Distribution sekali query
        $dist = ProductReview::query()
            ->selectRaw('rating, COUNT(*) as c')
            ->whereIn('product_id', $productIds)
            ->groupBy('rating')
            ->pluck('c', 'rating');

        $r1 = (int) ($dist[1] ?? 0);
        $r2 = (int) ($dist[2] ?? 0);
        $r3 = (int) ($dist[3] ?? 0);
        $r4 = (int) ($dist[4] ?? 0);
        $r5 = (int) ($dist[5] ?? 0);

        // ✅ PERLU PERHATIKAN (1–2★) sesuai card Laravel
        $needAttentionCount = $r1 + $r2;

        // Ambil produk + agregasi review
        // ✅ DEFAULT: hanya produk yang SUDAH ADA ULASAN
        $rows = Product::query()
            ->where('mitra_id', $mitraId)
            ->when($q !== '', fn($qq) => $qq->where('nama_produk', 'like', "%{$q}%"))
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->having('reviews_count', '>', 0)
            ->orderByDesc('reviews_count')
            ->get();

        // Filter rating (kalau user pilih 1..5)
        if (!empty($rating)) {
            $ratingInt = (int) $rating;

            // Produk yang punya minimal 1 review rating tsb
            $productIdsByRating = ProductReview::query()
                ->whereIn('product_id', $productIds)
                ->where('rating', $ratingInt)
                ->pluck('product_id')
                ->unique()
                ->values();

            $rows = $rows->filter(fn($p) => $productIdsByRating->contains($p->id))->values();
        }

        // ✅ Ambil review TERAKHIR per produk (sekali query, bukan N+1)
        $lastReviews = ProductReview::query()
            ->whereIn('product_id', $rows->pluck('id')->values())
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('product_id')
            ->map(fn($g) => $g->first());

        $list = $rows->map(function ($p) use ($lastReviews) {
            $cnt = (int) ($p->reviews_count ?? 0);
            $avg = (float) ($p->reviews_avg_rating ?? 0);

            $lastReview = $lastReviews->get($p->id);

            // ✅ Paksa WIB agar sama seperti tampilan Laravel
            $createdLocal = $lastReview?->created_at
                ? $lastReview->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i')
                : null;

            // buyer_name fallback:
            // kalau kolom buyer_name tidak ada, kamu bisa pakai relasi user (optional)
            $buyerName = $lastReview?->buyer_name ?? (method_exists($lastReview, 'user') ? optional($lastReview->user)->name : null);

            return [
                'product_id' => $p->id,
                'product_name' => $p->nama_produk,
                'foto_url' => $p->foto_url ?? null,

                // untuk list card
                'avg_product_rating' => $cnt > 0 ? round($avg, 1) : 0.0,
                'total_product_reviews' => $cnt,

                // ✅ info ulasan terakhir
                'buyer_name' => $buyerName,
                'created_at' => $createdLocal, // <- string WIB, aman untuk Android
                'rating' => $lastReview?->rating ?? null,
                'comment' => $lastReview?->comment ?? null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'avg_rating' => round($avgRating, 1),
                'total_reviews' => $totalReviews,
                'total_5_star' => $r5,

                // ✅ PERLU PERHATIKAN (1–2★)
                'need_attention_count' => $needAttentionCount,

                'distribution' => [
                    'r5' => $r5,
                    'r4' => $r4,
                    'r3' => $r3,
                    'r2' => $r2,
                    'r1' => $r1,
                ],
                'items' => $list,
            ],
        ]);
    }
}
