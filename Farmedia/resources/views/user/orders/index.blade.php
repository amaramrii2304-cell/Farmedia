<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pesanan Saya') }}
        </h2>
    </x-slot>

    {{-- Biar x-cloak jalan (untuk tab Alpine) --}}
    <style>[x-cloak]{ display:none !important; }</style>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div x-data="{ tab: 'semua' }" class="space-y-4">

                {{-- Header --}}
                <div class="bg-white shadow-sm rounded-xl px-6 py-4 border border-emerald-100">
                    <h3 class="text-lg font-semibold text-gray-800">
                        Riwayat Pesanan
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Daftar pesanan yang pernah kamu buat di Farmedia.
                    </p>
                </div>

                @if ($orders->isEmpty())
                    {{-- Empty state --}}
                    <div class="bg-white shadow-sm rounded-xl px-6 py-10 text-center border border-dashed border-gray-300">
                        <div class="mx-auto mb-4 flex items-center justify-center w-12 h-12 rounded-full bg-emerald-50 text-emerald-600">
                            🛒
                        </div>
                        <p class="text-gray-700 font-semibold text-sm">
                            Kamu belum memiliki pesanan.
                        </p>
                        <p class="text-gray-500 text-xs mt-1">
                            Yuk belanja kebutuhan pertanianmu di halaman Beranda.
                        </p>
                    </div>
                @else
                    {{-- Tabs status ala Shopee --}}
                    <div class="bg-white shadow-sm rounded-xl border border-emerald-100 overflow-hidden">
                        <div class="flex overflow-x-auto text-xs sm:text-sm">
                            @php
                                $tabs = [
                                    'semua'                => 'Semua',
                                    'menunggu_konfirmasi'  => 'Menunggu Konfirmasi',
                                    'diproses'             => 'Diproses',
                                    'dikirim'              => 'Dikirim',
                                    'siap_diambil'         => 'Siap Diambil',
                                    'selesai'              => 'Selesai',
                                    'pending_cancel'       => 'Menunggu Pembatalan',
                                    'dibatalkan'           => 'Dibatalkan',
                                ];
                            @endphp

                            @foreach($tabs as $key => $label)
                                <button type="button"
                                        @click="tab = '{{ $key }}'"
                                        class="flex-1 px-4 py-2 whitespace-nowrap border-b-2 transition
                                               hover:bg-emerald-50"
                                        :class="tab === '{{ $key }}'
                                            ? 'border-emerald-500 text-emerald-600 bg-emerald-50'
                                            : 'border-transparent text-gray-600'">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- LIST PESANAN --}}
                    <div class="space-y-4">
                        @foreach ($orders as $order)
                            @php
                                $status = $order->status_order;

                                $statusLabel = match ($status) {
                                    'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
                                    'diproses'            => 'Diproses',
                                    'dikirim'             => 'Dikirim',
                                    'siap_diambil'        => 'Siap Diambil',
                                    'selesai'             => 'Selesai',
                                    'pending_cancel'      => 'Menunggu Pembatalan',
                                    'dibatalkan'          => 'Dibatalkan',
                                    default               => ucfirst(str_replace('_', ' ', $status)),
                                };

                                $badgeClass = match ($status) {
                                    'menunggu_konfirmasi' => 'bg-yellow-100 text-yellow-700',
                                    'diproses'            => 'bg-blue-100 text-blue-700',
                                    'dikirim'             => 'bg-indigo-100 text-indigo-700',
                                    'siap_diambil'        => 'bg-purple-100 text-purple-700',
                                    'selesai'             => 'bg-green-100 text-green-700',
                                    'pending_cancel'      => 'bg-orange-100 text-orange-700',
                                    'dibatalkan'          => 'bg-gray-200 text-gray-700',
                                    default               => 'bg-gray-100 text-gray-700',
                                };

                                $totalItems = $order->items->sum('jumlah');

                                // ambil nama toko dari item pertama (kalau ada)
                                $firstItem = $order->items->first();
                                $shopName = optional(optional(optional($firstItem)->product)->mitra)->nama_toko ?? 'Mitra Farmedia';

                                $orderCode = 'FM-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
                            @endphp

                            <div x-show="tab === 'semua' || tab === '{{ $status }}'"
                                 x-cloak
                                 class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

                                {{-- HEADER KARTU PESANAN --}}
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 px-4 py-3 border-b border-gray-100 bg-emerald-50">
                                    <div class="flex items-center gap-2 text-sm text-gray-800">
                                        <span class="font-semibold">{{ $shopName }}</span>
                                        <span class="w-1 h-1 rounded-full bg-gray-400"></span>
                                        <span class="text-xs text-gray-500">
                                            {{ $order->created_at->format('d M Y, H:i') }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] text-gray-500">No. Pesanan: {{ $orderCode }}</span>
                                        <span class="px-3 py-1 rounded-full text-[11px] font-semibold {{ $badgeClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>
                                </div>

                                {{-- DAFTAR PRODUK DALAM PESANAN --}}
                                <div class="px-4 py-3 space-y-3">
                                    @foreach ($order->items as $item)
                                        <div class="flex gap-3">
                                            <div class="w-16 h-16 rounded-md overflow-hidden bg-gray-100 flex-shrink-0">
                                                @if($item->product && $item->product->foto_produk)
                                                    <img src="{{ asset('storage/' . $item->product->foto_produk) }}"
                                                         alt="{{ $item->product->nama_produk }}"
                                                         class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center text-[10px] text-gray-400">
                                                        Tidak ada foto
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-800 line-clamp-2">
                                                    {{ $item->product->nama_produk ?? 'Produk tidak tersedia' }}
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    {{ $item->jumlah }} × Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- FOOTER: TOTAL & AKSI --}}
                                <div class="px-4 py-3 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-gray-50">
                                    <div class="text-sm text-gray-700">
                                        <span class="mr-1">Total {{ $totalItems }} barang:</span>
                                        <span class="font-semibold text-emerald-700">
                                            Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                                        </span>
                                    </div>

                                    <div class="flex justify-end gap-2">
                                        {{-- Tombol detail pesanan --}}
                                        <a href="{{ route('orders.show', $order->id) }}"
                                           class="px-4 py-1.5 bg-emerald-600 text-white rounded-full text-xs font-semibold hover:bg-emerald-700">
                                            Detail Pesanan
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
