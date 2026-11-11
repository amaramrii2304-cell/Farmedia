<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MitraOrderController extends Controller
{
    /**
     * Helper: pastikan order milik mitra login.
     */
    private function ensureOwner(Order $order): void
    {
        if ((int) auth()->user()->mitra_id !== (int) $order->mitra_id) {
            abort(403);
        }
    }

    /**
     * Helper: kunci pemrosesan untuk metode transfer sampai admin approve.
     * Return RedirectResponse kalau ditolak, atau null kalau boleh lanjut.
     */
    private function ensureCanProcess(Order $order)
    {
        $this->ensureOwner($order);

        // tidak boleh proses kalau sudah dibatalkan/ditolak/selesai
        if (in_array($order->status_order, ['dibatalkan', 'rejected', 'selesai'], true)) {
            return back()->with('error', 'Pesanan sudah selesai / dibatalkan / ditolak.');
        }

        // escrow: transfer wajib PAID dulu
        if ($order->metode_pembayaran === 'transfer' && $order->payment_status !== 'paid') {
            return back()->with('error', 'Pesanan transfer belum diverifikasi admin. Tunggu status pembayaran menjadi PAID.');
        }

        return null;
    }

    /**
     * Daftar pesanan untuk mitra.
     */
    public function index(Request $request)
    {
        $mitraId = Auth::user()->mitra_id;
        $view = $request->query('view', 'aktif');

        $query = Order::where('mitra_id', $mitraId)
            ->with(['user', 'items.product'])
            ->orderBy('created_at', 'desc');

        if ($view === 'aktif') {
            $query->whereIn('status_order', [
                'menunggu_konfirmasi',
                'confirmed',       // data lama
                'diproses',
                'dikemas',
                'siap_dikirim',
                'dikirim',
                'siap_diambil',
                'pending_cancel',
            ]);
        } elseif ($view === 'selesai') {
            $query->where('status_order', 'selesai');
        } elseif ($view === 'dibatalkan') {
            $query->whereIn('status_order', ['rejected', 'dibatalkan']);
        }

        $orders = $query->get();

        return view('mitra.orders.index', [
            'orders' => $orders,
            'view'   => $view,
        ]);
    }

    /**
     * Detail pesanan mitra.
     */
    public function show(Order $order)
    {
        $this->ensureOwner($order);

        $order->load(['user', 'items.product', 'mitra']);

        return view('mitra.orders.show', compact('order'));
    }

    /**
     * Konfirmasi pesanan -> "diproses"
     * (Untuk data lama yang masih 'confirmed', sebaiknya kamu normalisasi via SQL)
     */
    public function confirm(Order $order)
    {
        if ($resp = $this->ensureCanProcess($order)) return $resp;

        // hanya boleh dari menunggu_konfirmasi atau confirmed (lama)
        if (!in_array($order->status_order, ['menunggu_konfirmasi', 'confirmed'], true)) {
            return back()->with('error', 'Status pesanan tidak valid untuk dikonfirmasi.');
        }

        $order->update([
            'status_order' => 'diproses',
        ]);

        return redirect()->route('mitra.orders.index')
            ->with('success', 'Pesanan dikonfirmasi dan masuk tahap diproses.');
    }

    /**
     * Tolak pesanan.
     */
    public function reject(Order $order)
    {
        $this->ensureOwner($order);

        // kalau transfer sudah paid lalu ditolak, harusnya ada mekanisme refund (opsional), tapi kita fokus sinkron alur dulu
        if (in_array($order->status_order, ['selesai', 'dibatalkan'], true)) {
            return back()->with('error', 'Pesanan sudah selesai / dibatalkan.');
        }

        $order->update([
            'status_order' => 'rejected',
        ]);

        return redirect()->route('mitra.orders.index')
            ->with('success', 'Pesanan berhasil ditolak.');
    }

    /**
     * Melanjutkan status ke tahap berikutnya (otomatis).
     */
    public function nextStep(Order $order)
    {
        if ($resp = $this->ensureCanProcess($order)) return $resp;

        // Normalisasi status lama
        $currentStatus = $order->status_order === 'confirmed' ? 'diproses' : $order->status_order;

        // Flow delivery (stop di siap_dikirim)
        $flow = $order->metode_pengiriman === 'pickup'
    ? ['menunggu_konfirmasi', 'diproses', 'siap_diambil']
    : ['menunggu_konfirmasi', 'diproses', 'dikemas', 'siap_dikirim'];

        $flow = $order->metode_pengiriman === 'pickup' ? $pickupFlow : $deliveryFlow;

        $currentIndex = array_search($currentStatus, $flow, true);

        if ($currentIndex === false) {
            return back()->with('error', 'Status pesanan tidak dikenali untuk nextStep.');
        }

        if ($currentIndex >= count($flow) - 1) {
            return back()->with('error', 'Pesanan sudah berada di tahap terakhir untuk alur ini.');
        }

        $next = $flow[$currentIndex + 1];

        // kalau pindah ke siap_dikirim (delivery), buat resi jika belum ada
        if ($next === 'siap_dikirim' && $order->metode_pengiriman === 'delivery') {
            if (empty($order->nomor_resi)) {
                $order->nomor_resi = 'FM-' . now()->format('ymd') . '-' . str_pad((string)$order->id, 6, '0', STR_PAD_LEFT);
            }
        }

        $order->status_order = $next;
        $order->save();

        // kalau selesai (pickup) isi selesai_at
        if ($next === 'selesai' && $order->metode_pengiriman === 'pickup') {
            $order->update(['selesai_at' => now()]);
        }

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function pack(Order $order)
    {
        if ($resp = $this->ensureCanProcess($order)) return $resp;

        if ($order->metode_pengiriman !== 'delivery') {
            return back()->with('error', 'Pesanan ini bukan pengiriman delivery.');
        }

        if (!in_array($order->status_order, ['diproses', 'confirmed'], true)) {
            return back()->with('error', 'Pesanan belum bisa dikemas (status harus diproses).');
        }

        $order->update(['status_order' => 'dikemas']);

        return back()->with('success', 'Pesanan ditandai sebagai sedang dikemas.');
    }

    public function readyPickup(Order $order)
    {
        if ($resp = $this->ensureCanProcess($order)) return $resp;

        if ($order->metode_pengiriman !== 'pickup') {
            return back()->with('error', 'Pesanan ini bukan metode ambil di toko.');
        }

        if ($order->status_order !== 'diproses' && $order->status_order !== 'confirmed') {
            return back()->with('error', 'Pesanan belum bisa ditandai siap diambil.');
        }

        $order->update(['status_order' => 'siap_diambil']);

        return back()->with('success', 'Pesanan siap diambil pembeli.');
    }

    public function readyShip(Order $order)
    {
        if ($resp = $this->ensureCanProcess($order)) return $resp;

        if ($order->metode_pengiriman !== 'delivery') {
            return back()->with('error', 'Pesanan ini bukan pengiriman delivery.');
        }

        if ($order->status_order !== 'dikemas') {
            return back()->with('error', 'Pesanan belum bisa siap dikirim (status harus dikemas).');
        }

        if (empty($order->nomor_resi)) {
            $order->nomor_resi = 'FM-' . now()->format('ymd') . '-' . str_pad((string)$order->id, 6, '0', STR_PAD_LEFT);
        }

        $order->status_order = 'siap_dikirim';
        $order->save();

        return redirect()->route('mitra.orders.show', $order->id)
            ->with('success', 'Pesanan siap dikirim & resi dibuat.');
    }

    public function finish(Order $order)
    {
        $this->ensureOwner($order);

        // Pickup boleh selesai oleh mitra, tapi tetap kunci transfer harus paid
        if ($order->metode_pembayaran === 'transfer' && $order->payment_status !== 'paid') {
            return back()->with('error', 'Pesanan transfer belum diverifikasi admin. Tidak bisa diselesaikan.');
        }

        if ($order->metode_pengiriman === 'pickup') {
            if (!in_array($order->status_order, ['siap_diambil', 'diproses'], true)) {
                return back()->with('error', 'Status belum valid untuk diselesaikan.');
            }

            $order->update([
                'status_order' => 'selesai',
                'selesai_at'   => now(),
            ]);

            return back()->with('success', 'Pesanan telah ditandai selesai.');
        }

        return back()->with('error', 'Pesanan delivery diselesaikan oleh admin setelah kurir mengonfirmasi.');
    }

    /**
     * Pembeli mengajukan cancel -> status_order = pending_cancel dan simpan status sebelumnya.
     * (Catatan: pengajuan cancel biasanya dibuat dari sisi pembeli, bukan di sini.
     * Tapi reject/approve ada di mitra.)
     */
    public function approveCancel(Order $order)
    {
        $this->ensureOwner($order);

        if ($order->status_order !== 'pending_cancel') {
            return back()->with('error', 'Tidak ada permintaan pembatalan untuk pesanan ini.');
        }

        $order->update([
            'status_order'        => 'dibatalkan',
            // optional: bersihkan status_order_before
            'status_order_before' => null,
        ]);

        return back()->with('success', 'Pembatalan pesanan telah disetujui.');
    }

    public function rejectCancel(Order $order)
    {
        $this->ensureOwner($order);

        if ($order->status_order !== 'pending_cancel') {
            return back()->with('error', 'Tidak ada permintaan pembatalan untuk pesanan ini.');
        }

        $previous = $order->status_order_before ?? 'menunggu_konfirmasi';

        $order->update([
            'status_order'        => $previous,
            'status_order_before' => null,
        ]);

        return back()->with('success', 'Permintaan pembatalan ditolak.');
    }

    /**
     * Transfer escrow: MITRA TIDAK BOLEH mengubah payment_status jadi paid.
     * Admin yang approve.
     */
    public function confirmPayment(Order $order)
    {
        $this->ensureOwner($order);

        if ($order->metode_pembayaran !== 'transfer') {
            return back()->with('error', 'Metode pembayaran bukan transfer bank.');
        }

        return back()->with('error', 'Pembayaran transfer diverifikasi oleh admin. Silakan tunggu status pembayaran menjadi PAID.');
    }

    public function printResi(Order $order)
    {
        $this->ensureOwner($order);

        if (empty($order->nomor_resi)) {
            return redirect()
                ->route('mitra.orders.show', $order->id)
                ->with('error', 'Resi belum dibuat. Klik "Pesanan Siap Dikirim" dulu untuk membuat resi.');
        }

        $order->load(['user', 'mitra', 'items.product']);

        return view('shared.print_resi', compact('order'));
    }
}