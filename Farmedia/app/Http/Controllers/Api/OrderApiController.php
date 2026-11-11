<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;

class OrderApiController extends Controller
{
    /**
     * ✅ NORMALISASI METODE PEMBAYARAN
     */
    private function sanitizePaymentMethod($method): string
    {
        $m = strtolower(trim((string)$method));
        if (str_contains($m, 'tempat') || str_contains($m, 'cod')) return 'COD'; 
        if (str_contains($m, 'cash') || str_contains($m, 'tunai')) return 'Cash';
        if (str_contains($m, 'transfer')) return 'Transfer';
        return substr($method, 0, 15);
    }

    private function isTransferPayment(?string $metodePembayaran): bool
    {
        $m = strtolower(trim((string) $metodePembayaran));
        return str_contains($m, 'transfer');
    }

    /**
     * ✅ CREATE ORDER
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'alamat_pengiriman'  => 'required|string',
            'metode_pembayaran'  => 'required|string',
            'metode_pengiriman'  => 'required|string',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.jumlah'     => 'required|integer|min:1',
        ]);

        $items = $request->input('items', []);
        $productIds = collect($items)->pluck('product_id')->unique()->all();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $mitraId = $products->pluck('mitra_id')->filter()->unique()->first();
        $ongkir = (int) ($request->input('ongkir', 0) ?? 0);
        $fixPaymentMethod = $this->sanitizePaymentMethod($request->metode_pembayaran);

        return DB::transaction(function() use ($user, $request, $items, $products, $mitraId, $ongkir, $fixPaymentMethod){
            foreach($items as $it){
                $pLocked = Product::where('id', (int)$it['product_id'])->lockForUpdate()->first();
                if (!$pLocked || (int)$pLocked->stok < (int)$it['jumlah']) {
                    return response()->json(['success'=>false,'message'=>"Stok habis"], 422);
                }
                $pLocked->decrement('stok', (int)$it['jumlah']);
            }

            $order = Order::create([
                'user_id'           => $user->id,
                'mitra_id'          => $mitraId,
                'total_harga'       => 0, 
                'ongkir'            => $ongkir,
                'metode_pembayaran' => $fixPaymentMethod,
                'alamat_pengiriman' => $request->alamat_pengiriman,
                'metode_pengiriman' => $request->metode_pengiriman,
                'status_order'      => 'menunggu_konfirmasi',
                'payment_status'    => 'pending',
            ]);

            $totalProduk = 0;
            foreach ($items as $it) {
                $p = $products->get((int)$it['product_id']);
                $harga = (int)($p->harga ?? 0);
                $qty = (int)$it['jumlah'];
                $subtotal = $harga * $qty;
                $totalProduk += $subtotal;

                $order->items()->create([
                    'product_id'   => $p->id,
                    'jumlah'       => $qty,
                    'harga_satuan' => $harga,
                    'subtotal'     => $subtotal,
                ]);
            }
            $order->update(['total_harga' => $totalProduk + $ongkir]);
            return response()->json(['success' => true, 'data' => ['id' => $order->id, 'order_no' => 'ORD-'.$order->id]], 201);
        });
    }

    /**
     * ✅ INDEX (Daftar Riwayat dengan FILTER STATUS)
     */
    public function index(Request $request) {
        $user = Auth::user();
        
        // 1. Ambil parameter status dari Android (misal: ?status=selesai)
        $status = $request->query('status');

        // 2. Mulai query dasar
        $query = Order::with(['items.product.mitra', 'mitra'])
                    ->where('user_id', $user->id);

        // 3. ✅ LOGIKA FILTER: Jika status ada dan bukan 'all', saring datanya
        if (!empty($status) && $status !== 'all') {
            $query->where('status_order', $status);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $data = $orders->map(function($o) {
            $firstItem = $o->items->first();
            $product = $firstItem?->product;
            $namaToko = $product?->mitra?->nama_toko ?? $o->mitra?->nama_toko ?? '-'; 
            return [
                'id'                => $o->id,
                'order_no'          => 'ORD-' . $o->id,
                'status_order'      => $o->status_order,
                'total_harga'       => (int)$o->total_harga,
                'ongkir'            => (int)($o->ongkir ?? 0),
                'metode_pembayaran' => $o->metode_pembayaran,
                'created_at'        => $o->created_at?->format('Y-m-d H:i:s'),
                'toko'              => ['nama_toko' => $namaToko],
                'summary'           => [
                    'product_name' => $product?->nama_produk ?? '-',
                    'qty_total'    => (int)($o->items->sum('jumlah') ?? 0),
                    'foto_url'     => $product?->foto_produk
                ]
            ];
        });
        return response()->json(['data' => $data]);
    }

    /**
     * ✅ SHOW (Detail Pesanan)
     */
    public function show(Order $order) {
        $order->load(['user', 'items.product.mitra', 'mitra']);
        $namaTokoPusat = $order->mitra?->nama_toko ?? $order->items->first()?->product?->mitra?->nama_toko ?? '-';
        return response()->json([
            'success' => true, 
            'data' => [
                'id'                => $order->id,
                'order_no'          => 'ORD-' . $order->id,
                'status_order'      => $order->status_order,
                'nama_pembeli'      => $order->user?->name,
                'total_harga'       => (int)$order->total_harga,
                'ongkir'            => (int)$order->ongkir,
                'metode_pembayaran' => $order->metode_pembayaran,
                'metode_pengiriman' => $order->metode_pengiriman,
                'alamat_pengiriman' => $order->alamat_pengiriman,
                'created_at'        => $order->created_at?->format('Y-m-d H:i:s'),
                'toko'              => ['nama_toko' => $namaTokoPusat, 'nama' => $namaTokoPusat],
                'items'             => $order->items->map(function($item) use ($namaTokoPusat) {
                    $prod = $item->product;
                    return [
                        'nama_produk'  => $prod?->nama_produk ?? '-',
                        'nama_toko'    => $prod?->mitra?->nama_toko ?? $namaTokoPusat,
                        'toko'         => ['nama_toko' => $prod?->mitra?->nama_toko ?? $namaTokoPusat],
                        'jumlah'       => (int)$item->jumlah,
                        'harga_satuan' => (int)$item->harga_satuan,
                        'subtotal'     => (int)($item->jumlah * $item->harga_satuan),
                        'foto_url'     => $prod?->foto_produk
                    ];
                }),
            ]
        ]);
    }

    /**
     * ✅ FUNGSI BATALKAN PESANAN
     */
    public function requestCancel(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if (!in_array($order->status_order, ['menunggu_konfirmasi', 'diproses'])) {
            return response()->json(['success' => false, 'message' => 'Gagal: Pesanan tidak dapat dibatalkan.'], 422);
        }

        return DB::transaction(function() use ($order) {
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) $product->increment('stok', $item->jumlah);
            }
            $order->update(['status_order' => 'dibatalkan']);
            return response()->json(['success' => true, 'message' => 'Pesanan dibatalkan.']);
        });
    }

    /**
     * ✅ FUNGSI PESANAN DITERIMA
     */
    public function confirmReceived(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if (!in_array($order->status_order, ['dikirim', 'siap_diambil'])) {
            return response()->json(['success' => false, 'message' => 'Gagal konfirmasi'], 422);
        }

        $order->update(['status_order' => 'selesai']);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih! Pesanan Anda telah selesai.'
        ]);
    }

    public function submitTransferProof(Request $request, Order $order) {
        if ($order->user_id !== auth()->id()) return response()->json(['success' => false], 403);
        $path = $request->file('transfer_proof')->store('transfer_proofs', 'public');
        $order->update(['transfer_proof_path' => $path, 'transfer_status' => 'submitted', 'transfer_submitted_at' => now()]);
        return response()->json(['success' => true]);
    }
}