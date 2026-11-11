<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class OrderPaymentController extends Controller
{
    /**
     * Upload bukti transfer
     * - User: hanya order miliknya
     * - Admin: boleh semua (kalau perlu manual)
     */
    public function uploadProof(Request $request, Order $order)
    {
        $user = Auth::user();
        $role = strtolower(trim((string) ($user->role ?? 'user')));

        // ✅ Proteksi akses
        if ($role !== 'admin' && (int)$order->user_id !== (int)$user->id) {
            abort(403, 'Forbidden');
        }

        // ✅ Pastikan metode pembayaran transfer
        $metode = strtolower(trim((string) $order->metode_pembayaran));
        if (!str_contains($metode, 'transfer')) {
            return back()->with('error', 'Metode pembayaran bukan transfer.');
        }

        // ✅ Validasi file
        $request->validate([
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $file = $request->file('payment_proof');
        if (!$file) {
            return back()->with('error', 'File bukti transfer tidak ditemukan.');
        }

        // ✅ Simpan file
        $path = $file->store('payments/orders', 'public');

        // ✅ Hapus file lama (jika ada)
        $old = null;
        if (Schema::hasColumn('orders', 'transfer_proof_path') && !empty($order->transfer_proof_path)) {
            $old = $order->transfer_proof_path;
        } elseif (Schema::hasColumn('orders', 'payment_proof_path') && !empty($order->payment_proof_path)) {
            $old = $order->payment_proof_path;
        } elseif (Schema::hasColumn('orders', 'payment_proof') && !empty($order->payment_proof)) {
            $old = $order->payment_proof;
        }

        if ($old && !preg_match('~^https?://~i', $old)) {
            $old = ltrim($old, '/');
            if (Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
        }

        // ✅ Update semua kemungkinan kolom (baru & lama)
        $update = [];

        // skema baru
        if (Schema::hasColumn('orders', 'transfer_proof_path')) {
            $update['transfer_proof_path'] = $path;
        }
        if (Schema::hasColumn('orders', 'transfer_status')) {
            $update['transfer_status'] = 'submitted';
        }
        if (Schema::hasColumn('orders', 'transfer_uploaded_at')) {
            $update['transfer_uploaded_at'] = now();
        }
        if (Schema::hasColumn('orders', 'transfer_verified_at')) {
            $update['transfer_verified_at'] = null;
        }

        // skema lama (kompatibilitas)
        if (Schema::hasColumn('orders', 'payment_proof_path')) {
            $update['payment_proof_path'] = $path;
        }
        if (Schema::hasColumn('orders', 'payment_proof')) {
            $update['payment_proof'] = $path;
        }
        if (Schema::hasColumn('orders', 'payment_status')) {
            $update['payment_status'] = 'waiting_verification';
        }
        if (Schema::hasColumn('orders', 'paid_at')) {
            $update['paid_at'] = null;
        }

        if (!empty($update)) {
            $order->update($update);
        }

        return back()->with(
            'success',
            'Bukti transfer berhasil diupload. Menunggu verifikasi admin.'
        );
    }
}
