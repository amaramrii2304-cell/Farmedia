<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detail Pesanan
                </h2>
                <p class="text-sm text-gray-500">
                    Kode pesanan <span class="font-mono font-semibold">ORD-{{ $order->id }}</span>
                </p>
            </div>

            <div class="text-xs text-gray-500">
                Dibuat: {{ $order->created_at?->format('d M Y, H:i') }}
            </div>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto space-y-4">

        {{-- FLASH MESSAGE --}}
        @if(session('success'))
            <div class="p-3 rounded-xl bg-emerald-50 text-emerald-700 text-sm border border-emerald-100">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-3 rounded-xl bg-red-50 text-red-700 text-sm border border-red-100">
                {{ session('error') }}
            </div>
        @endif

        {{-- ACTIONS --}}
        <div class="bg-white rounded-2xl border border-emerald-50 p-4 flex flex-wrap gap-2 items-center">

            {{-- Ship (Delivery + siap_dikirim) --}}
            @if($order->metode_pengiriman === 'delivery' && $order->status_order === 'siap_dikirim')
                <form method="POST" action="{{ route('admin.orders.ship', $order->id) }}">
                    @csrf
                    <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                        Kirim Pesanan (Generate Resi)
                    </button>
                </form>
            @endif

            {{-- Print Resi --}}
            @if(!empty($order->nomor_resi))
                <a href="{{ route('admin.orders.printResi', $order->id) }}"
                   class="px-4 py-2 rounded-xl bg-slate-800 text-white text-sm font-semibold hover:bg-slate-900">
                    Cetak Resi
                </a>
            @endif

            {{-- Finish (Delivery + dikirim) --}}
            @if($order->metode_pengiriman === 'delivery' && $order->status_order === 'dikirim')
                <form method="POST" action="{{ route('admin.orders.finish', $order->id) }}">
                    @csrf
                    <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                        Pesanan Selesai
                    </button>
                </form>
            @endif

            {{-- ===== VERIFIKASI TRANSFER (ESCROW) ===== --}}
            @php
                $metodePembayaran = strtolower(trim((string)($order->metode_pembayaran ?? '')));
                $isTransfer = str_contains($metodePembayaran, 'transfer');
            @endphp

            @if($isTransfer)
                @php
                    // ✅ pakai kolom baru transfer_* (fallback ke payment_* kalau masih ada)
                    $transferStatus = $order->transfer_status ?? ($order->payment_status ?? null);
                    $proofPath = $order->transfer_proof_path ?? ($order->payment_proof_path ?? ($order->payment_proof ?? null));
                @endphp

                <div class="w-full rounded-2xl border border-amber-200 bg-amber-50 p-4 mt-2">
                    <p class="text-xs font-semibold text-amber-800">Pembayaran Transfer (Escrow)</p>

                    <div class="mt-2 text-sm text-amber-900 space-y-1">
                        <p>Status pembayaran: <b>{{ $transferStatus ?? '-' }}</b></p>

                        @if($proofPath)
                            <p>
                                Bukti transfer:
                                <a class="text-emerald-700 underline" target="_blank"
                                   href="{{ route('admin.orders.transferProof', $order->id) }}">
                                    Lihat file
                                </a>
                            </p>
                        @else
                            <p class="text-xs text-amber-700">Belum ada bukti transfer.</p>
                        @endif

                        @if($order->transfer_verified_at ?? $order->paid_at)
                            <p>Dibayar pada: <b>{{ ($order->transfer_verified_at ?? $order->paid_at)?->format('d M Y, H:i') }}</b></p>
                        @endif
                    </div>

                    {{-- Tombol Approve hanya kalau submitted/waiting_verification + bukti ada --}}
                    @if(in_array(strtolower((string)($transferStatus ?? '')), ['submitted', 'waiting_verification', 'pending'], true) && $proofPath)
                        <form method="POST"
                              action="{{ route('admin.orders.approveTransfer', $order->id) }}"
                              class="mt-3"
                              onsubmit="return confirm('Setujui pembayaran transfer ini? Uang akan dicatat masuk ke escrow platform.');">
                            @csrf
                            <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                                Approve Transfer (Uang Masuk)
                            </button>
                        </form>
                    @endif
                </div>
            @endif

            {{-- Back --}}
            <a href="{{ route('admin.orders.index') }}"
               class="ml-auto px-4 py-2 rounded-xl border text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Kembali
            </a>
        </div>

        {{-- SUMMARY --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-emerald-50 p-4">
                <p class="text-[11px] uppercase tracking-wider text-gray-500">Status</p>
                <p class="mt-2 font-semibold text-gray-800">{{ $order->status_order }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    Pengiriman: {{ strtoupper($order->metode_pengiriman) }}
                </p>
            </div>

            <div class="bg-white rounded-2xl border border-emerald-50 p-4">
                <p class="text-[11px] uppercase tracking-wider text-gray-500">Pembeli</p>
                <p class="mt-2 font-semibold text-gray-800">{{ $order->user->name ?? '-' }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $order->user->email ?? '-' }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-emerald-50 p-4">
                <p class="text-[11px] uppercase tracking-wider text-gray-500">Total</p>
                <p class="mt-2 font-semibold text-emerald-700">
                    Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    Resi: <span class="font-mono">{{ $order->nomor_resi ?? '-' }}</span>
                </p>
            </div>
        </div>

        {{-- ADDRESS + MITRA --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl border border-emerald-50 p-4">
                <h3 class="text-sm font-semibold text-gray-800 mb-2">Alamat Pengiriman</h3>
                <p class="text-sm text-gray-700 leading-relaxed">
                    {{ $order->alamat_pengiriman ?? '-' }}
                </p>
            </div>

            <div class="bg-white rounded-2xl border border-emerald-50 p-4">
                <h3 class="text-sm font-semibold text-gray-800 mb-2">Mitra / Toko</h3>
                <p class="text-sm font-semibold text-gray-800">
                    {{ $order->mitra->nama_toko ?? ('Mitra #' . $order->mitra_id) }}
                </p>
                <p class="text-sm text-gray-700 leading-relaxed mt-1">
                    {{ $order->mitra->alamat ?? '-' }}
                </p>
            </div>
        </div>

        {{-- ITEMS --}}
        <div class="bg-white rounded-2xl border border-emerald-50 p-4">
            <h3 class="text-sm font-semibold text-gray-800 mb-3">Item Pesanan</h3>

            <div class="divide-y">
                @foreach($order->items as $it)
                    <div class="py-3 flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-800 truncate">
                                {{ $it->product->nama_produk ?? 'Produk dihapus' }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $it->jumlah }} x Rp {{ number_format($it->harga_satuan, 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="text-right">
                            <p class="text-xs text-gray-500">Subtotal</p>
                            <p class="font-semibold text-emerald-700">
                                Rp {{ number_format($it->subtotal, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</x-app-layout>
