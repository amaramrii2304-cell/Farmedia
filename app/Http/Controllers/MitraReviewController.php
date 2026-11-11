<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductReview;

class MitraReviewController extends Controller
{
    public function index(Request $request)
    {
        $mitraId = Auth::user()->mitra_id;

        $q      = trim((string) $request->get('q', ''));
        $rating = $request->get('rating'); // 1..5 atau kosong

        // ===== Query utama (list ulasan) =====
        $reviewsQuery = ProductReview::query()
            ->with([
                'product:id,mitra_id,nama_produk,foto_produk,harga',
                'user:id,name',
            ])
            ->whereHas('product', function ($p) use ($mitraId) {
                $p->where('mitra_id', $mitraId);
            })
            ->when($rating, function ($qq) use ($rating) {
                $qq->where('rating', (int) $rating);
            })
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('comment', 'like', "%{$q}%")
                      ->orWhereHas('product', fn($p) => $p->where('nama_produk', 'like', "%{$q}%"))
                      ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$q}%"));
                });
            })
            ->orderByDesc('created_at');

        $reviews = $reviewsQuery->paginate(10)->withQueryString();

        // ===== Ringkasan statistik (untuk card di atas) =====
        $baseStats = ProductReview::query()
            ->whereHas('product', fn($p) => $p->where('mitra_id', $mitraId));

        $totalReviews = (clone $baseStats)->count();
        $avgRating    = (float) ((clone $baseStats)->avg('rating') ?? 0);

        $ratingCounts = (clone $baseStats)
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->toArray();

        $count5 = (int) ($ratingCounts[5] ?? 0);
        $count4 = (int) ($ratingCounts[4] ?? 0);
        $count3 = (int) ($ratingCounts[3] ?? 0);
        $count2 = (int) ($ratingCounts[2] ?? 0);
        $count1 = (int) ($ratingCounts[1] ?? 0);

        $lowCount = $count1 + $count2;

        return view('mitra.reviews.index', compact(
            'reviews',
            'q',
            'rating',
            'totalReviews',
            'avgRating',
            'count5',
            'count4',
            'count3',
            'count2',
            'count1',
            'lowCount',
        ));
    }
}
