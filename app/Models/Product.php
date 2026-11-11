<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'mitra_id',
        'nama_produk',
        'kategori_produk',
        'deskripsi_produk',
        'harga',
        'stok',
        'berat',
        'foto_produk',
    ];

    protected $appends = ['foto_url'];

    public function getFotoUrlAttribute()
    {
        $path = $this->foto_produk;
        if (!$path) return null;

        // kalau sudah URL penuh
        if (preg_match('/^https?:\/\//i', $path)) return $path;

        // rapikan variasi yang mungkin tersimpan
        $path = ltrim($path, '/');                        // "/storage/..." -> "storage/..."
        $path = preg_replace('#^storage/#', '', $path);   // "storage/produk/..." -> "produk/..."

        // Storage::url("produk/xxx.jpg") => "/storage/produk/xxx.jpg"
        // url(...) => "http://APP_URL/storage/produk/xxx.jpg"
        return url(Storage::disk('public')->url($path));
    }

    public function mitra()
    {
        return $this->belongsTo(\App\Models\Mitra::class, 'mitra_id');
    }

    public function reviews()
    {
        return $this->hasMany(\App\Models\ProductReview::class, 'product_id');
    }
}
