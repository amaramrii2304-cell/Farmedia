<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MitraSettingsApiController extends Controller
{
    private function logoUrl($mitra): ?string
    {
        if (!$mitra || empty($mitra->logo_path)) return null;

        $path = ltrim($mitra->logo_path, '/');
        if (str_starts_with($path, 'storage/')) $path = substr($path, strlen('storage/'));

        return url(Storage::disk('public')->url($path));
    }

    private function payload($user, $mitra)
    {
        return [
            'success' => true,
            'message' => 'OK',
            'data' => [
                'store' => [
                    'id' => $mitra->id ?? null, // ✅ supaya Android bisa tampil "ID Mitra"
                    'nama_toko' => $mitra->nama_toko ?? null,
                    'alamat' => $mitra->alamat ?? null,
                    'telepon' => $mitra->telepon ?? null,
                    'logo_url' => $this->logoUrl($mitra),
                ],
                'account' => [
                    'email' => $user->email ?? null,
                ],
                'shipping' => [
                    'pickup_address' => $mitra->pickup_address ?? ($mitra->alamat ?? null),
                ],
                'payment' => [
                    'bank_name' => $mitra->bank_name ?? null,
                    'account_number' => $mitra->bank_account_number ?? null,
                    'account_name' => $mitra->bank_account_name ?? null,
                ],
                'holiday' => [
                    'is_holiday' => (bool)($mitra->is_holiday ?? false),
                    'message' => $mitra->holiday_message ?? null,
                ],
            ],
        ];
    }

    public function show(Request $request)
    {
        $user = $request->user();
        $mitra = $user->mitra;

        if (!$mitra) {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini tidak terhubung dengan mitra.',
                'data' => null,
            ], 403);
        }

        return response()->json($this->payload($user, $mitra));
    }

    public function updateAccount(Request $request)
    {
        $user = $request->user();
        $mitra = $user->mitra;

        if (!$mitra) {
            return response()->json(['success' => false, 'message' => 'Tidak ada mitra', 'data' => null], 403);
        }

        $data = $request->validate([
            'email' => ['required','email', Rule::unique('users','email')->ignore($user->id)],
            'new_password' => ['nullable','string','min:6'],
            'new_password_confirmation' => ['nullable','same:new_password'],
        ]);

        $user->email = $data['email'];
        if (!empty($data['new_password'])) {
            $user->password = Hash::make($data['new_password']);
        }
        $user->save();

        return response()->json($this->payload($user, $mitra));
    }

    // ✅ UPDATE INFORMASI TOKO (yang sekarang error 500)
    public function updateStore(Request $request)
    {
        $user = $request->user();
        $mitra = $user->mitra;

        if (!$mitra) {
            return response()->json(['success' => false, 'message' => 'Tidak ada mitra', 'data' => null], 403);
        }

        $data = $request->validate([
            'nama_toko' => ['required','string','min:2'],
            'alamat' => ['required','string','min:5'],
            'telepon' => ['nullable','string','min:5'],
        ]);

        $mitra->nama_toko = $data['nama_toko'];
        $mitra->alamat = $data['alamat'];
        $mitra->telepon = $data['telepon'] ?? null;
        $mitra->save();

        return response()->json($this->payload($user, $mitra));
    }

    // ✅ UPLOAD LOGO TOKO
    public function uploadLogo(Request $request)
    {
        $user = $request->user();
        $mitra = $user->mitra;

        if (!$mitra) {
            return response()->json(['success' => false, 'message' => 'Tidak ada mitra', 'data' => null], 403);
        }

        $request->validate([
            'logo' => ['required','image','mimes:jpg,jpeg,png','max:2048'], // 2MB
        ]);

        // hapus logo lama kalau ada
        if (!empty($mitra->logo_path)) {
            $old = ltrim($mitra->logo_path, '/');
            if (str_starts_with($old, 'storage/')) $old = substr($old, strlen('storage/'));
            Storage::disk('public')->delete($old);
        }

        $path = $request->file('logo')->store('mitra_logos', 'public');
        $mitra->logo_path = $path;
        $mitra->save();

        return response()->json($this->payload($user, $mitra));
    }

    public function updateShipping(Request $request)
    {
        $user = $request->user();
        $mitra = $user->mitra;

        if (!$mitra) {
            return response()->json(['success' => false, 'message' => 'Tidak ada mitra', 'data' => null], 403);
        }

        $data = $request->validate([
            'pickup_address' => ['required','string','min:5'],
        ]);

        $mitra->pickup_address = $data['pickup_address'];
        $mitra->save();

        return response()->json($this->payload($user, $mitra));
    }

    public function updatePayment(Request $request)
    {
        $user = $request->user();
        $mitra = $user->mitra;

        if (!$mitra) {
            return response()->json(['success' => false, 'message' => 'Tidak ada mitra', 'data' => null], 403);
        }

        $data = $request->validate([
            'bank_name' => ['required','string','min:2'],
            'account_number' => ['required','string','min:4'],
            'account_name' => ['required','string','min:2'],
        ]);

        $mitra->bank_name = $data['bank_name'];
        $mitra->bank_account_number = $data['account_number'];
        $mitra->bank_account_name = $data['account_name'];
        $mitra->save();

        return response()->json($this->payload($user, $mitra));
    }

    public function updateHoliday(Request $request)
    {
        $user = $request->user();
        $mitra = $user->mitra;

        if (!$mitra) {
            return response()->json(['success' => false, 'message' => 'Tidak ada mitra', 'data' => null], 403);
        }

        $data = $request->validate([
            'is_holiday' => ['required','boolean'],
            'message' => ['nullable','string'],
        ]);

        $mitra->is_holiday = (bool)$data['is_holiday'];
        $mitra->holiday_message = $data['message'] ?? null;
        $mitra->save();

        return response()->json($this->payload($user, $mitra));
    }
}
