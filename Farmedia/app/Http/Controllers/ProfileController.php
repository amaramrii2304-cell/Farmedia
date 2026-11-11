<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil.
     * - role "user"  -> view khusus pembeli: profile.user-edit
     * - role lain    -> view lama: profile.edit
     */
    public function edit(Request $request): View
    {
        // INI WAJIB: ambil user dulu
        $user = $request->user();

        if ($user && $user->role === 'user') {
            // pembeli → tampilan ala Shopee
            return view('profile.user-edit', [
                'user' => $user,
            ]);
        }

        // admin / mitra / role lain → pakai view bawaan
        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update data profil (dipakai oleh kedua view).
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->validated());

        // kalau email berubah, paksa verifikasi ulang
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Hapus akun.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
