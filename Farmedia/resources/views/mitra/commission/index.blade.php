<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Komisi & Pendapatan Mitra
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Komisi platform: <span class="font-semibold text-emerald-700">{{ $commissionPercent }}%</span> dari nilai produk (tanpa ongkir).
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- FILTER PERIODE --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                <form method="GET"
                      action="{{ route('mitra.commission.index') }}"
                      class="flex flex-col md:flex-row md:items-end gap-3">

                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="komisi-dari-tgl" class="text-xs font-medium text-gray-600">Dari Tanggal</label>
                            <input type="date"
                                   id="komisi-dari-tgl"
                                   name="from"
                                   value="{{ $from }}"
                                   class="mt-1 w-full rounded-lg border-gray-200 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label for="komisi-sampai-tgl" class="text-xs font-medium text-gray-600">Sampai Tanggal</label>
                            <input type="date"
                                   id="komisi-sampai-tgl"
                                   name="to"
                                   value="{{ $to }}"
                                   class="mt-1 w-full rounded-lg border-gray-200 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold shadow hover:bg-emerald-700">
                            Terapkan
                        </button>
                        <a href="{{ route('mitra.commission.index') }}"
                           class="inline-flex items-center justify-center px-4 py-2 rounded-lg border border-gray-300 text-xs text-gray-700 hover:bg-gray-50">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- RINGKASAN KOMISI --}}
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">

                {{-- Total Nilai Produk --}}
                <div class="rounded-2xl border border-gray-100 bg-white shadow-sm p-4">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-gray-500">
                        Total Nilai Produk
                    </p>
                    <p class="mt-2 text-lg font-semibold text-emerald-700">
                        Rp {{ number_format($totalProduct, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[11px] text-gray-500">
                        Akumulasi nilai produk (tanpa ongkir).
                    </p>
                </div>

                {{-- Total Komisi Platform --}}
                <div class="rounded-2xl border border-rose-100 bg-rose-50/70 shadow-sm p-4">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-rose-500">
                        Total Komisi Platform
                    </p>
                    <p class="mt-2 text-lg font-semibold text-rose-600">
                        Rp {{ number_format($totalCommission, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[11px] text-rose-500">
                        {{ $commissionPercent }}% dari nilai produk.
                    </p>
                </div>

                {{-- Hutang Komisi ke Platform (Cash Pickup) --}}
                <div class="rounded-2xl border border-amber-100 bg-amber-50/70 shadow-sm p-4">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-amber-600">
                        Hutang Komisi ke Platform
                    </p>
                    <p class="mt-2 text-lg font-semibold text-amber-700">
                        Rp {{ number_format($totalOutstanding, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[11px] text-amber-600">
                        Pesanan pickup + bayar di toko yang komisinya belum lunas.
                    </p>

                    @if(($totalOutstanding ?? 0) > 0)
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a href="{{ route('mitra.commission.pay') }}"
                               class="px-4 py-2 rounded-full bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700">
                                Bayar Hutang Komisi
                            </a>

                            @if($activeInvoice)
                                <span class="px-3 py-2 rounded-full text-xs font-semibold
                                    {{ $activeInvoice->status==='waiting_verification' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700' }}">
                                    Status: {{ $activeInvoice->status==='waiting_verification' ? 'Menunggu verifikasi' : 'Belum dibayar' }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Pendapatan Bersih Mitra --}}
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/80 shadow-sm p-4">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-emerald-600">
                        Estimasi Pendapatan Bersih
                    </p>
                    <p class="mt-2 text-lg font-semibold text-emerald-800">
                        Rp {{ number_format($totalNet, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[11px] text-emerald-600">
                        Setelah komisi + termasuk ongkir diterima.
                    </p>
                </div>

                {{-- Jumlah Pesanan --}}
                <div class="rounded-2xl border border-gray-100 bg-white shadow-sm p-4">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-gray-500">
                        Jumlah Pesanan Selesai
                    </p>
                    <p class="mt-2 text-lg font-semibold text-gray-800">
                        {{ $totalOrdersCount }} pesanan
                    </p>
                    <p class="mt-1 text-[11px] text-gray-500">
                        Dalam periode yang dipilih.
                    </p>
                </div>
            </div>

            {{-- RINCIAN PER PESANAN --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">
                        Rincian Komisi per Pesanan
                    </h3>
                    @if ($orders->count())
                        <span class="text-[11px] text-gray-400">
                            Menampilkan {{ $orders->firstItem() }}–{{ $orders->lastItem() }} dari {{ $orders->total() }} pesanan
                        </span>
                    @endif
                </div>

                @if ($orders->isEmpty())
                    <p class="text-sm text-gray-500">
                        Belum ada pesanan <span class="font-semibold">selesai</span> dalam periode ini.
                    </p>
                @else
                    <div class="space-y-3">
                        @foreach ($orders as $order)
                            @php
                                $productTotal = (float) ($order->computed_product_value ?? max(0, (float)$order->total_harga - (float)$order->ongkir));
                                $commission   = (float) ($order->computed_platform_fee ?? ((float)($order->komisi_produk ?? 0) + (float)($order->komisi_ongkir ?? 0)));
                                $net          = (float) ($order->computed_net_income ?? ((float)$order->total_harga - $commission));
                                $isPickup     = (bool)  ($order->computed_is_pickup ?? false);
                                $isOutstanding = (bool) ($order->computed_is_outstanding ?? false);
                                $outstandingAmount = (float) ($order->computed_outstanding_amt ?? ($isOutstanding ? $commission : 0));
                            @endphp

                            <div class="border border-gray-100 rounded-xl p-4 hover:border-emerald-200 hover:bg-emerald-50/40 transition">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div>
                                        <p class="text-xs text-gray-400">
                                            Kode Pesanan
                                        </p>
                                        <p class="text-sm font-semibold text-gray-900">
                                            ORD-{{ $order->id }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $order->created_at?->format('d M Y · H:i') }}
                                        </p>
                                    </div>

                                    <div class="text-xs text-right space-y-1">
                                        <p class="text-gray-500">
                                            Pembeli:
                                            <span class="font-medium text-gray-800">
                                                {{ $order->user->name ?? '-' }}
                                            </span>
                                        </p>
                                        <p class="text-gray-500">
                                            Metode:
                                            <span class="font-medium text-gray-800">
                                                {{ strtoupper((string) $order->metode_pembayaran) }}
                                                ·
                                                {{ ($order->computed_is_pickup ?? false) ? 'Ambil di Toko' : 'Dikirim' }}
                                            </span>
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                    <div>
                                        <p class="text-[11px] text-gray-500">Nilai Produk</p>
                                        <p class="mt-1 font-semibold text-gray-900">
                                            Rp {{ number_format($productTotal, 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] text-gray-500">Ongkir</p>
                                        <p class="mt-1 font-semibold text-gray-900">
                                            Rp {{ number_format((float)$order->ongkir, 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] text-rose-500">Komisi Platform (produk + ongkir)</p>
                                        <p class="mt-1 font-semibold text-rose-600">
                                            - Rp {{ number_format($commission, 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] text-emerald-600">Estimasi Bersih Mitra</p>
                                        <p class="mt-1 font-semibold text-emerald-700">
                                            Rp {{ number_format($net, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-3 pt-3 border-t border-dashed border-gray-200 flex flex-wrap justify-between gap-2">
                                    <div class="text-[11px] text-gray-500">
                                        @foreach ($order->items as $item)
                                            <div>
                                                • {{ $item->product->nama_produk ?? 'Produk dihapus' }}
                                                ({{ (int)($item->jumlah ?? $item->qty ?? $item->quantity ?? 0) }}x)
                                            </div>
                                        @endforeach
                                    </div>

                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-emerald-100 text-[11px] font-medium text-emerald-700">
                                        Status: Selesai
                                    </span>

                                    @if($isOutstanding)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full bg-amber-100 text-[11px] font-medium text-amber-700">
                                            Hutang Komisi: Rp {{ number_format($outstandingAmount, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-[11px] font-medium text-gray-700">
                                            Komisi Lunas
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $orders->withQueryString()->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>