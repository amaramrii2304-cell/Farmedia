<?php

namespace App\Http\Controllers;

use App\Models\Edukasi;
use Illuminate\Http\Request;

class UserEdukasiController extends Controller
{
    /**
     * Halaman edukasi utama (non-AJAX fallback).
     * Menampilkan daftar edukasi + search + filter + sort.
     */
    public function index(Request $request)
    {
        $q        = $request->query('q');
        $kategori = $request->query('kategori');
        $sort     = $request->query('sort', 'terbaru'); // default: terbaru

        $edukasies = Edukasi::when($q, function($query) use ($q) {
                $query->where('judul', 'like', "%$q%")
                      ->orWhere('kategori', 'like', "%$q%")
                      ->orWhere('isi', 'like', "%$q%");
            })
            ->when($kategori, function($query) use ($kategori) {
                $query->where('kategori', $kategori);
            })
            ->when($sort, function($query) use ($sort) {
                if ($sort === 'terbaru') $query->orderBy('created_at', 'desc');
                if ($sort === 'terlama') $query->orderBy('created_at', 'asc');
                if ($sort === 'az')      $query->orderBy('judul', 'asc');
                if ($sort === 'za')      $query->orderBy('judul', 'desc');
            })
            ->paginate(9)
            ->withQueryString(); // supaya pagination tidak hilang query params

        return view('user.edukasi.index', compact('edukasies', 'q', 'kategori', 'sort'));
    }

    /**
     * Endpoint AJAX untuk pencarian real-time.
     * Mengembalikan partial list HTML.
     */
    public function ajaxSearch(Request $request)
    {
        $q        = $request->query('q');
        $kategori = $request->query('kategori');
        $sort     = $request->query('sort');

        $edukasies = Edukasi::when($q, function($query) use ($q) {
                $query->where('judul', 'like', "%$q%")
                      ->orWhere('kategori', 'like', "%$q%")
                      ->orWhere('isi', 'like', "%$q%");
            })
            ->when($kategori, function($query) use ($kategori) {
                $query->where('kategori', $kategori);
            })
            ->when($sort, function($query) use ($sort) {
                if ($sort === 'terbaru') $query->orderBy('created_at', 'desc');
                if ($sort === 'terlama') $query->orderBy('created_at', 'asc');
                if ($sort === 'az')      $query->orderBy('judul', 'asc');
                if ($sort === 'za')      $query->orderBy('judul', 'desc');
            })
            ->paginate(9);

        // Kembalikan HTML partial untuk dirender Alpine.js
        return view('user.edukasi.partial-list', compact('edukasies'))->render();
    }

    /**
     * Detail satu konten edukasi.
     */
    public function show(Edukasi $edukasi)
    {
        return view('user.edukasi.show', compact('edukasi'));
    }
}
