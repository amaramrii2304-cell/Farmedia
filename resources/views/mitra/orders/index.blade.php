<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-lg text-gray-800 leading-tight">
                    Daftar Pesanan Pembeli
                </h2>
                <p class="text-xs text-gray-500">
                    Kelola pesanan yang masuk ke toko Farmedia Anda.
                </p>
            </div>
        </div>
    </x-slot>

    @php
        // ========= Helper: normalisasi status lama =========
        $normalizeStatus = function ($st) {
            return $st === 'confirmed' ? 'menunggu_konfirmasi' : $st; // confirmed dianggap belum diproses (data lama)
        };

        // ========= Helper: label status =========
        $statusMap = [
            'menunggu_konfirmasi' => ['label' => 'Menunggu konfirmasi', 'bg' => 'bg-amber-50',  'text' => 'text-amber-700',  'border' => 'border-amber-200'],
            'diproses'            => ['label' => 'Diproses',             'bg' => 'bg-emerald-50','text' => 'text-emerald-700','border' => 'border-emerald-200'],
            'dikemas'             => ['label' => 'Dikemas',              'bg' => 'bg-sky-50',    'text' => 'text-sky-700',    'border' => 'border-sky-200'],
            'siap_dikirim'        => ['label' => 'Siap dikirim',         'bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200'],
            'dikirim'             => ['label' => 'Dikirim',              'bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200'],
            'siap_diambil'        => ['label' => 'Siap diambil',         'bg' => 'bg-teal-50',   'text' => 'text-teal-700',   'border' => 'border-teal-200'],
            'selesai'             => ['label' => 'Selesai',              'bg' => 'bg-emerald-600','text' => 'text-white',     'border' => 'border-emerald-600'],
            'pending_cancel'      => ['label' => 'Menunggu pembatalan',  'bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'border' => 'border-orange-200'],
            'rejected'            => ['label' => 'Ditolak',              'bg' => 'bg-rose-50',   'text' => 'text-rose-700',   'border' => 'border-rose-200'],
            'dibatalkan'          => ['label' => 'Dibatalkan',           'bg' => 'bg-red-50',    'text' => 'text-red-700',    'border' => 'border-red-200'],
        ];

        // ========= Tab / filter =========
        $currentView = $view ?? request('view', 'aktif');

        // Tab yang diminta user: per status + ringkas
        $tabs = [
            'aktif'      => 'Aktif',
            'menunggu'   => 'Menunggu',
            'diproses'   => 'Diproses',
            'dikemas'    => 'Dikemas',
            'siap_dikirim'=> 'Siap Dikirim',
            'dikirim'    => 'Dikirim',
            'selesai'    => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            'semua'      => 'Semua',
        ];

        // ========= Data (urut terbaru paling atas) =========
        $sortedOrders = $orders->sortByDesc(function($o){
            return $o->created_at ?? now();
        });

        // ========= Filter sesuai tab =========
        $filteredOrders = $sortedOrders->filter(function($o) use ($currentView, $normalizeStatus) {
            $st = $normalizeStatus($o->status_order);

            if ($currentView === 'semua') return true;

            if ($currentView === 'aktif') {
                return in_array($st, [
                    'menunggu_konfirmasi',
                    'diproses',
                    'dikemas',
                    'siap_dikirim',
                    'dikirim',
                    'siap_diambil',
                    'pending_cancel',
                ], true);
            }

            if ($currentView === 'menunggu') {
                return in_array($st, ['menunggu_konfirmasi','pending_cancel'], true);
            }

            if ($currentView === 'dibatalkan') {
                return in_array($st, ['rejected','dibatalkan'], true);
            }

            // tab status spesifik
            return $st === $currentView;
        });

        $countLabel = $filteredOrders->count();
    @endphp

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-4 lg:px-6 space-y-4">

            {{-- Flash success --}}
            @if (session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-3 py-2 rounded-md text-xs">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Flash error (penting supaya kamu tahu kenapa gagal) --}}
            @if (session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-3 py-2 rounded-md text-xs">
                    {{ session('error') }}
                </div>
            @endif

            {{-- TAB FILTER --}}
            <div class="flex flex-wrap items-center gap-2 mb-2 text-xs">
                @foreach($tabs as $key => $label)
                    @php $active = $currentView === $key; @endphp
                    <a href="{{ route('mitra.orders.index', ['view' => $key]) }}"
                       class="inline-flex items-center rounded-full border px-3 py-1.5
                              {{ $active
                                    ? 'bg-emerald-600 text-white border-emerald-600'
                                    : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Ringkasan kecil --}}
            <div class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-emerald-50 border border-emerald-100 text-xs text-emerald-800">
                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-600 text-[10px] text-white font-semibold">
                    {{ $countLabel }}
                </span>
                <span>
                    Menampilkan pesanan: <b>{{ $tabs[$currentView] ?? 'Aktif' }}</b>
                </span>
            </div>

            {{-- LIST --}}
            @if ($filteredOrders->isEmpty())
                <div class="bg-white rounded-lg border border-slate-200 p-4 text-xs text-slate-500">
                    Tidak ada pesanan pada kategori ini.
                </div>
            @else
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">

                    <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-900">
                            Daftar Pesanan (Terbaru di atas)
                        </p>
                        <p class="text-xs text-slate-500">
                            {{ $countLabel }} pesanan
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-3 py-2 text-left font-semibold text-slate-500 uppercase tracking-wide">Order</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-500 uppercase tracking-wide">Tanggal</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-500 uppercase tracking-wide">Pembeli</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-500 uppercase tracking-wide">Total</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-500 uppercase tracking-wide">Pengiriman</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-500 uppercase tracking-wide">Pembayaran</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                                    <th class="px-3 py-2 text-right font-semibold text-slate-500 uppercase tracking-wide">Item</th>
                                    <th class="px-3 py-2 text-right font-semibold text-slate-500 uppercase tracking-wide">Aksi</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-200">
                                @foreach($filteredOrders as $order)
                                    @php
                                        $totalQty = (int) $order->items->sum('jumlah');
                                        $totalHarga = (float) $order->items->sum(function($item) {
                                            return (float)$item->jumlah * (float)$item->harga_satuan;
                                        });

                                        $rawStatus = $order->status_order;
                                        $status = $normalizeStatus($rawStatus);

                                        $statusStyle = $statusMap[$status] ?? [
                                            'label'  => ucfirst(str_replace('_', ' ', (string)$status)),
                                            'bg'     => 'bg-slate-100',
                                            'text'   => 'text-slate-700',
                                            'border' => 'border-slate-200',
                                        ];

                                        $payMethod = strtolower((string)($order->metode_pembayaran ?? ''));
                                        $payStatus = strtolower((string)($order->payment_status ?? 'pending'));

                                        // badge pembayaran
                                        $payMethodLabel = strtoupper($payMethod ?: '-');
                                        $payBadge = 'bg-slate-50 text-slate-700 border-slate-200';

                                        if ($payMethod === 'transfer') $payBadge = 'bg-amber-50 text-amber-700 border-amber-200';
                                        if ($payMethod === 'cod')      $payBadge = 'bg-sky-50 text-sky-700 border-sky-200';
                                        if ($payMethod === 'cash')     $payBadge = 'bg-teal-50 text-teal-700 border-teal-200';

                                        // teks status pembayaran
                                        $payStatusLabel = match($payStatus) {
                                            'paid' => 'PAID',
                                            'waiting_verification' => 'MENUNGGU VERIFIKASI',
                                            'failed' => 'GAGAL',
                                            'expired' => 'KADALUARSA',
                                            default => strtoupper($payStatus ?: 'PENDING'),
                                        };

                                        // tombol konfirmasi boleh muncul utk menunggu_konfirmasi dan confirmed (lama)
                                        $canConfirm = in_array($rawStatus, ['menunggu_konfirmasi','confirmed'], true);
                                    @endphp

                                    <tr class="hover:bg-slate-50">
                                        {{-- Order --}}
                                        <td class="px-3 py-3 align-top whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <span class="text-xs font-semibold text-slate-900">
                                                    ORD-{{ $order->id }}
                                                </span>
                                                @if(!empty($order->nomor_resi))
                                                    <span class="text-[11px] text-slate-500">
                                                        Resi: <span class="font-mono">{{ $order->nomor_resi }}</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Tanggal --}}
                                        <td class="px-3 py-3 align-top whitespace-nowrap">
                                            <span class="text-xs text-slate-700">
                                                {{ $order->created_at?->format('d M Y, H:i') ?? '-' }}
                                            </span>
                                        </td>

                                        {{-- Pembeli --}}
                                        <td class="px-3 py-3 align-top">
                                            <div class="flex flex-col">
                                                <span class="text-xs font-semibold text-slate-900">
                                                    {{ $order->user->name ?? '-' }}
                                                </span>
                                                @if($order->user?->email)
                                                    <span class="text-[11px] text-slate-500 truncate max-w-[180px]">
                                                        {{ $order->user->email }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Total --}}
                                        <td class="px-3 py-3 align-top whitespace-nowrap">
                                            <span class="text-xs font-semibold text-emerald-700">
                                                Rp {{ number_format($totalHarga, 0, ',', '.') }}
                                            </span>
                                        </td>

                                        {{-- Pengiriman --}}
                                        <td class="px-3 py-3 align-top whitespace-nowrap">
                                            <span class="text-xs text-slate-700">
                                                {{ ($order->metode_pengiriman ?? '') === 'pickup' ? 'Ambil di toko' : 'Kirim ke alamat' }}
                                            </span>
                                        </td>

                                        {{-- Pembayaran --}}
                                        <td class="px-3 py-3 align-top">
                                            <div class="flex flex-col gap-1">
                                                <span class="inline-flex w-fit items-center px-2.5 py-1 rounded-full border text-[11px] font-medium {{ $payBadge }}">
                                                    {{ $payMethodLabel }}
                                                </span>

                                                @if ($payMethod === 'transfer')
                                                    <span class="text-[11px] text-slate-500">
                                                        Status: <b>{{ $payStatusLabel }}</b>
                                                    </span>
                                                @else
                                                    <span class="text-[11px] text-emerald-600 font-semibold">
                                                        LUNAS DI TEMPAT
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Status --}}
                                        <td class="px-3 py-3 align-top">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-[11px] font-medium {{ $statusStyle['bg'] }} {{ $statusStyle['text'] }} {{ $statusStyle['border'] }}">
                                                {{ $statusStyle['label'] }}
                                            </span>

                                            @if($rawStatus === 'confirmed')
                                                <div class="text-[10px] text-slate-400 mt-1">
                                                    (data lama: confirmed)
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Item --}}
                                        <td class="px-3 py-3 align-top text-right whitespace-nowrap">
                                            <span class="text-xs text-slate-700">
                                                {{ $totalQty }} {{ $totalQty > 1 ? 'items' : 'item' }}
                                            </span>
                                        </td>

                                        {{-- Aksi --}}
                                        <td class="px-3 py-3 align-top text-right">
                                            <div class="inline-flex items-center gap-2">
                                                @if ($canConfirm)
                                                    <form action="{{ route('mitra.orders.confirm', $order->id) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('Konfirmasi pesanan ini?')">
                                                        @csrf
                                                        <button type="submit"
                                                                class="px-3 py-1.5 rounded-full bg-emerald-600 text-[11px] text-white hover:bg-emerald-700">
                                                            Konfirmasi
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('mitra.orders.reject', $order->id) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('Tolak pesanan ini?')">
                                                        @csrf
                                                        <button type="submit"
                                                                class="px-3 py-1.5 rounded-full border border-rose-300 bg-rose-50 text-[11px] text-rose-700 hover:bg-rose-100">
                                                            Tolak
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('mitra.orders.show', $order->id) }}"
                                                       class="inline-flex px-3 py-1.5 rounded-full border border-slate-300 text-[11px] font-medium text-slate-700 hover:bg-slate-50">
                                                        Detail
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            @endif

        </div>
    </div>
</x-app-layout>