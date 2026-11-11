<?php

// app/Models/CodSettlement.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodSettlement extends Model
{
    protected $fillable = [
        'order_id',
        'expected_amount',
        'received_amount',
        'received_at',
        'received_note',
        'received_by',
        'platform_fee',
        'service_fee',
        'net_to_seller',
        'status',
        'payout_status',
        'payout_at',
        'payout_ref',
        'paid_by',
    ];

    public function order() { return $this->belongsTo(Order::class); }
}
