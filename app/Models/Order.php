<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Order extends Model
{
    protected $fillable = [
        'user_id',
        'mitra_id',
        'total_harga',
        'ongkir',
        'metode_pembayaran',
        'alamat_pengiriman',
        'metode_pengiriman',
        'nomor_resi',
        'status_order',
        'status_order_before',
        'bank_nama',
        'bank_nomor',
        'bank_pemilik',
        'qris_image',
        'komisi_produk',
        'komisi_ongkir',
        'payment_status',
        'payment_deadline',
        'komisi_lunas_at',
        'selesai_at',
        'payment_proof_path',
        'paid_at',
        'transfer_proof_path',
        'transfer_status',
        'transfer_submitted_at',
        'transfer_verified_at',
        'transfer_rejected_at',
    ];

    protected $casts = [
        // kalau sudah ada, tinggal tambahkan ini
        'payment_deadline' => 'datetime',
        'komisi_lunas_at'  => 'datetime',
        'selesai_at'       => 'datetime',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'transfer_deadline_at' => 'datetime',
        'transfer_submitted_at' => 'datetime',
        'transfer_verified_at' => 'datetime',
        'transfer_rejected_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
{
    return $this->hasMany(OrderItem::class, 'order_id');
}

    public function mitra()
{
    return $this->belongsTo(Mitra::class, 'mitra_id');
}

public static function generateResi()
{
    return 'RS' . strtoupper(Str::random(10));
}

public function codSettlement()
{
    return $this->hasOne(\App\Models\CodSettlement::class);
}

public function commissionInvoices()
{
    return $this->belongsToMany(\App\Models\CommissionInvoice::class, 'commission_invoice_orders', 'order_id', 'commission_invoice_id')
        ->withTimestamps();
}

}