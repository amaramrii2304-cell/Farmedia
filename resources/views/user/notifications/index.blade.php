{{-- resources/views/user/notifications/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Notifikasi Pesanan Saya
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            {{-- Info kecil di atas --}}
            <div class="mb-4 bg-emerald-50 border border-emerald-100 text-emerald-800 px-4 py-3 rounded-xl text-sm">
                <p class="font-semibold text-xs uppercase tracking-[0.18em]">
                    NOTIFIKASI PESANAN
                </p>
                <p class="mt-1 text-xs text-emerald-900">
                    Update status pesanan Anda akan muncul di sini.
                </p>
            </div>

            @if ($orders->isEmpty())
                <div class="bg-white shadow-sm border border-dashed border-gray-200 rounded-2xl p-8 text-center text-gray-500 text-sm">
                    Belum ada notifikasi pesanan.
                    <div class="mt-2">
                        <a href="{{ route('user.beranda') }}"
                           class="inline-flex items-center px-3 py-1.5 rounded-full bg-emerald-600 text-white text-xs font-medium hover:bg-emerald-700">
                            Mulai Belanja
                        </a>
                    </div>
                </div>
            @else
                <div class="space-y-3">

                    @php
                        // Map status → label + warna
                        $statusMap = [
                            'menunggu_konfirmasi' => [
                                'label' => 'Menunggu Konfirmasi Mitra',
                                'class' => 'bg-yellow-100 text-yellow-800',
                            ],
                            'diproses' => [
                                'label' => 'Sedang Diproses',
                                'class' => 'bg-blue-100 text-blue-800',
                            ],
                            'dikemas' => [
                                'label' => 'Sedang Dikemas',
                                'class' => 'bg-sky-100 text-sky-800',
                            ],
                            'dikirim' => [
                                'label' => 'Sedang Dikirim',
                                'class' => 'bg-indigo-100 text-indigo-800',
                            ],
                            'siap_diambil' => [
                                'label' => 'Siap Diambil',
                                'class' => 'bg-teal-100 text-teal-800',
                            ],
                            'selesai' => [
                                'label' => 'Pesanan Selesai',
                                'class' => 'bg-emerald-100 text-emerald-800',
                            ],
                            'rejected' => [
                                'label' => 'Pesanan Ditolak Mitra',
                                'class' => 'bg-red-100 text-red-800',
                            ],
                            'dibatalkan' => [
                                'label' => 'Pesanan Dibatalkan',
                                'class' => 'bg-gray-100 text-gray-700',
                            ],
                            'pending_cancel' => [
                                'label' => 'Pengajuan Pembatalan Diproses',
                                'class' => 'bg-orange-100 text-orange-800',
                            ],
                        ];
                    @endphp

                    @foreach ($orders as $order)
                        @php
                            $status = $order->status_order;
                            $info   = $statusMap[$status] ?? [
                                'label' => ucfirst(str_replace('_', ' ', $status)),
                                'class' => 'bg-gray-100 text-gray-800',
                            ];

                            // Ambil 1–2 nama produk saja untuk ringkasan
                            $produkList = $order->items->map(function ($item) {
                                return $item->product->nama_produk ?? 'Produk dihapus';
                            })->take(2)->implode(', ');

                            if ($order->items->count() > 2) {
                                $produkList .= ' + ' . ($order->items->count() - 2) . ' produk lain';
                            }
                        @endphp

                        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-4 flex gap-3">
                            {{-- Titik timeline di kiri --}}
                            <div class="pt-2">
                                <span class="inline-flex w-2 h-2 rounded-full
                                    @if(in_array($status, ['selesai','dibatalkan'])) bg-gray-300
                                    @elseif(in_array($status, ['rejected'])) bg-red-400
                                    @else bg-emerald-500 @endif"></span>
                            </div>

                            <div class="flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs text-gray-500">
                                            Kode Pesanan
                                        </p>
                                        <p class="text-sm font-semibold text-gray-900">
                                            ORD-{{ $order->id }}
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500">
                                            Toko:
                                            <span class="font-medium text-gray-700">
                                                {{ $order->mitra->nama_toko ?? '-' }}
                                            </span>
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500">
                                            Produk:
                                            <span class="font-medium text-gray-700">
                                                {{ $produkList }}
                                            </span>
                                        </p>
                                    </div>

                                    <div class="text-right">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $info['class'] }}">
                                            {{ $info['label'] }}
                                        </span>
                                        <p class="mt-2 text-[11px] text-gray-400">
                                            Diupdate: {{ $order->updated_at?->format('d M Y, H:i') }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Pesan pendek sesuai status --}}
                                <p class="mt-2 text-xs text-gray-600">
                                    @switch($status)
                                        @case('menunggu_konfirmasi')
                                            Pesanan Anda sudah masuk dan menunggu konfirmasi dari mitra.
                                            @break
                                        @case('diproses')
                                            Mitra sedang menyiapkan pesanan Anda.
                                            @break
                                        @case('dikemas')
                                            Pesanan sedang dikemas oleh mitra.
                                            @break
                                        @case('dikirim')
                                            Pesanan sudah dikirim. Cek detail pesanan untuk nomor resi.
                                            @break
                                        @case('siap_diambil')
                                            Pesanan siap diambil di toko mitra.
                                            @break
                                        @case('selesai')
                                            Pesanan selesai. Terima kasih sudah berbelanja di Farmedia!
                                            @break
                                        @case('rejected')
                                            Maaf, pesanan ini ditolak oleh mitra. Silakan cek detail pesanan.
                                            @break
                                        @case('dibatalkan')
                                            Pesanan ini telah dibatalkan.
                                            @break
                                        @case('pending_cancel')
                                            Pengajuan pembatalan Anda sedang ditinjau mitra.
                                            @break
                                        @default
                                            Status pesanan: {{ $info['label'] }}.
                                    @endswitch
                                </p>

                                <div class="mt-3 flex justify-between items-center">
                                    <p class="text-[11px] text-gray-400">
                                        Dibuat: {{ $order->created_at?->format('d M Y, H:i') }}
                                    </p>

                                    <a href="{{ route('orders.show', $order->id) }}"
                                       class="inline-flex items-center px-3 py-1.5 rounded-full bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700">
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
</x-app-layout>
