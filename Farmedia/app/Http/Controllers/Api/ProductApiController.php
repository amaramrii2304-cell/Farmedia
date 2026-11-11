<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Mitra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductApiController extends Controller
{
    private function transformProduct(Product $p): array
    {
        return [
            'id' => $p->id,
            'mitra_id' => $p->mitra_id,
            'toko' => optional($p->mitra)->nama_toko,
            'nama_produk' => $p->nama_produk,
            'kategori_produk' => $p->kategori_produk,
            'deskripsi_produk' => $p->deskripsi_produk,
            'harga' => (string) $p->harga,
            'stok' => $p->stok,
            'berat' => $p->berat,

            // raw path (buat admin/mitra kalau perlu)
            'foto_produk' => $p->foto_produk,

            // ✅ ini yang dipakai Android untuk load gambar
            'foto_url' => $p->foto_url,

            'avg_rating' => isset($p->avg_rating) ? (float) $p->avg_rating : null,
            'ulasan_count' => isset($p->ulasan_count) ? (int) $p->ulasan_count : 0,

            'created_at' => $p->created_at,
            'updated_at' => $p->updated_at,
        ];
    }

    // PUBLIC - /api/products
    public function index(Request $request)
    {
        // 1. Mulai Query dengan relasi dan statistik rating
        $query = Product::with('mitra:id,nama_toko')
            ->withAvg('reviews as avg_rating', 'rating')
            ->withCount('reviews as ulasan_count');

        // 2. ✅ FIXED: LOGIKA FILTER KATEGORI (Mendukung filter dari Android)
        if ($request->filled('kategori')) {
            $kategori = trim($request->query('kategori'));
            
            // Gunakan whereRaw LOWER agar tidak sensitif huruf besar/kecil (Benih vs benih)
            $query->whereRaw('LOWER(kategori_produk) = ?', [strtolower($kategori)]);
        }

        // 3. Logika Pencarian Nama Produk (Opsional)
        if ($request->filled('search')) {
            $search = strtolower($request->query('search'));
            $query->whereRaw('LOWER(nama_produk) LIKE ?', ["%{$search}%"]);
        }

        // 4. ✅ FILTER STOK: Pastikan hanya produk yang tersedia yang muncul di pembeli
        $query->where('stok', '>', 0);

        // 5. Ambil data dengan urutan terbaru
        $products = $query->latest()->get();

        return response()->json($products->map(fn ($p) => $this->transformProduct($p)));
    }

    // PUBLIC - /api/products/{product}
    public function show(Product $product)
    {
        $product = Product::with('mitra:id,nama_toko')
            ->withAvg('reviews as avg_rating', 'rating')
            ->withCount('reviews as ulasan_count')
            ->where('id', $product->id)
            ->firstOrFail();

        return response()->json($this->transformProduct($product));
    }

    // PUBLIC - /api/products/{product}/reviews
    public function reviews(Product $product)
    {
        $rows = $product->reviews()
            ->with(['user:id,name'])
            ->latest()
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'user_name' => optional($r->user)->name,
                    'rating' => (float) ($r->rating ?? 0),
                    'komentar' => $r->comment,
                    'created_at' => $r->created_at ? $r->created_at->format('Y-m-d H:i:s') : null,
                ];
            });

        return response()->json($rows);
    }

    // PUBLIC - /api/mitra/{mitraId}/products
    public function productsByMitraPublic(Request $request, $mitraId)
    {
        $products = Product::with('mitra:id,nama_toko')
            ->withAvg('reviews as avg_rating', 'rating')
            ->withCount('reviews as ulasan_count')
            ->where('mitra_id', (int) $mitraId)
            ->latest()
            ->get();

        return response()->json($products->map(fn ($p) => $this->transformProduct($p)));
    }

    // PUBLIC - /api/mitra/{mitraId}
    public function mitraDetailPublic($mitraId)
    {
        $m = Mitra::find((int) $mitraId);

        if (!$m) {
            return response()->json(['message' => 'Mitra tidak ditemukan'], 404);
        }

        $alamat = null;
        foreach (['alamat', 'alamat_toko', 'alamat_pengiriman', 'address'] as $col) {
            if (isset($m->$col) && !empty($m->$col)) {
                $alamat = $m->$col;
                break;
            }
        }

        return response()->json([
            'id' => $m->id,
            'nama_toko' => $m->nama_toko ?? null,
            'alamat' => $alamat,
        ]);
    }

    // =======================
    // MITRA (protected)
    // =======================
    public function mitraIndex(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->mitra_id) {
            return response()->json(['message' => 'Akun ini tidak terhubung ke mitra'], 403);
        }

        $products = Product::with('mitra:id,nama_toko')
            ->withAvg('reviews as avg_rating', 'rating')
            ->withCount('reviews as ulasan_count')
            ->where('mitra_id', $user->mitra_id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $products->map(fn ($p) => $this->transformProduct($p)),
        ]);
    }

    public function mitraShow(Request $request, $id)
    {
        $user = $request->user();

        if (!$user || !$user->mitra_id) {
            return response()->json(['message' => 'Akun ini tidak terhubung ke mitra'], 403);
        }

        $product = Product::with('mitra:id,nama_toko')
            ->withAvg('reviews as avg_rating', 'rating')
            ->withCount('reviews as ulasan_count')
            ->where('id', $id)
            ->where('mitra_id', $user->mitra_id)
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json(['data' => $this->transformProduct($product)]);
    }

    // CREATE - /api/mitra/products (POST)
    public function mitraStore(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->mitra_id) {
            return response()->json(['message' => 'Akun ini tidak terhubung ke mitra'], 403);
        }

        $validated = $request->validate([
            'nama_produk' => 'required|string|max:255',
            'kategori_produk' => 'required|string|max:255',
            'deskripsi_produk' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'berat' => 'required|integer|min:0',
            'foto_produk' => 'nullable|image|max:4096',
        ]);

        $product = new Product();
        $product->mitra_id = $user->mitra_id;
        $product->nama_produk = $validated['nama_produk'];
        $product->kategori_produk = $validated['kategori_produk'];
        $product->deskripsi_produk = $validated['deskripsi_produk'] ?? '';
        $product->harga = $validated['harga'];
        $product->stok = $validated['stok'];
        $product->berat = $validated['berat'];

        if ($request->hasFile('foto_produk')) {
            $path = $request->file('foto_produk')->store('produk', 'public');
            $product->foto_produk = $path;
        }

        $product->save();

        $product = Product::with('mitra:id,nama_toko')
            ->withAvg('reviews as avg_rating', 'rating')
            ->withCount('reviews as ulasan_count')
            ->where('id', $product->id)
            ->first();

        return response()->json([
            'message' => 'Produk berhasil ditambahkan',
            'data' => $this->transformProduct($product),
        ], 201);
    }

    public function mitraUpdate(Request $request, $id)
    {
        $user = $request->user();

        if (!$user || !$user->mitra_id) {
            return response()->json(['message' => 'Akun ini tidak terhubung ke mitra'], 403);
        }

        $product = Product::where('id', $id)
            ->where('mitra_id', $user->mitra_id)
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'nama_produk' => 'required|string|max:255',
            'kategori_produk' => 'required|string|max:255',
            'deskripsi_produk' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'berat' => 'required|integer|min:0',
            'foto_produk' => 'nullable|image|max:4096',
        ]);

        $product->nama_produk = $validated['nama_produk'];
        $product->kategori_produk = $validated['kategori_produk'];
        $product->deskripsi_produk = $validated['deskripsi_produk'] ?? '';
        $product->harga = $validated['harga'];
        $product->stok = $validated['stok'];
        $product->berat = $validated['berat'];

        if ($request->hasFile('foto_produk')) {
            // hapus foto lama kalau ada
            if (!empty($product->foto_produk)) {
                $oldPath = ltrim((string) $product->foto_produk, '/');
                $oldPath = preg_replace('#^storage/#', '', $oldPath);

                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $path = $request->file('foto_produk')->store('produk', 'public');
            $product->foto_produk = $path; // ✅ path bersih
        }

        $product->save();

        $product = Product::with('mitra:id,nama_toko')
            ->withAvg('reviews as avg_rating', 'rating')
            ->withCount('reviews as ulasan_count')
            ->where('id', $product->id)
            ->first();

        return response()->json([
            'message' => 'Produk berhasil diperbarui',
            'data' => $this->transformProduct($product),
        ]);
    }

    public function mitraDestroy(Request $request, $id)
    {
        $user = $request->user();

        if (!$user || !$user->mitra_id) {
            return response()->json(['message' => 'Akun ini tidak terhubung ke mitra'], 403);
        }

        $product = Product::where('id', $id)
            ->where('mitra_id', $user->mitra_id)
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        if (!empty($product->foto_produk)) {
            $oldPath = ltrim((string) $product->foto_produk, '/');
            $oldPath = preg_replace('#^storage/#', '', $oldPath);

            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $product->delete();

        return response()->json(['message' => 'Produk berhasil dihapus']);
    }
}