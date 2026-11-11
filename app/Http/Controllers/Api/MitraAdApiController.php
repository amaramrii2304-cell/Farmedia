<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdSubmission;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class MitraAdApiController extends Controller
{
    public function index()
    {
        $mitraId = Auth::user()->mitra_id;

        $query = AdSubmission::with('product')
            ->where('mitra_id', $mitraId)
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('created_at');

        $items = $query->get()->map(fn($ad) => $this->toDto($ad, false));

        $statsBase = AdSubmission::where('mitra_id', $mitraId)->where('status','!=','cancelled');

        $stats = [
            'total'     => (clone $statsBase)->count(),
            'pending'   => (clone $statsBase)->where('status','pending')->count(),
            'approved'  => (clone $statsBase)->where('status','approved')->count(),
            'active'    => (clone $statsBase)->where('status','active')->count(),
            'rejected'  => (clone $statsBase)->where('status','rejected')->count(),
            'ended'     => (clone $statsBase)->where('status','ended')->count(),
            'cancelled' => 0,
        ];

        // payment/transfer info untuk Android
        $bank = config('ads.bank', []);

        $payment = [
            'bank_name'      => $bank['nama'] ?? '-',
            'account_name'   => $bank['pemilik'] ?? '-',
            'account_number' => $bank['nomor'] ?? '-',
            'note'           => 'Setelah submit, transfer sesuai total lalu upload bukti.',
        ];

        return response()->json([
            'success' => true,
            'message' => null,
            'data'    => [
                'stats'   => $stats,
                'items'   => $items,
                'payment' => $payment,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $mitraId  = Auth::user()->mitra_id;
        $packages = config('ads.packages', []);

        $data = $request->validate([
            'product_id'      => 'required|integer|exists:products,id',
            'placement'       => 'required|in:home,category',
            'target_kategori' => 'nullable|string|max:255',
            'duration_days'   => 'required|integer|min:1',
        ]);

        if ($data['placement'] === 'category' && empty($data['target_kategori'])) {
            return response()->json([
                'success' => false,
                'message' => 'Target kategori wajib diisi.',
            ], 422);
        }

        $duration = (int) $data['duration_days'];
        $price = $packages[$data['placement']][$duration] ?? null;

        if ($price === null) {
            return response()->json([
                'success' => false,
                'message' => 'Paket durasi tidak valid.',
            ], 422);
        }

        /**
         * ✅ KUNCI UTAMA ANTI 409 / DOUBLE SUBMIT:
         * - Lock row Product (lockForUpdate) dalam transaction
         * - Jadi kalau user double tap / jaringan resend, request kedua nunggu
         * - Setelah request pertama create, request kedua akan ketemu existing -> 409 dengan existing_id
         */
        $result = DB::transaction(function () use ($mitraId, $data, $duration, $price) {

            // lock product row
            $product = Product::where('id', $data['product_id'])
                ->where('mitra_id', $mitraId)
                ->lockForUpdate()
                ->firstOrFail();

            // cari pengajuan existing yang masih "mengunci"
            $existing = AdSubmission::where('mitra_id', $mitraId)
                ->where('product_id', $product->id)
                ->whereIn('status', ['pending', 'approved', 'active'])
                ->orderByDesc('created_at')
                ->first();

            if ($existing) {
                $existing->load('product');

                return [
                    'type' => 'conflict',
                    'ad'   => $existing,
                ];
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

            $ad->load('product');

            return [
                'type' => 'created',
                'ad'   => $ad,
            ];
        });

        if (($result['type'] ?? null) === 'conflict') {
            /** ✅ 409 sekarang balikin existing_id + data */
            $ad = $result['ad'];

            return response()->json([
                'success' => false,
                'message' => 'Produk ini sudah punya pengajuan iklan yang masih berjalan/pending.',
                'existing_id' => $ad->id,
                'data' => $this->toDto($ad, true),
            ], 409);
        }

        /** ✅ sukses create */
        $ad = $result['ad'];

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan iklan berhasil dibuat.',
            'data'    => $this->toDto($ad, true),
        ]);
    }

    public function show(AdSubmission $ad)
    {
        $this->authorizeMitra($ad);
        $ad->load('product');

        return response()->json([
            'success' => true,
            'message' => null,
            'bank'    => config('ads.bank', []),
            'data'    => $this->toDto($ad, true),
        ]);
    }

    public function cancel(AdSubmission $ad)
    {
        $this->authorizeMitra($ad);

        if (in_array($ad->status, ['active','ended'])) {
            return response()->json([
                'success' => false,
                'message' => 'Iklan aktif/selesai tidak bisa dibatalkan.',
            ], 422);
        }

        $ad->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan iklan dibatalkan.',
        ]);
    }

    public function uploadProof(Request $request, AdSubmission $ad)
    {
        $this->authorizeMitra($ad);

        if (!in_array($ad->status, ['pending','approved'])) {
            return response()->json([
                'success' => false,
                'message' => 'Bukti hanya bisa diupload saat pending/approved.',
            ], 422);
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
            // kamu bisa ubah jadi "submitted" kalau mau,
            // tapi biar tidak merusak flow lama, kita tetap unpaid:
            'payment_status' => 'unpaid',
        ]);

        $ad->load('product');

        return response()->json([
            'success' => true,
            'message' => 'Bukti berhasil diupload. Menunggu verifikasi admin.',
            'data'    => $this->toDto($ad, true),
        ]);
    }

    private function authorizeMitra(AdSubmission $ad): void
    {
        if ((int) Auth::user()->mitra_id !== (int) $ad->mitra_id) abort(403);
    }

    private function toDto(AdSubmission $ad, bool $withProofUrl = false): array
    {
        $proofUrl = $ad->payment_proof ? asset('storage/'.$ad->payment_proof) : null;

        $product = $ad->relationLoaded('product') ? $ad->product : null;

        return [
            'id'               => $ad->id,
            'mitra_id'         => $ad->mitra_id,
            'product_id'       => $ad->product_id,
            'product_name'     => $product->nama_produk ?? null,

            'placement'        => $ad->placement,
            'target_kategori'  => $ad->target_kategori,
            'kategori_snapshot'=> $ad->kategori_snapshot,

            'duration_days'    => (int)($ad->duration_days ?? 0),
            'price'            => (int)($ad->price ?? 0),

            'status'           => $ad->status,
            'payment_method'   => $ad->payment_method,
            'payment_status'   => $ad->payment_status,

            'payment_proof'    => $ad->payment_proof,
            'payment_proof_url'=> $proofUrl,

            'start_at'         => optional($ad->start_at)->toIso8601String(),
            'end_at'           => optional($ad->end_at)->toIso8601String(),
            'created_at'       => optional($ad->created_at)->toIso8601String(),
            'updated_at'       => optional($ad->updated_at)->toIso8601String(),

            'date_text'        => optional($ad->created_at)->format('d M Y, H:i'),

            'product' => $product ? [
                'id'             => $product->id,
                'nama_produk'    => $product->nama_produk,
                'kategori_produk'=> $product->kategori_produk,
                'harga'          => (int)($product->harga ?? 0),
                'stok'           => (int)($product->stok ?? 0),
                'foto_url'       => $product->foto_url ?? null,
            ] : null,
        ];
    }
}
