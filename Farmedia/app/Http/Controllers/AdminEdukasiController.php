<?php

namespace App\Http\Controllers;

use App\Models\Edukasi;
use Illuminate\Http\Request;

class AdminEdukasiController extends Controller
{
    /**
     * Tampilkan daftar konten edukasi (card-card).
     */
    public function index()
    {
        $edukasies = Edukasi::latest()->paginate(9); // bisa diubah sesuai kebutuhan
        return view('admin.edukasi.index', compact('edukasies'));
    }

    /**
     * Form tambah edukasi.
     */
    public function create()
    {
        return view('admin.edukasi.create');
    }

    /**
     * Simpan edukasi baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul'      => 'required|string|max:255',
            'kategori'   => 'nullable|string|max:255',
            'link_video' => 'nullable|string|max:255',
            'isi'        => 'nullable|string',
        ]);

        Edukasi::create($validated);

        return redirect()
            ->route('admin.edukasi.index')
            ->with('success', 'Konten edukasi berhasil ditambahkan.');
    }

    /**
     * Form edit edukasi.
     */
    public function edit(Edukasi $edukasi)
    {
        return view('admin.edukasi.edit', compact('edukasi'));
    }

    /**
     * Update edukasi.
     */
    public function update(Request $request, Edukasi $edukasi)
    {
        $validated = $request->validate([
            'judul'      => 'required|string|max:255',
            'kategori'   => 'nullable|string|max:255',
            'link_video' => 'nullable|string|max:255',
            'isi'        => 'nullable|string',
        ]);

        $edukasi->update($validated);

        return redirect()
            ->route('admin.edukasi.index')
            ->with('success', 'Konten edukasi berhasil diperbarui.');
    }

    /**
     * Hapus edukasi.
     */
    public function destroy(Edukasi $edukasi)
    {
        $edukasi->delete();

        return redirect()
            ->route('admin.edukasi.index')
            ->with('success', 'Konten edukasi berhasil dihapus.');
    }

    public function dashboard()
{
    $jumlahEdukasi = Edukasi::count(); // jumlah total konten edukasi

    return view('admin.dashboard', compact('jumlahEdukasi'));
}
}
