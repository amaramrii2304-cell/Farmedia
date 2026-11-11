<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Komisi Platform
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Banner info komisi --}}
            <div class="bg-emerald-50 border border-emerald-100 rounded-2xl px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <p class="text-[11px] uppercase tracking-[0.18em] text-emerald-700 font-semibold">
                        Ringkasan Komisi Farmedia
                    </p>
                    <p class="mt-1 text-sm text-gray-800">
                        Platform mengambil komisi
                        <span class="font-semibold">{{ (int)($commissionRate * 100) }}%</span>
                        dari nilai produk serta 5% dari nilai ongkir untuk setiap pesanan selesai.
                    </p>
                </div>
                <div class="text-right text-xs text-gray-500">
                    <p>Terakhir diperbarui: {{ now()->format('d M Y H:i') }}</p>
                </div>
            </div>

            {{-- 3 kartu kecil: omzet, komisi, bersih mitra --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Omzet produk --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-gray-500">
                        Total Omzet Produk
                    </p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">
                        Rp {{ number_format($totalBruto, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        Akumulasi nilai produk dari semua pesanan berstatus selesai.
                    </p>
                </div>

                {{-- Komisi platform --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-gray-500">
                        Total Komisi Platform
                    </p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-700">
                        Rp {{ number_format($totalCommission, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-xs text-emerald-600">
                        {{ (int)($commissionRate * 100) }}% × total omzet produk.
                    </p>
                </div>

                {{-- Estimasi bersih mitra --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-gray-500">
                        Estimasi Bersih Mitra
                    </p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">
                        Rp {{ number_format($totalNetForMitra, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        Nilai produk setelah potongan komisi (belum termasuk ongkir ke mitra).
                    </p>
                </div>
            </div>

            {{-- ✅ KARTU KE-4: Pemasukan Admin dari COD --}}
<div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
    <p class="text-[11px] uppercase tracking-[0.16em] text-gray-500">
        Pemasukan Admin dari COD
    </p>

    <p class="mt-2 text-2xl font-semibold text-indigo-700">
        Rp {{ number_format((float)($codAdminIncomePaid ?? 0), 0, ',', '.') }}
    </p>

    <p class="mt-1 text-xs text-gray-500">
        Akumulasi komisi + biaya layanan dari transaksi COD yang sudah ditandai <b>paid</b>.
    </p>

    <div class="mt-3 text-xs text-gray-600 space-y-1">
        <div class="flex items-center justify-between">
            <span>Komisi platform (COD)</span>
            <span class="font-semibold text-gray-900">
                Rp {{ number_format((float)($codPlatformPaid ?? 0), 0, ',', '.') }}
            </span>
        </div>
        <div class="flex items-center justify-between">
            <span>Biaya layanan (COD)</span>
            <span class="font-semibold text-gray-900">
                Rp {{ number_format((float)($codServicePaid ?? 0), 0, ',', '.') }}
            </span>
        </div>
    </div>
</div>

            {{-- Hutang Komisi --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
            <p class="text-[11px] uppercase tracking-[0.16em] text-gray-500">
                Hutang Komisi Mitra (Cash Pickup)
            </p>
            <p class="mt-2 text-2xl font-semibold text-amber-700">
                Rp {{ number_format($totalOutstanding, 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-gray-500">
            Total komisi yang belum disetor mitra untuk pesanan ambil di toko & bayar cash.
            </p>
        </div>

            {{-- Ringkasan per Mitra --}}
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">
                        Ringkasan Komisi per Mitra
                    </h3>
                    <span class="text-[11px] text-gray-400">
                        Berdasarkan pesanan berstatus selesai
                    </span>
                </div>

                @if($perMitra->isEmpty())
                    <div class="px-5 py-6 text-sm text-gray-500">
                        Belum ada data komisi. Pesanan selesai belum ditemukan.
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($perMitra as $row)
                            <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $row['nama_toko'] }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        ID Mitra: {{ $row['mitra_id'] }}
                                    </p>
                                </div>
                                <div class="text-right text-xs sm:text-sm">
                                    <p class="text-gray-500">
                                        Omzet produk:
                                        <span class="font-semibold text-gray-900">
                                            Rp {{ number_format($row['omzet'], 0, ',', '.') }}
                                        </span>
                                    </p>
                                    <p class="mt-1 text-emerald-700 font-semibold">
                                        Komisi platform:
                                        Rp {{ number_format($row['commission'], 0, ',', '.') }}
                                    </p>
                                    <p class="mt-1 text-amber-700 font-semibold">
                                        Hutang komisi:
                                        Rp {{ number_format($row['outstanding'] ?? 0, 0, ',', '.') }}
                                    </p>

                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Daftar pesanan terbaru + komisi per order --}}
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">
                        Pesanan Selesai Terbaru
                    </h3>
                    <span class="text-[11px] text-gray-400">
                        Menampilkan {{ $latestOrders->count() }} dari total {{ $latestOrders->total() }} pesanan
                    </span>
                </div>

                @if($latestOrders->isEmpty())
                    <div class="px-5 py-6 text-sm text-gray-500">
                        Belum ada pesanan selesai.
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($latestOrders as $order)
    @php
        $orderSubtotal       = $order->items->sum('subtotal');
        $orderCommissionProd = $order->komisi_produk;
        $orderCommissionShip = $order->komisi_ongkir;
        $orderCommission     = $orderCommissionProd + $orderCommissionShip;
    @endphp

                            <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div class="space-y-1">
                                    <p class="text-sm font-semibold text-gray-900">
                                        ORD-{{ $order->id }}
                                        <span class="ml-2 text-[11px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">
                                            {{ $order->mitra->nama_toko ?? 'Mitra #' . $order->mitra_id }}
                                        </span>
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $order->user->name ?? 'Pembeli' }} &middot;
                                        {{ $order->created_at?->format('d M Y H:i') }}
                                    </p>
                                </div>

                                <div class="text-right text-xs sm:text-sm space-y-1">
                                    <p class="text-gray-500">
                                        Nilai produk:
                                        <span class="font-semibold text-gray-900">
                                            Rp {{ number_format($orderSubtotal, 0, ',', '.') }}
                                        </span>
                                    </p>
                                    <p class="text-emerald-700 font-semibold">
                                        Komisi platform:
                                        Rp {{ number_format($orderCommission, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="px-5 py-3 border-t border-gray-100">
                        {{ $latestOrders->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
