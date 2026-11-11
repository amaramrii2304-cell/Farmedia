<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mitra extends Model
{
    use HasFactory;

    protected $table = 'mitras';

    protected $fillable = [
        'nama_toko',
        'email',
        'password',
        'alamat',
        'logo_path',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'qris_image',
        'telepon',
        'pickup_address',
        'is_holiday',
        'holiday_message',

    ];

    // Relasi: satu mitra punya banyak produk
    public function products()
    {
    return $this->hasMany(\App\Models\Product::class, 'mitra_id');
    }

    public function user()
    {
    return $this->hasOne(User::class, 'mitra_id');
    }

    protected $casts = [
    'is_holiday' => 'boolean',
    ];

}
