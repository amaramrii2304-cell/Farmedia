<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdSubmission;
use App\Models\Product;
use Illuminate\Http\Request;

class AdApiController extends Controller
{
    /**
     * GET /api/ads
     * Query:
     * - placement: home | category (default: home)
     * - category: string (wajib jika placement=category)
     *
     * Return: List product shape mirip /products agar Android bisa pakai PublicProductResponse
     */
    public function index(Request $request)
    {
        $placement = strtolower($request->query('placement', 'home')); // home | category
        $category  = $request->query('category');                      // untuk category ads

        $now = now();

        $adsQuery = AdSubmission::where('status', 'active')
            ->whereRaw('LOWER(placement) = ?', [$placement])
            ->whereNotNull('start_at')->whereNotNull('end_at')
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now);

        // category ads: wajib ada category
        if ($placement === 'category') {
            if (empty($category)) {
                return response()->json([]);
            }

            $catLower = mb_strtolower($category);

            // ✅ FIX: dukung label chip Android seperti "Alat Olah Tanah"
            // dengan cara match dua arah + fallback key (alat/pupuk/benih/pestisida)
            $catKey = null;
            if (str_contains($catLower, 'pupuk')) $catKey = 'pupuk';
            elseif (str_contains($catLower, 'benih')) $catKey = 'benih';
            elseif (str_contains($catLower, 'pestisida')) $catKey = 'pestisida';
            elseif (str_contains($catLower, 'alat')) $catKey = 'alat';

            $adsQuery->where(function ($q) use ($category, $catLower, $catKey) {
                $q->where('target_kategori', $category)
                    ->orWhereRaw('LOWER(target_kategori) = ?', [$catLower])
                    ->orWhereRaw('LOWER(target_kategori) LIKE ?', ['%' . $catLower . '%'])

                    // ✅ request mengandung target_kategori (contoh: "alat olah tanah" mengandung "alat")
                    ->orWhereRaw('? LIKE CONCAT("%", LOWER(target_kategori), "%")', [$catLower]);

                if (!empty($catKey)) {
                    $q->orWhereRaw('LOWER(target_kategori) LIKE ?', ['%' . $catKey . '%']);
                }
            });
        }

        $productIds = $adsQuery->pluck('product_id')->unique()->values();

        if ($productIds->isEmpty()) {
            return response()->json([]);
        }

        $products = Product::with('mitra')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->whereIn('id', $productIds)
            ->get()
            ->values();

        $data = $products->map(function ($p) {
            $avg = $p->reviews_avg_rating ?? 0;

            return [
                'id'              => $p->id,
                'mitra_id'         => $p->mitra_id ?? null,
                'toko'            => optional($p->mitra)->nama_toko,
                'nama_produk'     => $p->nama_produk,
                'kategori_produk' => $p->kategori_produk,
                'deskripsi_produk'=> $p->deskripsi_produk,
                'harga'           => (string)($p->harga ?? ''),
                'stok'            => $p->stok ?? 0,
                'berat'           => $p->berat ?? null,
                'foto_produk'     => $p->foto_produk ?? null,
                'foto_url'        => $p->foto_url ?? null,
                'avg_rating'      => round((float)$avg, 1),
                'ulasan_count'    => (int)($p->reviews_count ?? 0),
                'created_at'      => optional($p->created_at)->toIso8601String(),
                'updated_at'      => optional($p->updated_at)->toIso8601String(),
            ];
        });

        return response()->json($data);
    }
}
