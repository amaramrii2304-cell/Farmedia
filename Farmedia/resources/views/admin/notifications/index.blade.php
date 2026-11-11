<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Notifikasi Admin
        </h2>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">
        @if ($orders->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-5 py-4 text-sm text-gray-500">
                Belum ada notifikasi.
            </div>
        @else
            <div class="space-y-3">
                @foreach ($orders as $order)
                    <div class="bg-white rounded-xl shadow-sm border border-amber-100 px-5 py-4 flex gap-3">
                        <div class="w-9 h-9 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-lg">
                            🔔
                        </div>

                        <div class="flex-1">
                            {{-- Judul notif sesuai status --}}
                            @if($order->status_order === 'siap_dikirim')
                                <p class="text-sm font-semibold text-gray-900">
                                    Pesanan siap dikirim oleh Mitra
                                </p>
                            @elseif($order->status_order === 'pending_cancel')
                                <p class="text-sm font-semibold text-gray-900">
                                    Pengajuan pembatalan pesanan
                                </p>
                            @else
                                <p class="text-sm font-semibold text-gray-900">
                                    Pesanan baru dari {{ $order->user->name ?? 'Pengguna' }}
                                </p>
                            @endif

                            <p class="text-xs text-gray-500 mt-0.5">
                                Kode:
                                <span class="font-mono">ORD-{{ $order->id }}</span>
                                · Mitra: {{ $order->mitra->nama_toko ?? '-' }}
                            </p>

                            <p class="text-xs text-gray-500 mt-0.5">
                                Status:
                                @if($order->status_order === 'menunggu_konfirmasi')
                                    <span class="px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 text-[11px]">
                                        Menunggu konfirmasi mitra
                                    </span>
                                @elseif($order->status_order === 'pending_cancel')
                                    <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-[11px]">
                                        Pengajuan pembatalan
                                    </span>
                                @elseif($order->status_order === 'siap_dikirim')
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[11px]">
                                        Siap dikirim (butuh admin)
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-[11px]">
                                        {{ ucfirst(str_replace('_',' ', $order->status_order)) }}
                                    </span>
                                @endif
                            </p>

                            <p class="text-[11px] text-gray-400 mt-1">
                                {{ $order->created_at?->format('d M Y, H:i') }}
                            </p>
                        </div>

                        <div class="flex items-center">
                            <a href="{{ route('admin.orders.show', $order->id) }}"
                               class="px-3 py-1.5 rounded-full bg-emerald-600 text-white text-xs font-medium hover:bg-emerald-700">
                                Lihat detail
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
