<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        // Proses login bawaan Breeze
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => __('Terjadi kesalahan saat login.'),
            ]);
        }

        // === REDIRECT BERDASARKAN ROLE ===

        // Admin -> ke dashboard admin
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // Mitra -> ke dashboard mitra
        if ($user->role === 'mitra') {
            return redirect()->route('mitra.dashboard');
        }

        // Selain itu (user/petani) -> dashboard user biasa
        return redirect()->route('user.beranda');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
