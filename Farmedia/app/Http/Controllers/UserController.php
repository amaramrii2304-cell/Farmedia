<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Mitra;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Menampilkan daftar pengguna
    public function index()
    {
        $users = User::all();
        return view('admin.pengguna.index', compact('users'));
    }

    // Form tambah pengguna
    public function create()
    {
        return view('admin.pengguna.create');
    }

    // Simpan pengguna baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:user,mitra,admin',
            'nama_toko' => 'nullable|string|max:255',
            'alamat' => 'nullable|string|max:500',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        // Jika role mitra/penjual, buat record Mitra dan hubungkan
        if ($request->role === 'mitra') {
            $mitra = Mitra::create([
                'nama_toko' => $request->nama_toko ?? $user->name . "'s Toko",
                'alamat' => $request->alamat ?? '-',
            ]);

            $user->mitra_id = $mitra->id;
            $user->save();
        }

        return redirect()->route('admin.pengguna.index')
            ->with('success', 'Pengguna berhasil ditambahkan');
    }

    // Form edit pengguna
    public function edit(User $pengguna)
    {
        return view('admin.pengguna.edit', ['user' => $pengguna]);
    }

    // Update pengguna
    public function update(Request $request, User $pengguna)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$pengguna->id,
            'role'  => 'required|in:admin,pembeli,mitra',
            'password' => 'nullable|string|min:6',
        ]);

        // Update basic info
        $pengguna->name = $request->name;
        $pengguna->email = $request->email;
        $pengguna->role = $request->role;

        if ($request->password) {
            $pengguna->password = Hash::make($request->password);
        }

        $pengguna->save();

        // Jika role menjadi mitra, pastikan ada record di tabel mitras
        if ($request->role === 'mitra') {
            if ($pengguna->mitra_id) {
                // Update existing mitra
                $mitra = Mitra::find($pengguna->mitra_id);
                if ($mitra) {
                    $mitra->nama_toko = $mitra->nama_toko ?: $pengguna->name . "'s Toko";
                    $mitra->email      = $mitra->email ?: $pengguna->email; // wajib
                    $mitra->alamat     = $mitra->alamat ?: '-';
                    $mitra->save();
                } else {
                    // kalau mitra_id ada tapi record hilang
                    $mitra = Mitra::create([
                        'nama_toko' => $pengguna->name . "'s Toko",
                        'email'     => $pengguna->email,
                        'alamat'    => '-',
                    ]);
                    $pengguna->mitra_id = $mitra->id;
                    $pengguna->save();
                }
            } else {
                // buat record baru mitra
                $mitra = Mitra::create([
                    'nama_toko' => $pengguna->name . "'s Toko",
                    'email'     => $pengguna->email,
                    'alamat'    => '-',
                ]);
                $pengguna->mitra_id = $mitra->id;
                $pengguna->save();
            }
        } else {
            // Jika role bukan mitra, hapus mitra_id supaya tidak ada kesalahan
            $pengguna->mitra_id = null;
            $pengguna->save();
        }

        return redirect()->route('admin.pengguna.index')->with('success', 'Pengguna berhasil diupdate');
    }

    // Hapus pengguna
    public function destroy(User $pengguna)
    {
        // Hapus juga record mitra jika role mitra
        if ($pengguna->role === 'mitra' && $pengguna->mitra_id) {
            $mitra = Mitra::find($pengguna->mitra_id);
            if ($mitra) {
                $mitra->delete();
            }
        }

        $pengguna->delete();
        return redirect()->route('admin.pengguna.index')
            ->with('success', 'Pengguna berhasil dihapus');
    }
}
