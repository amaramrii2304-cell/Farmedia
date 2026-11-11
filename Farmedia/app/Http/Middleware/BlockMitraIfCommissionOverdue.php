<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\CommissionInvoice;

class BlockMitraIfCommissionOverdue
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if (!$user || empty($user->mitra_id)) return $next($request);

        $hasOverdue = CommissionInvoice::where('mitra_id', (int)$user->mitra_id)
            ->whereIn('status', ['unpaid','waiting_verification'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->exists();

        if ($hasOverdue) {
            // kamu bisa ganti blokirnya: hanya blok beberapa route saja
            return redirect()->route('mitra.commission.index')
                ->with('error', 'Akun ditangguhkan sementara karena hutang komisi sudah melewati jatuh tempo. Silakan lakukan pembayaran.');
        }

        return $next($request);
    }
}
