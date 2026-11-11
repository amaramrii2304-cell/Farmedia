<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'comment',
    ];

    // Relasi ke produk
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relasi ke user (pembeli)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke order (opsional)
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
