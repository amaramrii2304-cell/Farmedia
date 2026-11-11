<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Laporan Penjualan
        </h2>
    </x-slot>

        @php
        $fromLabel = $from
            ? \Carbon\Carbon::parse($from)->translatedFormat('d M Y')
            : 'Awal';

        $toLabel = $to
            ? \Carbon\Carbon::parse($to)->translatedFormat('d M Y')
            : 'Hari ini';
    @endphp


    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- FILTER PERIODE --}}
        <div class="bg-white rounded-2xl shadow-sm border border-emerald-50 p-5">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold text-emerald-700 tracking-wide mb-1">
                        Laporan Penjualan Toko Anda
                    </p>
                    <p class="text-sm text-gray-500">
                        Periode: <span class="font-semibold text-gray-800">{{ $fromLabel }}</span>
                        &mdash;
                        <span class="font-semibold text-gray-800">{{ $toLabel }}</span>
                    </p>
                </div>

                <form method="GET" action="{{ route('mitra.sales.index') }}"
                      class="flex flex-wrap items-center gap-3 text-sm">
                    <div>
    <label for="filter-dari" class="block text-xs text-gray-500 mb-1">Dari tanggal</label>
    <input type="date" name="from" id="filter-dari"
           value="{{ $from ? \Carbon\Carbon::parse($from)->format('Y-m-d') : '' }}"
           class="rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
</div>
<div>
    <label for="filter-sampai" class="block text-xs text-gray-500 mb-1">Sampai tanggal</label>
    <input type="date" name="to" id="filter-sampai"
           value="{{ $to ? \Carbon\Carbon::parse($to)->format('Y-m-d') : '' }}"
           class="rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
</div>

                    <div class="flex gap-2 pt-5 md:pt-0">
                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700">
                            Terapkan
                        </button>
                        <a href="{{ route('mitra.sales.index') }}"
                           class="px-3 py-2 rounded-xl border text-xs text-gray-600 hover:bg-gray-50">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- KARTU RINGKASAN --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl shadow-sm border border-emerald-50 p-4">
                <p class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wide">Total Omzet</p>
                <p class="mt-2 text-2xl font-extrabold text-emerald-800">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </p>
                <p class="mt-1 text-[11px] text-gray-500">Dari pesanan selesai pada periode ini.</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-emerald-50 p-4">
                <p class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wide">Total Pesanan</p>
                <p class="mt-2 text-2xl font-extrabold text-emerald-800">
                    {{ $totalOrders }}
                </p>
                <p class="mt-1 text-[11px] text-gray-500">Order dengan status <b>selesai</b>.</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-emerald-50 p-4">
                <p class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wide">Produk Terjual</p>
                <p class="mt-2 text-2xl font-extrabold text-emerald-800">
                    {{ $totalItems }}
                </p>
                <p class="mt-1 text-[11px] text-gray-500">Total item yang keluar dari stok.</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-emerald-50 p-4">
    <p class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wide">
        Pencairan COD (Masuk Rekening)
    </p>
    <p class="mt-2 text-2xl font-extrabold text-emerald-800">
        Rp {{ number_format($codPayoutTotal ?? 0, 0, ',', '.') }}
    </p>
    <p class="mt-1 text-[11px] text-gray-500">
        Total transfer admin ke mitra ({{ $codPayoutCount ?? 0 }} transaksi) pada periode ini.
    </p>
</div>
        </div>

        {{-- GRAFIK PENJUALAN --}}
        <div class="bg-white rounded-2xl shadow-sm border border-emerald-50 p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-emerald-700">
                    Grafik Omzet Per Hari
                </h3>
            </div>

            @if($chartLabels->isEmpty())
                <p class="text-sm text-gray-500">
                    Belum ada data penjualan pada periode ini.
                </p>
            @else
                <div class="h-52">
                    <canvas id="salesChart" class="w-full h-full"></canvas>
                </div>
            @endif
        </div>

        {{-- DAFTAR TRANSAKSI --}}
        <div class="bg-white rounded-2xl shadow-sm border border-emerald-50 p-5">
            <h3 class="text-sm font-semibold text-emerald-700 mb-3">
                Riwayat Transaksi
            </h3>

            @if($orders->isEmpty())
                <p class="text-sm text-gray-500">Belum ada pesanan selesai pada periode ini.</p>
            @else
                <div class="space-y-3">
                    @foreach($orders as $order)
                        @php
                            $total = $order->items->sum('subtotal');
                            $qty   = $order->items->sum('jumlah');
                        @endphp

                        <div class="rounded-xl border border-gray-100 hover:border-emerald-200
                                    hover:shadow-sm transition bg-gray-50/70 px-4 py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
                                    <span class="font-semibold text-gray-800">
                                        ORD-{{ $order->id }}
                                    </span>
                                    <span class="hidden md:inline">•</span>
                                    <span>{{ $order->created_at->format('d M Y, H:i') }}</span>
                                    <span class="hidden md:inline">•</span>
                                    <span>{{ $order->user->name ?? 'Pembeli' }}</span>
                                    @if(strtolower((string)$order->metode_pembayaran) === 'cod')
    @php $cs = $order->codSettlement; @endphp

    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
        {{ ($cs?->status === 'paid') ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
        COD: {{ ($cs?->status === 'paid') ? 'SUDAH DITRANSFER' : 'BELUM DITRANSFER' }}
    </span>

    @if($cs?->status === 'paid')
        <span class="text-[11px] text-gray-500">
            • Masuk: Rp {{ number_format((float)$cs->net_to_seller,0,',','.') }}
            @if(!empty($cs->payout_ref)) • Ref: {{ $cs->payout_ref }} @endif
        </span>
    @endif
@endif
                                </div>

                                <div class="mt-1 text-xs text-gray-500">
                                    @foreach($order->items as $item)
                                        <div>
                                            • {{ $item->product->nama_produk ?? 'Produk dihapus' }}
                                            ({{ $item->jumlah }}x)
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex items-end md:items-center gap-4 text-sm">
                                <div class="text-right">
                                    <p class="text-[11px] text-gray-500">Qty</p>
                                    <p class="font-semibold text-gray-800">{{ $qty }} item</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[11px] text-gray-500">Total</p>
                                    <p class="font-semibold text-emerald-700">
                                        Rp {{ number_format($total, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- CHART.JS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
    <script>
        const labels = @json($chartLabels);
        const values = @json($chartValues);

        if (labels.length > 0) {
            const ctx = document.getElementById('salesChart').getContext('2d');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        data: values,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16,185,129,0.12)',
                        borderWidth: 2,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#059669',
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const val = ctx.parsed.y || 0;
                                    return 'Rp ' + val.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(16,185,129,0.08)' },
                            ticks: {
                                font: { size: 11 },
                                callback: (value) => 'Rp ' + value.toLocaleString('id-ID')
                            }
                        }
                    }
                }
            });
        }
    </script>
</x-app-layout>
