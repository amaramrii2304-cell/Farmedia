<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MitraOrderApiController extends Controller
{
    private function mitraId(): ?int
    {
        return Auth::user()->mitra_id ? (int) Auth::user()->mitra_id : null;
    }

    /**
     * ✅ FIX UTAMA: Fungsi Hitung Badge Lonceng
     * Disesuaikan dengan Android: response.body()?.data?.count
     * Endpoint: GET /mitra/orders/unread-count
     */
    public function unreadCount()
    {
        $mitraId = $this->mitraId();
        if (!$mitraId) return response()->json(['success' => false, 'data' => ['count' => 0]]);

        $count = Order::where('mitra_id', $mitraId)
                    ->where('status_order', 'menunggu_konfirmasi')
                    ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count // Variabel ini yang dibaca badge merah Android
            ]
        ]);
    }

    public function index(Request $request)
    {
        $mitraId = $this->mitraId();
        if (!$mitraId) return response()->json(['message' => 'Unauthorized'], 401);

        $query = Order::with(['user', 'items.product', 'mitra'])->where('mitra_id', $mitraId);

        if ($request->filled('status')) {
            $status = strtolower(trim($request->status));
            $query->whereRaw('LOWER(status_order) = ?', [$status]);
        }

        $orders = $query->orderByDesc('created_at')->get()->map(fn ($o) => $this->mapOrder($o));
        
        return response()->json([
            'success' => true, 
            'data' => $orders
        ]);
    }

    public function show(Order $order)
    {
        $mitraId = $this->mitraId();
        if (!$mitraId || (int) $order->mitra_id !== $mitraId) return response()->json(['message' => 'Forbidden'], 403);
        
        return response()->json([
            'success' => true, 
            'data' => $this->mapOrder($order->load(['user', 'items.product', 'mitra']))
        ]);
    }

    public function confirm(Order $order) {
        $order->update(['status_order' => 'diproses']);
        return response()->json(['success' => true, 'message' => 'Pesanan dikonfirmasi']);
    }

    public function process(Order $order) {
        $order->update(['status_order' => 'diproses']);
        return response()->json(['success' => true, 'message' => 'Pesanan sedang diproses']);
    }

    public function pack(Order $order) {
        $order->update(['status_order' => 'dikemas']);
        return response()->json(['success' => true, 'message' => 'Pesanan telah dikemas']);
    }

    public function readyShip(Order $order) {
        $order->update([
            'status_order' => 'siap_dikirim', 
            'nomor_resi'   => $order->nomor_resi ?? Order::generateResi()
        ]);
        return response()->json(['success' => true, 'data' => $this->mapOrder($order->fresh())]);
    }

    public function markShipped(Order $order) {
        $order->update(['status_order' => 'dikirim']);
        return response()->json(['success' => true, 'message' => 'Pesanan telah dikirim']);
    }

    public function readyPickup(Order $order) {
        $order->update(['status_order' => 'siap_diambil']);
        return response()->json(['success' => true, 'message' => 'Pesanan siap diambil']);
    }

    public function completePickup(Order $order) {
        $order->update(['status_order' => 'selesai', 'selesai_at' => now()]);
        return response()->json(['success' => true, 'message' => 'Pesanan selesai diambil']);
    }

    public function reject(Order $order) {
        $order->update(['status_order' => 'rejected']);
        return response()->json(['success' => true, 'message' => 'Pesanan ditolak']);
    }

    public function printResi(Order $order)
    {
        $mitraId = $this->mitraId();
        if (!$mitraId || (int) $order->mitra_id !== $mitraId) return response("Forbidden", 403);

        $order->load(['user', 'items.product', 'mitra']);
        $paymentMethod = strtoupper((string)$order->metode_pembayaran);
        $paymentLabel = (str_contains($paymentMethod, 'TRANSFER') || str_contains($paymentMethod, 'QRIS')) ? 'NON TUNAI' : 'TUNAI';
        $metodeRaw = strtolower(trim((string) $order->metode_pengiriman));
        $shippingLabel = (str_contains($metodeRaw, 'ambil') || str_contains($metodeRaw, 'pickup')) ? 'PICKUP' : 'DELIVERY';
        $alamatToko = $order->mitra->alamat_toko ?? $order->mitra->alamat ?? $order->mitra->address ?? 'Alamat Toko tidak diset';

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body { font-family: 'Arial', sans-serif; margin: 0; padding: 10px; background: #fff; }
                .label-container { border: 2px solid #000; border-radius: 15px; padding: 20px; max-width: 500px; margin: auto; }
                .top-section { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
                .brand { font-size: 24px; font-weight: 900; letter-spacing: 1px; margin: 0; }
                .label-type { font-size: 14px; color: #666; margin: 0; }
                .payment-badge { border: 2px solid #000; border-radius: 20px; padding: 5px 15px; font-weight: bold; font-size: 16px; }
                .order-id { font-weight: bold; text-align: right; margin-top: 5px; font-size: 14px; }
                .main-grid { display: flex; gap: 15px; margin-top: 15px; border-top: 1px solid #000; padding-top: 15px; }
                .left-col { flex: 1.2; border: 1px solid #ddd; border-radius: 10px; padding: 10px; }
                .right-col { flex: 1; display: flex; flex-direction: column; gap: 10px; }
                .box-title { font-size: 11px; font-weight: bold; color: #333; text-transform: uppercase; margin-bottom: 5px; }
                .box-content { font-size: 13px; font-weight: bold; line-height: 1.3; }
                .address { font-size: 12px; font-weight: normal; color: #444; margin-top: 3px; }
                .resi-section { text-align: center; border: 1px dashed #999; border-radius: 8px; padding: 10px; margin-top: 5px; }
                .barcode-sim { height: 40px; background: linear-gradient(90deg, #000 2px, transparent 2px, #000 4px, transparent 1px, #000 2px, transparent 1px, #000 3px); width: 80%; margin: 10px auto; }
                .info-table { width: 100%; margin-top: 10px; font-size: 13px; }
                .info-table td { padding: 3px 0; }
                .info-table .val { font-weight: bold; text-align: right; }
                .card { border: 1px solid #ddd; border-radius: 10px; padding: 10px; }
                .footer-line { border-top: 2px solid #000; margin-top: 20px; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px; color: #444; }
            </style>
        </head>
        <body>
            <div class='label-container'>
                <div class='top-section'>
                    <div><p class='brand'>FARMEDIA</p><p class='label-type'>Label Pengiriman</p></div>
                    <div><div class='payment-badge'>$paymentLabel</div><div class='order-id'>#".($order->kode_order ?? ('ORD-'.$order->id))."</div></div>
                </div>
                <div class='main-grid'>
                    <div class='left-col'>
                        <div class='box-title'>Nomor Resi</div>
                        <div class='resi-section'>
                            <div style='font-size:12px'>Resi <span style='font-size:16px; font-weight:bold;'>".($order->nomor_resi ?? '-')."</span></div>
                            <div class='barcode-sim'></div>
                            <div style='font-size:11px; letter-spacing: 2px;'>".($order->nomor_resi ?? '-')."</div>
                        </div>
                        <table class='info-table'>
                            <tr><td>Metode Kirim</td><td class='val'>$shippingLabel</td></tr>
                            <tr><td>Berat</td><td class='val'>0,01 kg</td></tr>
                            <tr><td>Total Dibayar</td><td class='val'>Rp ".number_format($order->total_harga, 0, ',', '.')."</td></tr>
                        </table>
                    </div>
                    <div class='right-col'>
                        <div class='card'><div class='box-title'>Kepada</div><div class='box-content'>".$order->user->name."</div><div class='address'>".$order->alamat_pengiriman."</div></div>
                        <div class='card'><div class='box-title'>Dari</div><div class='box-content'>".($order->mitra->nama_toko ?? 'Mitra Farmedia')."</div><div class='address'>".$alamatToko."</div></div>
                        <div class='card'>
                            <div class='box-title'>Isi Paket</div>
                            <div class='box-content' style='font-weight:normal;'>";
                                foreach($order->items as $item) {
                                    $html .= "<div>".$item->product->nama_produk." <span style='float:right'>".$item->jumlah."x</span></div>";
                                }
        $html .= "          </div>
                        </div>
                    </div>
                </div>
                <div class='footer-line'><span><strong>FARMEDIA</strong> • Shipping Label</span><span>".now()->format('d M Y H:i')."</span></div>
            </div>
        </body>
        </html>";

        return response($html)->header('Content-Type', 'text/html');
    }

    private function mapOrder(Order $order): array
    {
        $metodeRaw = strtolower(trim((string) $order->metode_pengiriman));
        $labelPengiriman = (str_contains($metodeRaw, 'ambil') || str_contains($metodeRaw, 'pickup')) ? 'Ambil di Toko' : 'Dikirim ke Alamat';
        $namaToko = $order->mitra->nama_toko ?? 'Mitra Farmedia';

        return [
            'id'                => $order->id,
            'order_no'          => $order->kode_order ?? ('ORD-' . $order->id),
            'created_at'        => optional($order->created_at)->toDateTimeString(),
            'date_text'         => optional($order->created_at)->format('d M Y'),
            'status_order'      => $order->status_order,
            'nama_pembeli'      => $order->user?->name,
            'buyer_name'        => $order->user?->name,
            'email_pembeli'     => $order->user?->email,
            'alamat_pengiriman' => $order->alamat_pengiriman,
            'metode_pembayaran' => $order->metode_pembayaran ?? '-',
            'payment_status'    => strtoupper($order->transfer_status ?? $order->payment_status ?? 'PENDING'),
            'admin_verified'    => in_array(strtolower((string)($order->transfer_status ?? $order->payment_status)), ['verified', 'paid']),
            'jenis_pengiriman'  => $labelPengiriman, 
            'metode_pengiriman' => $labelPengiriman,
            'shipping_method'   => $labelPengiriman,
            'total_harga'       => (int) $order->total_harga,
            'total'             => (int) $order->total_harga,
            'total_bayar'       => (int) $order->total_harga,
            'ongkir'            => (int) ($order->ongkir ?? 0),
            'item_count'        => (int) $order->items->sum('jumlah'),
            'toko'              => ['nama' => $namaToko, 'nama_toko' => $namaToko],
            'items'             => $order->items->map(function ($item) use ($namaToko) {
                $path = $item->product?->foto_url;
                $foto_url = $path ? (preg_match('~^https?://~i', $path) ? $path : asset('storage/' . ltrim($path, '/'))) : null;
                $price = (int) ($item->harga_satuan ?? 0);
                $qty = (int) $item->jumlah;
                return [
                    'product_id'   => $item->product_id,
                    'nama_produk'  => $item->product?->nama_produk,
                    'product_name' => $item->product?->nama_produk,
                    'qty'          => $qty,
                    'jumlah'       => $qty,
                    'harga'        => $price,
                    'harga_satuan' => $price,
                    'subtotal'     => (int) ($qty * $price),
                    'foto_url'     => $foto_url,
                    'image'        => $foto_url,
                    'toko'         => $namaToko
                ];
            })->values(),
        ];
    }
}