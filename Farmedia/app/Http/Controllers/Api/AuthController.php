<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Mitra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Password; // ✅ TAMBAHAN untuk lupa sandi

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // ✅ Role hanya: petani / mitra
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:petani,mitra',
            'password' => 'required|string|min:6|confirmed',

            // ✅ optional dari Android (kalau belum ada field, aman)
            'alamat' => 'sometimes|nullable|string|max:5000',
        ]);

        $role = $data['role'];

        // ✅ Buat user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $role,
        ]);

        // ✅ Kalau Mitra: buat record di tabel mitras
        if ($role === 'mitra') {
            $mitra = new Mitra();

            // wajib umum
            if (Schema::hasColumn('mitras', 'nama_toko')) {
                $mitra->nama_toko = $data['name'];
            }
            if (Schema::hasColumn('mitras', 'email')) {
                $mitra->email = $data['email'];
            }

            // ✅ FIX UTAMA: alamat wajib (mitras.alamat NOT NULL)
            if (Schema::hasColumn('mitras', 'alamat')) {
                $mitra->alamat = !empty($data['alamat']) ? $data['alamat'] : 'Belum diisi';
            }

            // kalau struktur mitras punya kolom password
            if (Schema::hasColumn('mitras', 'password')) {
                $mitra->password = Hash::make($data['password']);
            }

            // isi default aman untuk kolom lain biar gak error NOT NULL kalau sewaktu-waktu wajib
            if (Schema::hasColumn('mitras', 'logo_path')) $mitra->logo_path = $mitra->logo_path ?? '';
            if (Schema::hasColumn('mitras', 'bank_name')) $mitra->bank_name = $mitra->bank_name ?? '';
            if (Schema::hasColumn('mitras', 'bank_account_number')) $mitra->bank_account_number = $mitra->bank_account_number ?? '';
            if (Schema::hasColumn('mitras', 'bank_account_name')) $mitra->bank_account_name = $mitra->bank_account_name ?? '';
            if (Schema::hasColumn('mitras', 'telepon')) $mitra->telepon = $mitra->telepon ?? '';
            if (Schema::hasColumn('mitras', 'pickup_address')) $mitra->pickup_address = $mitra->pickup_address ?? '';

            if (Schema::hasColumn('mitras', 'is_holiday')) {
                $mitra->is_holiday = 0;
            }
            if (Schema::hasColumn('mitras', 'holiday_message')) {
                $mitra->holiday_message = $mitra->holiday_message ?? '';
            }

            $mitra->save();

            // sambungkan ke user
            $user->mitra_id = $mitra->id;
            $user->save();
        }

        $token = $user->createToken('android')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $token = $user->createToken('android')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    // ✅ TAMBAHAN: LUPA SANDI (KIRIM LINK RESET KE EMAIL) — PUBLIC API
    // Endpoint: POST /api/auth/forgot-password
    // Body: { "email": "abc@gmail.com" }
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => __($status),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __($status),
        ], 422);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        $u = $request->user();

        $avatarUrl = null;
        if (!empty($u->avatar)) {
            $avatarUrl = url(Storage::url($u->avatar));
        }

        return response()->json([
            'data' => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'mitra_id' => $u->mitra_id,
                'avatar_url' => $avatarUrl,
                'created_at' => optional($u->created_at)->toISOString(),
                'alamat' => $u->alamat ?: $u->alamat_lengkap,
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $u = $request->user();

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|email|unique:users,email,' . $u->id,
            'alamat' => 'sometimes|nullable|string|max:5000',
        ]);

        if (array_key_exists('alamat', $data)) {
            $data['alamat_lengkap'] = $data['alamat'];
        }

        $u->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
        ]);
    }

    public function updatePassword(Request $request)
    {
        $u = $request->user();

        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($data['current_password'], $u->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi saat ini salah',
            ], 422);
        }

        $u->password = Hash::make($data['password']);
        $u->save();

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diperbarui',
        ]);
    }

    public function updateAvatar(Request $request)
    {
        $u = $request->user();

        $request->validate([
            'avatar' => 'required|image|max:2048',
        ]);

        if (!empty($u->avatar)) {
            Storage::disk('public')->delete($u->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $u->avatar = $path;
        $u->save();

        return response()->json([
            'success' => true,
            'message' => 'Foto profil berhasil diperbarui',
            'data' => [
                'avatar_url' => url(Storage::url($path)),
            ],
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $u = $request->user();

        $request->user()->currentAccessToken()->delete();

        if (!empty($u->avatar)) {
            Storage::disk('public')->delete($u->avatar);
        }

        $u->delete();

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil dihapus',
        ]);
    }
}
