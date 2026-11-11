<x-app-layout>
    <div class="space-y-6">

        {{-- BANNER SAMBUTAN --}}
        <div class="bg-white border border-emerald-100 rounded-2xl px-5 py-4 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="text-[11px] uppercase tracking-[0.18em] text-emerald-600 font-semibold">
                    Dashboard Mitra Farmedia
                </p>
                <h1 class="mt-1 text-lg font-semibold text-gray-900">
                    Halo, {{ auth()->user()->name }} 👋
                </h1>
                <p class="mt-1 text-xs text-gray-500">
                    Ringkasan performa toko berdasarkan pesanan yang sudah <span class="font-semibold">selesai</span>.
                </p>
            </div>
            <div class="text-right text-xs text-gray-400">
                <p>{{ now()->format('d M Y') }}</p>
                <p class="mt-1 text-[11px] text-emerald-600 font-medium">
                    Periode: {{ $periodText }}
                </p>
            </div>
        </div>

        {{-- KARTU RINGKASAN + GRAFIK --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-5">

            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">
                        Ringkasan Penjualan (30 hari terakhir)
                    </h2>
                    <p class="text-xs text-gray-500">
                        Berdasarkan pesanan dengan status <span class="font-semibold">selesai</span>.
                    </p>
                </div>

                <div class="flex flex-col items-end text-[11px] text-gray-400">
                    <span>Data otomatis dari sistem pesanan.</span>
                </div>
            </div>

            {{-- Empat kartu kecil --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

                {{-- Total Penjualan --}}
                <div class="rounded-xl border border-gray-100 p-4 bg-slate-50/60">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-gray-500">
                        Total Penjualan
                    </p>
                    <p class="mt-2 text-lg font-semibold text-emerald-700">
                        Rp {{ number_format($totalSales, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[11px] text-gray-500">
                        Omzet kotor dari semua pesanan selesai.
                    </p>
                </div>

                {{-- Pesanan Selesai --}}
                <div class="rounded-xl border border-gray-100 p-4 bg-slate-50/60">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-gray-500">
                        Pesanan Selesai
                    </p>
                    <p class="mt-2 text-lg font-semibold text-emerald-700">
                        {{ $totalOrders }} pesanan
                    </p>
                    <p class="mt-1 text-[11px] text-gray-500">
                        Total {{ $totalQty }} item terjual.
                    </p>
                </div>

                {{-- Produk Aktif --}}
                <div class="rounded-xl border border-gray-100 p-4 bg-slate-50/60">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-gray-500">
                        Produk Aktif
                    </p>
                    <p class="mt-2 text-lg font-semibold text-emerald-700">
                        {{ $activeProducts }} produk
                    </p>
                    <p class="mt-1 text-[11px] text-gray-500">
                        Produk milik Anda dengan stok &gt; 0.
                    </p>
                </div>

                {{-- Pendapatan Bersih (perkiraan) --}}
                <div class="rounded-xl border border-gray-100 p-4 bg-slate-50/60">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-gray-500">
                        Perkiraan Pendapatan Bersih
                    </p>
                    <p class="mt-2 text-lg font-semibold text-emerald-700">
                        Rp {{ number_format($netRevenue, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[11px] text-gray-500">
                        Setelah potongan komisi {{ $commissionPercent }}%.
                    </p>
                </div>
            </div>

            {{-- Grafik Penjualan --}}
            <div class="mt-3">
                <p class="text-sm font-semibold text-gray-900 mb-2">
                    Tren Penjualan 7 Hari Terakhir
                </p>
                <div class="h-52">
                    <canvas id="salesChart" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>

        @php
    $pendingOrders = \App\Models\Order::with(['user', 'items.product'])
        ->where('mitra_id', auth()->user()->mitra_id)
        ->where('status_order', 'menunggu_konfirmasi')
        ->orderByDesc('created_at')
        ->take(5)
        ->get();
@endphp

{{-- NOTIFIKASI PESANAN BARU --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-3">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-sm font-semibold text-gray-900">
                Pesanan Menunggu Konfirmasi
            </h2>
            <p class="text-xs text-gray-500">
                Daftar pesanan baru yang perlu segera Anda respon.
            </p>
        </div>

        <a href="{{ route('mitra.orders.index') }}"
           class="text-[11px] text-emerald-600 hover:text-emerald-700 hover:underline">
            Lihat semua
        </a>
    </div>

    @if($pendingOrders->isEmpty())
        <p class="text-xs text-gray-400">
            Belum ada pesanan baru yang menunggu konfirmasi.
        </p>
    @else
        <div class="space-y-2">
            @foreach($pendingOrders as $order)
                @php
                    $totalQty = $order->items->sum('jumlah');
                    $totalHarga = $order->items->sum(function($item) {
                        return $item->jumlah * $item->harga_satuan;
                    });
                @endphp

                <div class="flex items-start justify-between gap-3 rounded-xl border border-emerald-50 bg-emerald-50/60 px-3 py-2.5">
                    <div class="text-xs">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 text-[10px] font-semibold text-emerald-700">
                                #{{ $order->id }}
                            </span>
                            <span class="font-semibold text-gray-800">
                                {{ $order->user->name ?? 'Pembeli' }}
                            </span>
                        </div>

                        <p class="mt-1 text-[11px] text-gray-500">
                            {{ $totalQty }} item · Rp {{ number_format($totalHarga, 0, ',', '.') }}
                        </p>
                        <p class="mt-0.5 text-[11px] text-gray-400">
                            {{ $order->created_at?->diffForHumans() }}
                        </p>
                    </div>

                    <div class="flex flex-col items-end gap-1">
                        <span class="px-2 py-0.5 rounded-full bg-yellow-100 text-[10px] font-medium text-yellow-800">
                            Menunggu konfirmasi
                        </span>

                        <a href="{{ route('mitra.orders.show', $order->id) }}"
                           class="mt-1 inline-flex items-center justify-center px-3 py-1 rounded-full bg-emerald-600 text-[11px] text-white hover:bg-emerald-700">
                            Kelola
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

    </div>

    {{-- SCRIPT GRAFIK (REAL DATA) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const canvas = document.getElementById('salesChart');
            if (!canvas) return;

            const labels      = @json($chartLabels);
            const salesData   = @json($chartSales);
            const ordersData  = @json($chartOrders);

            new Chart(canvas, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Total Penjualan (Rp)',
                            data: salesData,
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16,185,129,0.15)',
                            borderWidth: 2,
                            tension: 0.4,
                            pointRadius: 2.5,
                            pointBackgroundColor: '#10B981',
                            yAxisID: 'y',
                            fill: true,
                        },
                        {
                            label: 'Jumlah Pesanan',
                            data: ordersData,
                            borderColor: '#FBBF24',
                            backgroundColor: 'rgba(251,191,36,0.15)',
                            borderWidth: 2,
                            tension: 0.4,
                            pointRadius: 2.5,
                            pointBackgroundColor: '#FBBF24',
                            yAxisID: 'y1',
                            fill: false,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            labels: { font: { size: 10 } }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    if (ctx.dataset.yAxisID === 'y') {
                                        const value = ctx.parsed.y || 0;
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                    return (ctx.parsed.y || 0) + ' pesanan';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10 }, color: '#9CA3AF' }
                        },
                        y: {
                            position: 'left',
                            grid: { color: 'rgba(209,213,219,0.4)' },
                            ticks: {
                                font: { size: 10 },
                                color: '#6B7280',
                                callback: function (value) {
                                    return (value >= 1000000)
                                        ? (value / 1000000) + ' jt'
                                        : value.toLocaleString('id-ID');
                                }
                            }
                        },
                        y1: {
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            ticks: {
                                font: { size: 10 },
                                color: '#6B7280'
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
