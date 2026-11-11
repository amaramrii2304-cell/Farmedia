<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validasi (kalau role mitra, email juga harus unik di tabel mitras)
        $emailRules = ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class];
        if ($request->input('role') === 'mitra') {
            $emailRules[] = Rule::unique('mitras', 'email');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => $emailRules,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:user,mitra', // petani/pembeli=user, penjual=mitra
        ]);

        $user = DB::transaction(function () use ($request) {

            $mitraId = null;

            // ✅ Jika daftar sebagai mitra, buat record mitras dulu
            if ($request->role === 'mitra') {
                $mitraId = DB::table('mitras')->insertGetId([
                    'nama_toko'  => $request->name, // sementara pakai name sebagai nama toko
                    'email'      => $request->email,

                    // kolom password di mitras tidak dipakai buat login (login pakai users),
                    // tapi biar aman & konsisten, kita hash.
                    'password'   => Hash::make($request->password),

                    // aman kalau kolom NOT NULL
                    'alamat'     => '',
                    'logo_path'  => null,

                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Buat user baru
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => $request->role,
            ]);

            // ✅ Isi mitra_id kalau memang mitra
            if ($mitraId !== null) {
                $user->mitra_id = $mitraId;
                $user->save();
            }

            return $user;
        });

        // Trigger event registered
        event(new Registered($user));

        // ✅ Login pakai data terbaru (biar session kebawa mitra_id dan tidak 403)
        Auth::login($user->fresh());

        // Regenerate session
        $request->session()->regenerate();

        // Redirect sesuai role
        if ($user->role === 'user') {
            return redirect()->route('user.beranda');
        } elseif ($user->role === 'mitra') {
            return redirect()->route('mitra.dashboard');
        } elseif ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect('/');
    }
}
