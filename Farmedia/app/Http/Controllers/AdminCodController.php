<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\CodSettlement;
use Illuminate\Http\Request;

class AdminCodController extends Controller
{
    public function index(Request $request)
{
    $status = $request->get('status'); // unverified | verified | selisih | paid

    $orders = Order::with(['mitra','user','codSettlement'])
        ->where('metode_pembayaran', 'cod')

        // ✅ DEFAULT: kalau tidak memilih filter, sembunyikan yang sudah PAID
        ->when(empty($status), function ($q) {
            $q->where(function ($qq) {
                $qq->whereDoesntHave('codSettlement') // belum ada settlement = belum verif
                   ->orWhereHas('codSettlement', fn ($s) => $s->where('status', '!=', 'paid'));
            });
        })

        // ✅ Kalau pilih filter status tertentu
        ->when(!empty($status), function ($q) use ($status) {
            if ($status === 'unverified') {
                $q->where(function ($qq) {
                    $qq->whereDoesntHave('codSettlement')
                       ->orWhereHas('codSettlement', fn ($s) => $s->where('status', 'unverified'));
                });
            } else {
                $q->whereHas('codSettlement', fn ($s) => $s->where('status', $status));
            }
        })

        ->orderByDesc('created_at')
        ->paginate(15)
        ->withQueryString();

    return view('admin.cod.index', compact('orders'));
}

    public function receive(Request $request, Order $order)
    {
        $data = $request->validate([
            'received_amount' => 'required|numeric|min:0',
            'received_note'   => 'nullable|string|max:255',
        ]);

        $expected = (float)($order->total_harga ?? 0);

        $settlement = CodSettlement::firstOrCreate(
            ['order_id' => $order->id],
            ['expected_amount' => $expected]
        );

        $received = (float)$data['received_amount'];

        $settlement->update([
            'expected_amount' => $expected,
            'received_amount' => $received,
            'received_note'   => $data['received_note'] ?? null,
            'received_at'     => now(),
            'received_by'     => auth()->id(),
            'status'          => ($received == $expected) ? 'verified' : 'selisih',
        ]);

        return back()->with('success', 'Penerimaan uang COD disimpan.');
    }

    public function deduct(Order $order)
{
    $settlement = CodSettlement::firstOrCreate(
        ['order_id' => $order->id],
        ['expected_amount' => (float)($order->total_harga ?? 0)]
    );

    if (is_null($settlement->received_amount)) {
        return back()->with('error', 'Terima COD dulu.');
    }

    $platformFee = (float)($order->komisi_produk ?? 0) + (float)($order->komisi_ongkir ?? 0);
    $serviceFee  = (float)($order->ongkir ?? 0); // ongkir = biaya layanan sesuai kurir
    $net         = max(0, (float)$settlement->received_amount - $platformFee - $serviceFee);

    $settlement->update([
        'platform_fee'  => $platformFee,
        'service_fee'   => $serviceFee,
        'net_to_seller' => $net,
        'status'        => ($settlement->status === 'selisih') ? 'selisih' : 'verified',
    ]);

    return back()->with('success', 'Potongan komisi & biaya dihitung.');
}

    public function pay(Request $request, Order $order)
{
    $data = $request->validate([
        'payout_ref' => 'required|string|max:100',
    ]);

    $order->load('mitra');
    $m = $order->mitra;

    if (empty($m?->bank_nama) || empty($m?->bank_nomor) || empty($m?->bank_pemilik)) {
        return back()->with('error', 'Data rekening mitra belum lengkap (Bank, No Rekening, Pemilik). Lengkapi dulu.');
    }

    $settlement = $order->codSettlement;

    if (!$settlement) return back()->with('error', 'Data COD belum ada.');
    if ($settlement->status === 'selisih') return back()->with('error', 'Masih selisih, bereskan dulu.');
    if (is_null($settlement->received_amount)) return back()->with('error', 'Terima COD dulu.');
    if (is_null($settlement->net_to_seller)) return back()->with('error', 'Hitung potongan dulu.');

    $settlement->update([
        'payout_status' => 'paid',
        'payout_at'     => now(),
        'payout_ref'    => $data['payout_ref'],
        'paid_by'       => auth()->id(),
        'status'        => 'paid',
    ]);

    $order->update([
        'komisi_lunas_at' => now(),
    ]);

    return back()->with('success', 'Pembayaran ke penjual ditandai selesai (transfer manual).');
}
}