<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index()
{
    $orders = Order::with(['user','mitra','items'])
        ->where(function ($q) {
            $q->where('status_order', 'menunggu_konfirmasi')

              // siap dikirim & dikirim khusus delivery
              ->orWhere(function ($qq) {
                  $qq->where('metode_pengiriman', 'delivery')
                     ->whereIn('status_order', ['siap_dikirim', 'dikirim']);
              });
        })
        ->orderByRaw("
            CASE status_order
                WHEN 'menunggu_konfirmasi' THEN 1
                WHEN 'siap_dikirim' THEN 2
                WHEN 'dikirim' THEN 3
                ELSE 99
            END
        ")
        ->orderByDesc('created_at')
        ->paginate(15);

    return view('admin.orders.index', compact('orders'));
}

    public function show(Order $order)
    {
        $order->load(['user','mitra','items.product']);
        return view('admin.orders.show', compact('order'));
    }

    public function ship(Order $order)
    {
        // hanya bisa ship kalau sudah siap_dikirim + delivery
        if ($order->metode_pengiriman !== 'delivery' || $order->status_order !== 'siap_dikirim') {
            return back()->with('error', 'Order belum siap dikirim.');
        }

        // generate resi otomatis kalau belum ada
        if (empty($order->nomor_resi)) {
            $order->nomor_resi = \App\Models\Order::generateResi();
        }

        $order->status_order = 'dikirim';
        $order->save();

        return back()->with('success', 'Pesanan dikirim. Nomor resi berhasil dibuat.');
    }

    public function finish(Order $order)
    {
        // admin selesaiin hanya kalau delivery sudah dikirim
        if ($order->metode_pengiriman !== 'delivery' || $order->status_order !== 'dikirim') {
            return back()->with('error', 'Order belum dalam status dikirim.');
        }

        $order->update(['status_order' => 'selesai']);

        return back()->with('success', 'Pesanan ditandai selesai.');
    }

    public function printResi(Order $order)
    {
        // hanya boleh cetak kalau sudah ada nomor resi
        if (empty($order->nomor_resi)) {
            return back()->with('error', 'Resi belum dibuat.');
        }

        $order->load(['user','mitra','items.product']);
        return view('admin.orders.print_resi', compact('order'));
    }

    /**
     * ✅ VIEW BUKTI TRANSFER VIA CONTROLLER (ANTI 403 /storage)
     * URL: /admin/pesanan/{order}/transfer-proof
     */
    public function transferProof(Order $order)
    {
        // (sebenarnya sudah aman karena route admin pakai middleware is_admin,
        // tapi kamu boleh tetap simpan ini)
        $user = Auth::user();
        $role = strtolower(trim((string)($user->role ?? '')));
        if ($role !== 'admin') {
            abort(403, 'Forbidden (admin only)');
        }

        // ambil path bukti (transfer_* dulu)
        $path = null;
        if (Schema::hasColumn('orders', 'transfer_proof_path') && !empty($order->transfer_proof_path)) {
            $path = $order->transfer_proof_path;
        } elseif (!empty($order->payment_proof_path)) {
            $path = $order->payment_proof_path;
        } elseif (!empty($order->payment_proof)) {
            $path = $order->payment_proof;
        }

        if (!$path) abort(404);

        // ✅ FIX: bersihkan leading slash biar Storage::exists() selalu cocok
        $path = ltrim((string)$path, '/');

        // kalau path ternyata URL, redirect saja
        if (preg_match('~^https?://~i', $path)) {
            return redirect()->away($path);
        }

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $stream = Storage::disk('public')->readStream($path);
        $mime   = Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';
        $name   = basename($path);

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) fclose($stream);
        }, 200, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="'.$name.'"',
        ]);
    }

    /**
     * ✅ APPROVE TRANSFER (pakai transfer_* agar status berubah di web kamu)
     */
    public function approveTransfer(Order $order)
    {
        // ✅ FIX UTAMA:
        // jangan strict === 'transfer', karena data bisa "TRANSFER"/"Transfer Bank"/dll
        $metode = strtolower(trim((string) $order->metode_pembayaran));
        if (!str_contains($metode, 'transfer')) {
            return back()->with('error', 'Metode pembayaran bukan transfer.');
        }

        // harus ada bukti
        $proofPath = null;
        if (Schema::hasColumn('orders', 'transfer_proof_path') && !empty($order->transfer_proof_path)) {
            $proofPath = $order->transfer_proof_path;
        } elseif (!empty($order->payment_proof_path)) {
            $proofPath = $order->payment_proof_path;
        } elseif (!empty($order->payment_proof)) {
            $proofPath = $order->payment_proof;
        }

        if (empty($proofPath)) {
            return back()->with('error', 'Bukti transfer belum ada.');
        }

        // status harus submitted (atau waiting_verification kalau masih ada field lama)
        $status = null;
        if (Schema::hasColumn('orders', 'transfer_status')) {
            $status = strtolower(trim((string)($order->transfer_status ?? '')));
        } else {
            $status = strtolower(trim((string)($order->payment_status ?? '')));
        }

        // kalau sudah verified/paid, jangan error (biar aman klik 2x)
        if (in_array($status, ['verified', 'paid'], true)) {
            return back()->with('success', 'Pembayaran transfer sudah diverifikasi.');
        }

        if (!in_array($status, ['submitted', 'waiting_verification', 'pending', ''], true)) {
            return back()->with('error', 'Status pembayaran tidak valid untuk approve.');
        }

        DB::transaction(function () use ($order) {
            $update = [];

            // ✅ skema baru transfer_*
            if (Schema::hasColumn('orders', 'transfer_status')) {
                $update['transfer_status'] = 'verified';

                if (Schema::hasColumn('orders', 'transfer_verified_at')) {
                    $update['transfer_verified_at'] = now();
                }

                if (Schema::hasColumn('orders', 'transfer_rejected_at')) {
                    $update['transfer_rejected_at'] = null;
                }
            }

            // ✅ kompatibel: kalau kolom lama masih ada
            if (Schema::hasColumn('orders', 'payment_status')) {
                $update['payment_status'] = 'paid';
            }
            if (Schema::hasColumn('orders', 'payment_verified_at')) {
                $update['payment_verified_at'] = now();
            }
            if (Schema::hasColumn('orders', 'paid_at')) {
                $update['paid_at'] = now();
            }

            if (!empty($update)) {
                $order->update($update);
            }
        });

        return back()->with('success', 'Pembayaran transfer disetujui. Status menjadi VERIFIED/PAID.');
    }
}
