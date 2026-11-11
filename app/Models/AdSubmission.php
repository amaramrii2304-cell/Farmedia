<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdSubmission extends Model
{
    protected $fillable = [
        'mitra_id',
        'product_id',
        'placement',
        'target_kategori',
        'kategori_snapshot',

        'duration_days', // ✅ TAMBAH (biar paket durasi ke-save)

        'status',
        'start_at',
        'end_at',
        'payment_method',
        'payment_status',
        'payment_proof',
        'paid_at',
        'price',
        'admin_note',
    ];

    protected $casts = [
        'duration_days' => 'integer', // ✅ TAMBAH
        'start_at'      => 'datetime',
        'end_at'        => 'datetime',
        'paid_at'       => 'datetime',
        'price'         => 'integer',
    ];

    // (opsional) gampang buat cek status
    public function getIsPaidAttribute(): bool
    {
        return ($this->payment_status ?? '') === 'paid';
    }

    public function mitra()
    {
        return $this->belongsTo(\App\Models\Mitra::class, 'mitra_id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id');
    }
}
