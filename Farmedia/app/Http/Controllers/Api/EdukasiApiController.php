<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Edukasi;          // sesuaikan nama model kamu
use App\Models\EdukasiKategori;  // kalau ada tabel kategori

class EdukasiApiController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $categoryId = $request->query('category_id');
        $sort = $request->query('sort', 'terbaru'); // terbaru|terlama

        $query = Edukasi::query();

        // kalau ada relasi kategori: edukasi->kategori()
        // $query->with('kategori');

        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('judul', 'like', "%$q%")
                  ->orWhere('isi', 'like', "%$q%")
                  ->orWhere('ringkasan', 'like', "%$q%");
            });
        }

        if ($categoryId) {
            $query->where('kategori_id', $categoryId);
        }

        if ($sort === 'terlama') $query->orderBy('created_at', 'asc');
        else $query->orderBy('created_at', 'desc');

        $data = $query->paginate(10);

        return response()->json($data);
    }

    public function show($id)
    {
        $edu = Edukasi::find($id);

        if (!$edu) {
            return response()->json([
                'data' => null,
                'message' => 'Edukasi tidak ditemukan'
            ], 404);
        }

        // ✅ BUNGKUS DALAM "data" supaya cocok dengan EdukasiDetailResponse di Android
        return response()->json([
            'data' => $edu
        ]);
    }

    public function categories()
    {
        // kalau kamu memang punya tabel kategori edukasi
        $cats = EdukasiKategori::orderBy('nama')->get();
        return response()->json($cats);
    }
}
