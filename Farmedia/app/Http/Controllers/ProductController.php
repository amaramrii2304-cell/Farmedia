<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Tampilkan daftar produk.
     * - Admin: semua produk
     * - Mitra: hanya produk miliknya
     */
    public function index(Request $request)
{
    $user = Auth::user();

    // ==== ADMIN ====
    if ($user->role === 'admin') {
        $products = Product::with('mitra')
            ->withAvg('reviews as avg_rating', 'rating')   // rata-rata rating
            ->withCount('reviews')                         // jumlah ulasan
            ->latest()
            ->paginate(10);

        return view('admin.products.index', compact('products'));
    }

    // ==== MITRA ====
    if ($user->role === 'mitra') {

        // ambil query ?layout=list|grid, default: list
        $layout = $request->query('layout', 'list');

        $products = Product::with('mitra')
            ->where('mitra_id', $user->mitra_id)
            ->withAvg('reviews as avg_rating', 'rating')   // rata-rata rating
            ->withCount('reviews')                         // jumlah ulasan
            ->latest()
            ->paginate(10)
            ->withQueryString(); // supaya ?layout=... ikut ke pagination

        return view('mitra.products.index', compact('products', 'layout'));
    }

    abort(403, 'Anda tidak punya akses ke halaman ini.');
}

    /**
     * Form tambah produk (Hanya Mitra)
     */
    public function create()
    {
        $user = Auth::user();

        if ($user->role !== 'mitra') {
            abort(403, 'Hanya mitra yang boleh menambah produk.');
        }

        return view('mitra.products.create');
    }

    /**
     * Simpan produk baru (Hanya Mitra)
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'mitra') {
            abort(403, 'Hanya mitra yang boleh menambah produk.');
        }

        $validated = $request->validate([
            'nama_produk'      => 'required|string|max:255',
            'kategori_produk'  => 'nullable|string|max:255',
            'deskripsi_produk' => 'nullable|string',
            'harga'            => 'required|numeric|min:0',
            'stok'             => 'required|integer|min:0',
            'berat'            => 'nullable|numeric|min:0',
            'foto_produk'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('foto_produk')) {
            $path = $request->file('foto_produk')->store('produk', 'public');
            $validated['foto_produk'] = $path;
        }

        $validated['mitra_id'] = $user->mitra_id;

        Product::create($validated);

        return redirect()->route('mitra.products.index')
                         ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Form edit produk
     * - Admin: bisa edit semua produk
     * - Mitra: hanya produk miliknya
     */
    public function edit(Product $product)
{
    $user = Auth::user();

    // ADMIN boleh edit semua produk
    if ($user->role === 'admin') {
        return view('admin.products.edit', [
            'product' => $product,
        ]);
    }

    // MITRA: hanya boleh produk miliknya
    if ($user->role === 'mitra') {

        // cocokkan mitra_id (pakai != biar aman)
        if ((int) $user->mitra_id != (int) $product->mitra_id) {
            return redirect()
                ->route('mitra.products.index')
                ->with('error', 'Produk ini bukan milik toko Anda.');
        }

        return view('mitra.products.edit', [
            'produk' => $product,  // <- view mitra memang pakai $produk
        ]);
    }

    abort(403, 'Anda tidak punya akses ke halaman ini.');
}


    /**
     * Update produk
     */
    public function update(Request $request, Product $product)
{
    $user = Auth::user();

    if (!(
        $user->role === 'admin' ||
        ($user->role === 'mitra' && (int) $user->mitra_id == (int) $product->mitra_id)
    )) {
        return redirect()
            ->back()
            ->with('error', 'Anda tidak boleh mengupdate produk ini.');
    }

    $validated = $request->validate([
        'nama_produk'      => 'required|string|max:255',
        'kategori_produk'  => 'nullable|string|max:255',
        'deskripsi_produk' => 'nullable|string',
        'harga'            => 'required|numeric|min:0',
        'stok'             => 'required|integer|min:0',
        'berat'            => 'nullable|numeric|min:0',
        'foto_produk'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    if ($request->hasFile('foto_produk')) {
        if ($product->foto_produk && \Storage::disk('public')->exists($product->foto_produk)) {
            \Storage::disk('public')->delete($product->foto_produk);
        }

        $path = $request->file('foto_produk')->store('produk', 'public');
        $validated['foto_produk'] = $path;
    }

    $product->update($validated);

    $redirectRoute = $user->role === 'admin'
        ? 'admin.products.index'
        : 'mitra.products.index';

    return redirect()
        ->route($redirectRoute)
        ->with('success', 'Produk berhasil diperbarui.');
}

    /**
     * Hapus produk
     */
    public function destroy(Product $product)
    {
        $user = Auth::user();

        if ($user->role === 'admin' || ($user->role === 'mitra' && $user->mitra_id === $product->mitra_id)) {
            if ($product->foto_produk && Storage::disk('public')->exists($product->foto_produk)) {
                Storage::disk('public')->delete($product->foto_produk);
            }

            $product->delete();

            $redirectRoute = $user->role === 'admin' ? 'admin.products.index' : 'mitra.products.index';
            return redirect()->route($redirectRoute)
                             ->with('success', 'Produk berhasil dihapus.');
        }

        abort(403, 'Anda tidak boleh menghapus produk ini.');
    }

    public function showUser(Product $product)
{
    // Load relasi yang dipakai di view
    $product->load([
        'mitra',
        'reviews.user',
    ]);

    // Ringkasan rating
    $avgRating = round((float) $product->reviews()->avg('rating'), 1);
    $reviewsCount = (int) $product->reviews()->count();

    // Default: tidak boleh review
    $canReview = false;

    // Hanya pembeli (role user) yang boleh review
    if (Auth::check() && Auth::user()->role === 'user') {
        $userId = Auth::id();

        // Pernah beli produk ini dan order selesai?
        $hasPurchased = OrderItem::where('product_id', $product->id)
            ->whereHas('order', function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->where('status_order', 'selesai');
            })
            ->exists();

        // Sudah pernah review produk ini?
        $alreadyReviewed = ProductReview::where('product_id', $product->id)
            ->where('user_id', $userId)
            ->exists();

        $canReview = $hasPurchased && !$alreadyReviewed;
    }

    return view('user.products.show', compact(
        'product',
        'avgRating',
        'reviewsCount',
        'canReview'
    ));
}
}