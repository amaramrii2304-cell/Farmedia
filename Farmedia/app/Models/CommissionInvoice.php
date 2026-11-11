<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionInvoice extends Model
{
    protected $fillable = [
        'mitra_id',
        'amount',
        'penalty',
        'total_due',
        'due_date',
        'status',
        'payment_method',
        'payment_proof_path',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'paid_at'  => 'datetime',
    ];

    public function mitra()
    {
        return $this->belongsTo(Mitra::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'commission_invoice_orders', 'commission_invoice_id', 'order_id')
            ->withTimestamps();
    }

    // ===== aturan denda: 1% per minggu, max 10% =====
    public function computedPenalty(): float
    {
        if (!$this->due_date) return 0;
        if (!in_array($this->status, ['unpaid', 'waiting_verification'])) return (float) $this->penalty;

        if (now()->lte($this->due_date)) return 0;

        $daysLate = now()->diffInDays($this->due_date);
        $weeksLate = (int) ceil($daysLate / 7);

        $raw = (float)$this->amount * 0.01 * $weeksLate;
        $cap = (float)$this->amount * 0.10;

        return (float) min($raw, $cap);
    }

    public function computedTotalDue(): float
    {
        return (float) $this->amount + (float) $this->computedPenalty();
    }

    public function isOverdue(): bool
    {
        return $this->due_date && now()->gt($this->due_date) && in_array($this->status, ['unpaid', 'waiting_verification']);
    }
}
