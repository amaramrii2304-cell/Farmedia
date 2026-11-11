<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class MitraSalesReportApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $mitraId = $user->mitra_id ?? null;

        if (!$mitraId) {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini tidak terhubung dengan mitra.',
                'data' => null
            ], 403);
        }

        [$from, $to] = $this->parseRange($request->query('from'), $request->query('to'));

        $group = strtolower($request->query('group', 'daily'));
        if (!in_array($group, ['daily', 'weekly', 'monthly'])) $group = 'daily';

        // ====== TABLES ======
        $ordersTable = 'orders';
        $itemsTable  = 'order_items';
        $prodTable   = 'products';
        $usersTable  = 'users';
        $codTable    = 'cod_settlements';

        // ====== COLUMNS (AMAN) ======
        $statusCol    = $this->pickCol($ordersTable, ['status_order', 'status']);
        $payCol       = $this->pickCol($ordersTable, ['metode_pembayaran', 'payment_method']);
        $userIdCol    = $this->pickCol($ordersTable, ['user_id', 'buyer_id']);
        $buyerNameCol = $this->pickCol($ordersTable, ['nama_pembeli', 'buyer_name']);

        // ✅ PENTING: RANGE SELALU BERDASARKAN created_at (biar gak kosong)
        $dateCol = Schema::hasColumn($ordersTable, 'created_at')
            ? 'created_at'
            : ($this->pickCol($ordersTable, ['updated_at', 'selesai_at', 'completed_at']) ?? 'created_at');

        $qtyCol      = $this->pickCol($itemsTable, ['jumlah', 'qty', 'quantity']);
        $subCol      = $this->pickCol($itemsTable, ['subtotal', 'line_total', 'total']);
        $prodNameCol = $this->pickCol($prodTable, ['nama_produk', 'name', 'nama']);

        // ✅ status sukses CASE-INSENSITIVE
        $successStatusesLower = ['selesai', 'completed', 'done', 'diterima', 'sudah_diterima'];

        $rangeStart = $from->copy()->startOfDay();
        $rangeEnd   = $to->copy()->endOfDay();

        // ====== BASE ITEMS ======
        $baseItems = DB::table($ordersTable)
            ->join($itemsTable, "$itemsTable.order_id", '=', "$ordersTable.id")
            ->join($prodTable, "$prodTable.id", '=', "$itemsTable.product_id")
            ->where("$prodTable.mitra_id", $mitraId)
            ->whereBetween("$ordersTable.$dateCol", [$rangeStart, $rangeEnd]);

        if ($statusCol) {
            $baseItems->whereIn(DB::raw("LOWER($ordersTable.$statusCol)"), $successStatusesLower);
        }

        $totalOrders = (clone $baseItems)->distinct("$ordersTable.id")->count("$ordersTable.id");
        $itemsSold   = $qtyCol ? (int) (clone $baseItems)->sum("$itemsTable.$qtyCol") : 0;
        $totalSales  = $subCol ? (int) round((float) (clone $baseItems)->sum("$itemsTable.$subCol")) : 0;

        // ====== COD PAID (PENCAIRAN) ======
        $codPaid = 0;
        $codPaidCount = 0;

        $codStatusCol = Schema::hasTable($codTable) ? $this->pickCol($codTable, ['status']) : null;
        $codPayoutAt  = Schema::hasTable($codTable) ? $this->pickCol($codTable, ['payout_at', 'paid_at', 'updated_at', 'created_at']) : null;
        $codNetCol    = Schema::hasTable($codTable) ? $this->pickCol($codTable, ['net_to_seller', 'net_amount', 'amount']) : null;

        if (Schema::hasTable($codTable) && $codStatusCol && $codPayoutAt && $codNetCol) {
            $codPaidBase = DB::table($codTable)
                ->join($ordersTable, "$ordersTable.id", '=', "$codTable.order_id")
                ->where("$codTable.$codStatusCol", 'paid')
                ->whereBetween("$codTable.$codPayoutAt", [$rangeStart, $rangeEnd]);

            if ($payCol) {
                $codPaidBase->whereRaw("LOWER($ordersTable.$payCol) = ?", ['cod']);
            }

            if (Schema::hasColumn($ordersTable, 'mitra_id')) {
                $codPaidBase->where("$ordersTable.mitra_id", $mitraId);
            } else {
                $codPaidBase->whereExists(function ($sub) use ($mitraId, $ordersTable, $itemsTable, $prodTable) {
                    $sub->select(DB::raw(1))
                        ->from($itemsTable)
                        ->join($prodTable, "$prodTable.id", '=', "$itemsTable.product_id")
                        ->whereColumn("$itemsTable.order_id", "$ordersTable.id")
                        ->where("$prodTable.mitra_id", $mitraId);
                });
            }

            $codPaid      = (int) round((float) (clone $codPaidBase)->sum("$codTable.$codNetCol"));
            $codPaidCount = (int) (clone $codPaidBase)->count();
        }

        // ====== TRANSACTIONS ======
        $rows = DB::table($ordersTable)
            ->join($itemsTable, "$itemsTable.order_id", '=', "$ordersTable.id")
            ->join($prodTable, "$prodTable.id", '=', "$itemsTable.product_id")
            ->selectRaw("
                $ordersTable.id as order_id,
                $ordersTable.$dateCol as dt,
                CONCAT('ORD-', $ordersTable.id) as order_no,
                " . ($qtyCol ? "SUM($itemsTable.$qtyCol)" : "0") . " as qty_total,
                " . ($subCol ? "SUM($itemsTable.$subCol)" : "0") . " as total_mitra
            ")
            ->where("$prodTable.mitra_id", $mitraId)
            ->whereBetween("$ordersTable.$dateCol", [$rangeStart, $rangeEnd]);

        if ($statusCol) {
            $rows->whereIn(DB::raw("LOWER($ordersTable.$statusCol)"), $successStatusesLower);
        }

        $rows = $rows->groupBy("$ordersTable.id", "$ordersTable.$dateCol")
            ->orderByDesc("$ordersTable.$dateCol")
            ->limit(100)
            ->get();

        $transactions = $rows->map(function ($row) use (
            $ordersTable, $itemsTable, $prodTable, $usersTable,
            $mitraId, $userIdCol, $buyerNameCol, $payCol,
            $prodNameCol, $qtyCol,
            $codTable, $codStatusCol, $codNetCol
        ) {
            $dt = $row->dt ? Carbon::parse($row->dt) : null;

            // buyer_name
            $buyerName = null;
            if ($buyerNameCol) {
                $buyerName = DB::table($ordersTable)->where('id', $row->order_id)->value($buyerNameCol);
            }
            if (!$buyerName && $userIdCol && Schema::hasTable($usersTable) && Schema::hasColumn($usersTable, 'name')) {
                $uid = DB::table($ordersTable)->where('id', $row->order_id)->value($userIdCol);
                if ($uid) $buyerName = DB::table($usersTable)->where('id', $uid)->value('name');
            }

            // metode
            $metode = $payCol ? (string) DB::table($ordersTable)->where('id', $row->order_id)->value($payCol) : null;

            // product_summary (ambil 1 produk pertama utk mitra + qty total)
            $productSummary = null;
            if ($prodNameCol && $qtyCol) {
                $first = DB::table($itemsTable)
                    ->join($prodTable, "$prodTable.id", '=', "$itemsTable.product_id")
                    ->where("$itemsTable.order_id", $row->order_id)
                    ->where("$prodTable.mitra_id", $mitraId)
                    ->select("$prodTable.$prodNameCol as nm")
                    ->orderBy("$itemsTable.id")
                    ->first();

                $nm = $first->nm ?? null;
                $qtyTotal = (int) ($row->qty_total ?? 0);
                if ($nm) $productSummary = $nm . " (" . $qtyTotal . "x)";
            }

            // COD payout status
            $codPayoutStatus = null;
            $codAmountIn = null;

            if (Schema::hasTable($codTable) && $codStatusCol && $codNetCol && $metode && strtolower(trim($metode)) === 'cod') {
                $paidRow = DB::table($codTable)
                    ->where('order_id', $row->order_id)
                    ->where($codStatusCol, 'paid')
                    ->first();

                if ($paidRow) {
                    $codPayoutStatus = 'paid';
                    $codAmountIn = (int) round((float) ($paidRow->{$codNetCol} ?? 0));
                } else {
                    $codPayoutStatus = 'unpaid';
                    $codAmountIn = 0;
                }
            }

            return [
                'order_no' => $row->order_no,
                'date' => $dt ? $dt->format('Y-m-d') : null,
                'date_text' => $dt ? $dt->format('d M Y, H:i') : null,
                'qty' => (int) ($row->qty_total ?? 0),
                'total' => (int) round((float) ($row->total_mitra ?? 0)),

                'buyer_name' => $buyerName,
                'product_summary' => $productSummary,
                'metode_pembayaran' => $metode,
                'cod_payout_status' => $codPayoutStatus,
                'cod_amount_in' => $codAmountIn,
            ];
        })->values();

        // ====== CHART ======
        $chart = $this->buildChart($mitraId, $from, $to, $group, $statusCol, $successStatusesLower, $dateCol, $subCol);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
                'grouping' => $group,
                'summary' => [
                    'total_orders' => (int) $totalOrders,
                    'items_sold' => (int) $itemsSold,
                    'total_sales' => (int) $totalSales,

                    // ✅ Android card pencairan pakai ini
                    'payout_cod' => (int) $codPaid,
                    'payout_cod_count' => (int) $codPaidCount,
                ],
                'chart' => $chart,
                'transactions' => $transactions,
            ]
        ]);
    }

    // ================= HELPERS =================

    private function pickCol(string $table, array $candidates): ?string
    {
        if (!Schema::hasTable($table)) return null;
        foreach ($candidates as $c) {
            if (Schema::hasColumn($table, $c)) return $c;
        }
        return null;
    }

    private function parseRange(?string $from, ?string $to): array
    {
        $toDate = $this->parseDateFlexible($to) ?? Carbon::now();
        $fromDate = $this->parseDateFlexible($from) ?? $toDate->copy()->subDays(30);

        if ($fromDate->gt($toDate)) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }
        return [$fromDate, $toDate];
    }

    private function parseDateFlexible(?string $s): ?Carbon
    {
        if (!$s) return null;
        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return Carbon::createFromFormat('Y-m-d', $s);
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $s)) return Carbon::createFromFormat('d/m/Y', $s);
            return Carbon::parse($s);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function buildChart(
        int $mitraId,
        Carbon $from,
        Carbon $to,
        string $group,
        ?string $statusCol,
        array $successStatusesLower,
        string $dateCol,
        ?string $subCol
    ): array {
        if (!$subCol) return [];

        $ordersTable = 'orders';
        $itemsTable  = 'order_items';
        $prodTable   = 'products';

        $rangeStart = $from->copy()->startOfDay();
        $rangeEnd   = $to->copy()->endOfDay();

        $q = DB::table($ordersTable)
            ->join($itemsTable, "$itemsTable.order_id", '=', "$ordersTable.id")
            ->join($prodTable, "$prodTable.id", '=', "$itemsTable.product_id")
            ->where("$prodTable.mitra_id", $mitraId)
            ->whereBetween("$ordersTable.$dateCol", [$rangeStart, $rangeEnd]);

        if ($statusCol) {
            $q->whereIn(DB::raw("LOWER($ordersTable.$statusCol)"), $successStatusesLower);
        }

        if ($group === 'weekly') {
            $rows = (clone $q)
                ->selectRaw("YEARWEEK($ordersTable.$dateCol, 1) as bucket, MIN($ordersTable.$dateCol) as first_date, SUM($itemsTable.$subCol) as total")
                ->groupBy('bucket')->orderBy('bucket')->get();

            return $rows->map(function ($r) {
                $d = Carbon::parse($r->first_date);
                return ['label' => 'Minggu ' . $d->format('W'), 'date' => $d->format('Y-m-d'), 'value' => (int) round((float) $r->total)];
            })->toArray();
        }

        if ($group === 'monthly') {
            $rows = (clone $q)
                ->selectRaw("DATE_FORMAT($ordersTable.$dateCol, '%Y-%m') as bucket, MIN($ordersTable.$dateCol) as first_date, SUM($itemsTable.$subCol) as total")
                ->groupBy('bucket')->orderBy('bucket')->get();

            return $rows->map(function ($r) {
                $d = Carbon::parse($r->first_date);
                return ['label' => $d->format('M Y'), 'date' => $d->format('Y-m-d'), 'value' => (int) round((float) $r->total)];
            })->toArray();
        }

        $rows = (clone $q)
            ->selectRaw("DATE($ordersTable.$dateCol) as bucket, SUM($itemsTable.$subCol) as total")
            ->groupBy('bucket')->orderBy('bucket')->get();

        return $rows->map(function ($r) {
            $d = Carbon::parse($r->bucket);
            return ['label' => $d->format('d/m'), 'date' => $d->format('Y-m-d'), 'value' => (int) round((float) $r->total)];
        })->toArray();
    }
}
