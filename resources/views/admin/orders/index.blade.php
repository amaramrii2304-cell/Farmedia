<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Data Pesanan</h2>
    </x-slot>

    @php
        // 1) Urutkan: terbaru dulu (created_at desc), fallback id desc
        // NOTE: $orders bisa paginator atau collection
        $ordersCollection = method_exists($orders, 'getCollection') ? $orders->getCollection() : collect($orders);

        $sorted = $ordersCollection->sortByDesc(function($o){
            return $o->created_at ?? $o->id;
        });

        // 2) Label status untuk tampilan
        $statusLabels = [
            'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
            'confirmed'           => 'Diproses (Data Lama)',
            'diproses'            => 'Diproses',
            'dikemas'             => 'Dikemas',
            'siap_dikirim'        => 'Siap Dikirim',
            'dikirim'             => 'Dikirim',
            'siap_diambil'        => 'Siap Diambil',
            'pending_cancel'      => 'Menunggu Pembatalan',
            'selesai'             => 'Selesai',
            'rejected'            => 'Ditolak',
            'dibatalkan'          => 'Dibatalkan',
        ];

        // 3) Urutan section yang kamu mau tampil
        $statusOrder = [
            'menunggu_konfirmasi',
            'confirmed',
            'diproses',
            'dikemas',
            'siap_dikirim',
            'dikirim',
            'siap_diambil',
            'pending_cancel',
            'selesai',
            'rejected',
            'dibatalkan',
        ];

        // 4) Grouping berdasarkan status_order
        $grouped = $sorted->groupBy(fn($o) => $o->status_order ?? 'unknown');

        // Helper badge kelas
        $badgeClass = function($status){
            return match($status){
                'menunggu_konfirmasi' => 'bg-amber-50 text-amber-700 border-amber-200',
                'diproses','dikemas','siap_dikirim','dikirim','siap_diambil','confirmed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'pending_cancel' => 'bg-orange-50 text-orange-700 border-orange-200',
                'selesai' => 'bg-sky-50 text-sky-700 border-sky-200',
                'rejected','dibatalkan' => 'bg-rose-50 text-rose-700 border-rose-200',
                default => 'bg-slate-50 text-slate-700 border-slate-200',
            };
        };
    @endphp

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">

        {{-- SECTION PER STATUS --}}
        @foreach($statusOrder as $st)
            @php $list = $grouped->get($st, collect()); @endphp
            @if($list->count() > 0)
                <div class="bg-white rounded-2xl border p-5 overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $badgeClass($st) }}">
                                {{ $statusLabels[$st] ?? strtoupper($st) }}
                            </span>
                            <span class="text-xs text-gray-500">
                                {{ $list->count() }} pesanan
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-500">
                                    <th class="py-2 pr-3">ID</th>
                                    <th class="py-2 pr-3">Pembeli</th>
                                    <th class="py-2 pr-3">Mitra</th>
                                    <th class="py-2 pr-3">Status</th>
                                    <th class="py-2 pr-3">Resi</th>
                                    <th class="py-2 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($list as $o)
                                    <tr class="border-t">
                                        <td class="py-2 pr-3 font-mono">ORD-{{ $o->id }}</td>
                                        <td class="py-2 pr-3">{{ $o->user->name ?? '-' }}</td>
                                        <td class="py-2 pr-3">{{ $o->mitra->nama_toko ?? '-' }}</td>
                                        <td class="py-2 pr-3">
                                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $badgeClass($o->status_order) }}">
                                                {{ $statusLabels[$o->status_order] ?? ($o->status_order ?? '-') }}
                                            </span>
                                        </td>
                                        <td class="py-2 pr-3">{{ $o->nomor_resi ?? '-' }}</td>
                                        <td class="py-2 text-right">
                                            <a class="text-emerald-700 underline"
                                               href="{{ route('admin.orders.show', $o->id) }}">
                                               Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endforeach

        {{-- STATUS TIDAK DIKENAL (kalau ada) --}}
        @php $unknown = $grouped->get('unknown', collect()); @endphp
        @if($unknown->count() > 0)
            <div class="bg-white rounded-2xl border border-rose-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold bg-rose-50 text-rose-700 border-rose-200">
                            Status Tidak Dikenal
                        </span>
                        <span class="text-xs text-gray-500">{{ $unknown->count() }} pesanan</span>
                    </div>
                </div>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th>ID</th>
                            <th>Pembeli</th>
                            <th>Mitra</th>
                            <th>Status</th>
                            <th>Resi</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unknown as $o)
                            <tr class="border-t">
                                <td class="py-2 font-mono">ORD-{{ $o->id }}</td>
                                <td>{{ $o->user->name ?? '-' }}</td>
                                <td>{{ $o->mitra->nama_toko ?? '-' }}</td>
                                <td class="text-rose-700 font-semibold">{{ $o->status_order ?? '-' }}</td>
                                <td>{{ $o->nomor_resi ?? '-' }}</td>
                                <td class="text-right">
                                    <a class="text-emerald-700 underline"
                                       href="{{ route('admin.orders.show', $o->id) }}">
                                       Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <p class="mt-3 text-xs text-rose-700">
                    ⚠️ Ada status yang tidak terdaftar di sistem. Sebaiknya dinormalisasi agar alur aksi tidak macet.
                </p>
            </div>
        @endif

        {{-- Pagination tetap --}}
        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>
</x-app-layout>