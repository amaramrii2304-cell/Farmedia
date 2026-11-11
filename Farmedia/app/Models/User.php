<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'mitra_id',
        'alamat',          // ✅ TAMBAH INI
        'alamat_lengkap',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** RELASI: User → Mitra */
    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'mitra_id');
    }

    public function reviews()
    {
        return $this->hasMany(\App\Models\ProductReview::class);
    }
}
