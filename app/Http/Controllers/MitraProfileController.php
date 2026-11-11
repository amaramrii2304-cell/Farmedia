<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class MitraProfileController extends Controller
{
    /**
     * Halaman pengaturan mitra dengan tab:
     * - akun  (Akun & Keamanan)
     * - pengiriman
     * - pembayaran
     * - mode-libur
     */
    public function edit(Request $request)
    {
        $user = Auth::user();
        $mitra = Mitra::findOrFail($user->mitra_id);

        // tab aktif: akun / pengiriman / pembayaran / mode-libur
        $tab = $request->query('tab', 'akun');

        return view('mitra.profile.index', compact('mitra', 'tab', 'user'));
    }

    /**
     * Update pengaturan, dibedakan per "section".
     */
    public function update(Request $request)
    {
        $user  = Auth::user();
        $mitra = Mitra::findOrFail($user->mitra_id);

        $section = $request->input('section', 'akun'); // akun / pengiriman / pembayaran / mode_libur

        /*
        |--------------------------------------------------------------------------
        | 1. AKUN & KEAMANAN
        |--------------------------------------------------------------------------
        */
        if ($section === 'akun') {
            $validated = $request->validate([
                'nama_toko'                 => 'required|string|max:255',
                'alamat'                    => 'required|string|max:500',
                'phone'                     => 'nullable|string|max:30',
                'logo'                      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'password'                  => 'nullable|string|min:8|same:password_confirmation',
                'password_confirmation'     => 'nullable|string|min:8',
                'sms_verification_enabled'  => 'nullable|boolean',
            ]);

            // Data toko
            $mitra->nama_toko = $validated['nama_toko'];
            $mitra->alamat    = $validated['alamat'];
            $mitra->phone     = $validated['phone'] ?? $mitra->phone;
            $mitra->sms_verification_enabled = $request->boolean('sms_verification_enabled');

            // Logo toko (jika ada upload)
            if ($request->hasFile('logo')) {
                if (!empty($mitra->logo_path) && Storage::disk('public')->exists($mitra->logo_path)) {
                    Storage::disk('public')->delete($mitra->logo_path);
                }

                $path = $request->file('logo')->store('logos_mitra', 'public');
                $mitra->logo_path = $path;
            }

            // Password akun user (opsional)
            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
                $user->save();
            }

            $mitra->save();

            return redirect()
                ->route('mitra.profile.edit', ['tab' => 'akun'])
                ->with('success', 'Profil akun & keamanan berhasil diperbarui.');
        }

        /*
        |--------------------------------------------------------------------------
        | 2. PENGIRIMAN  (alamat + jasa kirim)
        |--------------------------------------------------------------------------
        */
        if ($section === 'pengiriman') {
            // ✅ DIUBAH: samakan dengan API Android -> pickup_address
            $validated = $request->validate([
                'pickup_address' => 'required|string|max:500',
                // checkbox jasa kirim bisa tanpa rule khusus (boolean)
            ]);

            // ✅ DIUBAH: simpan ke kolom pickup_address (bukan alamat_pengiriman)
            $mitra->pickup_address = $validated['pickup_address'];

            // Checkbox jasa kirim (ON/OFF)
            $mitra->ship_sapx       = $request->boolean('ship_sapx');
            $mitra->ship_pmtoh      = $request->boolean('ship_pmtoh');
            $mitra->ship_ksi        = $request->boolean('ship_ksi');
            $mitra->ship_cargonesia = $request->boolean('ship_cargonesia');
            $mitra->ship_313        = $request->boolean('ship_313');
            $mitra->ship_sng        = $request->boolean('ship_sng');
            $mitra->ship_tiga       = $request->boolean('ship_tiga');

            $mitra->save();

            return redirect()
                ->route('mitra.profile.edit', ['tab' => 'pengiriman'])
                ->with('success', 'Pengaturan pengiriman berhasil diperbarui.');
        }

        /*
        |--------------------------------------------------------------------------
        | 3. PEMBAYARAN  (rekening bank)
        |--------------------------------------------------------------------------
        */
        if ($section === 'pembayaran') {
            // ✅ DIUBAH: samakan dengan API Android -> bank_name, account_number, account_name
            $validated = $request->validate([
                'bank_name'       => 'nullable|string|max:100',
                'account_number'  => 'nullable|string|max:50',
                'account_name'    => 'nullable|string|max:150',
            ]);

            // ✅ DIUBAH: simpan ke kolom yang dipakai API Android
            $mitra->bank_name = $validated['bank_name'] ?? null;
            $mitra->bank_account_number = $validated['account_number'] ?? null;
            $mitra->bank_account_name = $validated['account_name'] ?? null;

            $mitra->save();

            return redirect()
                ->route('mitra.profile.edit', ['tab' => 'pembayaran'])
                ->with('success', 'Pengaturan pembayaran berhasil diperbarui.');
        }

        /*
        |--------------------------------------------------------------------------
        | 4. MODE LIBUR
        |--------------------------------------------------------------------------
        */
        if ($section === 'mode_libur') {
            $validated = $request->validate([
                'vacation_mode'    => 'nullable|boolean',
                'vacation_message' => 'nullable|string|max:500',
            ]);

            $mitra->vacation_mode    = $request->boolean('vacation_mode');
            $mitra->vacation_message = $validated['vacation_message'] ?? null;

            $mitra->save();

            return redirect()
                ->route('mitra.profile.edit', ['tab' => 'mode-libur'])
                ->with('success', 'Pengaturan mode libur berhasil diperbarui.');
        }

        // fallback kalau section tidak dikenal
        return redirect()
            ->route('mitra.profile.edit')
            ->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
