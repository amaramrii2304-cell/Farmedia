<?php

namespace App\Http\Controllers;

use App\Models\AdSubmission;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MitraAdSubmissionController extends Controller
{
    public function index()
    {
        $mitraId = Auth::user()->mitra_id;

        $ads = AdSubmission::with('product')
            ->where('mitra_id', $mitraId)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('mitra.ads.index', compact('ads'));
    }

    public function create()
    {
        $mitraId = Auth::user()->mitra_id;

        $products = Product::where('mitra_id', $mitraId)
            ->orderByDesc('created_at')
            ->get(['id','nama_produk','kategori_produk','harga','stok']);

        $kategoriList = Product::where('mitra_id', $mitraId)
            ->whereNotNull('kategori_produk')
            ->where('kategori_produk','!=','')
            ->distinct()
            ->orderBy('kategori_produk')
            ->pluck('kategori_produk');

        $packages = config('ads.packages', []);
        $bank     = config('ads.bank', []);

        return view('mitra.ads.create', compact('products','kategoriList','packages','bank'));
    }

    public function store(Request $request)
    {
        $mitraId  = Auth::user()->mitra_id;
        $packages = config('ads.packages', []);

        $data = $request->validate([
            'product_id'      => 'required|exists:products,id',
            'placement'       => 'required|in:home,category',
            'target_kategori' => 'nullable|string|max:255',
            'duration_days'   => 'required|integer',
        ]);

        $product = Product::where('id', $data['product_id'])
            ->where('mitra_id', $mitraId)
            ->firstOrFail();

        if ($data['placement'] === 'category' && empty($data['target_kategori'])) {
            return back()->withInput()->with('error', 'Target kategori wajib diisi untuk iklan kategori.');
        }

        // Validasi paket durasi sesuai placement (biar gak submit durasi aneh)
        $duration = (int) $data['duration_days'];
        $price = $packages[$data['placement']][$duration] ?? null;
        if ($price === null) {
            return back()->withInput()->with('error', 'Paket durasi tidak valid.');
        }

        // Anti spam sederhana: produk yang sama tidak boleh ada iklan aktif/pending/approved
        $exists = AdSubmission::where('product_id', $product->id)
            ->whereIn('status', ['pending','approved','active'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Produk ini sudah punya pengajuan iklan yang masih berjalan/pending.');
        }

        $ad = AdSubmission::create([
            'mitra_id'          => $mitraId,
            'product_id'        => $product->id,
            'placement'         => $data['placement'],
            'target_kategori'   => $data['placement'] === 'category' ? $data['target_kategori'] : null,
            'kategori_snapshot' => $product->kategori_produk,

            'duration_days'     => $duration,
            'price'             => (int) $price,

            'status'            => 'pending',
            'payment_method'    => 'transfer',
            'payment_status'    => 'unpaid',
        ]);

        return redirect()->route('mitra.ads.show', $ad->id)
            ->with('success', 'Pengajuan iklan berhasil dibuat. Silakan transfer & upload bukti.');
    }

    public function show(AdSubmission $ad)
    {
        $this->authorizeMitra($ad);

        $ad->load('product');
        $bank = config('ads.bank', []);

        return view('mitra.ads.show', compact('ad','bank'));
    }

    public function uploadProof(Request $request, AdSubmission $ad)
    {
        $this->authorizeMitra($ad);

        if (!in_array($ad->status, ['pending','approved'])) {
            return back()->with('error', 'Bukti transfer hanya bisa diupload saat pending/approved.');
        }

        $request->validate([
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($ad->payment_proof && Storage::disk('public')->exists($ad->payment_proof)) {
            Storage::disk('public')->delete($ad->payment_proof);
        }

        $path = $request->file('payment_proof')->store('ads_proof', 'public');

        $ad->update([
            'payment_proof'  => $path,
            'payment_status' => 'unpaid', // admin yang mark paid
        ]);

        return back()->with('success', 'Bukti transfer berhasil diupload. Menunggu verifikasi admin.');
    }

    public function cancel(AdSubmission $ad)
    {
        $this->authorizeMitra($ad);

        if (in_array($ad->status, ['active','ended'])) {
            return back()->with('error', 'Iklan yang sudah aktif/selesai tidak bisa dibatalkan.');
        }

        $ad->update(['status' => 'cancelled']);
        return redirect()->route('mitra.ads.index')->with('success', 'Pengajuan iklan dibatalkan.');
    }

    private function authorizeMitra(AdSubmission $ad): void
    {
        if ((int)Auth::user()->mitra_id !== (int)$ad->mitra_id) {
            abort(403);
        }
    }
}
