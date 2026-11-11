<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Detail Pesanan
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @php
                $status = $order->status_order;

                $statusMeta = [
                    'menunggu_konfirmasi' => ['Menunggu Konfirmasi', 'bg-amber-100 text-amber-700'],
                    'diproses'            => ['Diproses', 'bg-blue-100 text-blue-700'],
                    'dikirim'             => ['Dikirim', 'bg-indigo-100 text-indigo-700'],
                    'siap_diambil'        => ['Siap Diambil', 'bg-purple-100 text-purple-700'],
                    'selesai'             => ['Pesanan Selesai', 'bg-green-100 text-green-700'],
                    'pending_cancel'      => ['Menunggu Persetujuan Pembatalan', 'bg-orange-100 text-orange-700'],
                    'dibatalkan'          => ['Dibatalkan', 'bg-gray-200 text-gray-700'],
                ];

                $meta       = $statusMeta[$status] ?? [ucfirst($status), 'bg-gray-100 text-gray-700'];
                $statusText = $meta[0];
                $statusBadgeClass = $meta[1];

                $orderCode = 'FM-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
            @endphp

            {{-- ===================== KARTU BESAR DETAIL PESANAN ===================== --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl border border-emerald-100 overflow-hidden">

                {{-- HEADER: STATUS + INFO PESANAN --}}
                <div class="px-6 py-4 bg-gradient-to-r from-emerald-700 via-emerald-600 to-emerald-500 text-white flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-white/15 flex items-center justify-center">
                            🧾
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-emerald-100">
                                Status Pesanan
                            </p>
                            <p class="text-lg font-semibold">
                                {{ $statusText }}
                            </p>
                        </div>
                    </div>

                    <div class="text-xs md:text-right">
                        <p class="text-emerald-100">
                            No. Pesanan: <span class="font-semibold text-white">{{ $orderCode }}</span>
                        </p>
                        <p class="text-emerald-100 mt-1">
                            Tanggal: {{ $order->created_at->format('d M Y, H:i') }}
                        </p>
                        <span class="inline-flex mt-2 px-3 py-1 rounded-full text-[11px] font-semibold bg-white/15">
                            {{ $statusText }}
                        </span>
                    </div>
                </div>

                {{-- BODY KARTU --}}
                <div class="px-6 py-5 space-y-6">

                    {{-- ========== ALAMAT & INFO PENGIRIMAN ========== --}}
                    <div class="grid md:grid-cols-2 gap-4 text-sm">
                        {{-- Alamat pengiriman --}}
                        <div>
                            <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-2">
                                Alamat Pengiriman
                            </h3>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $order->user->name }}
                            </p>
                            <p class="text-gray-700 dark:text-gray-300 text-sm mt-1">
                                {{ $order->alamat_pengiriman }}
                            </p>
                        </div>

                        {{-- Info pengiriman & pembayaran singkat --}}
                        <div>
                            <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-2">
                                Info Pengiriman & Pembayaran
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                Metode Pengiriman:
                                <span class="font-semibold text-gray-800 dark:text-gray-100">
                                    {{ $order->metode_pengiriman === 'pickup' ? 'Ambil di Toko (Pickup)' : 'Dikirim ke Alamat' }}
                                </span>
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                Metode Pembayaran:
                                <span class="font-semibold text-gray-800 dark:text-gray-100">
                                    {{ strtoupper($order->metode_pembayaran) }}
                                </span>
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                Ongkos Kirim:
                                <span class="font-semibold">
                                    Rp {{ number_format($order->ongkir, 0, ',', '.') }}
                                </span>
                            </p>
                        </div>

                        {{-- ========== INSTRUKSI PEMBAYARAN TRANSFER BANK ========== --}}
                        @if(str_contains(strtolower($order->metode_pembayaran), 'transfer'))
                            @php
                                // ✅ ambil bukti & status dari semua kemungkinan kolom (baru + lama)
                                $proofPath = $order->transfer_proof_path
                                    ?? ($order->payment_proof_path ?? ($order->payment_proof ?? null));

                                $transferStatus = $order->transfer_status
                                    ?? ($order->payment_status ?? null);

                                $transferStatusNorm = strtolower(trim((string)($transferStatus ?? '')));

                                // dianggap sudah beres kalau verified/paid
                                $isPaid = in_array($transferStatusNorm, ['verified', 'paid'], true)
                                    || strtolower(trim((string)($order->payment_status ?? ''))) === 'paid';

                                $bank = config('ads.bank');
                            @endphp

                            <div class="mt-4 md:col-span-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">
                                <p class="font-semibold mb-1">Pembayaran via Transfer Bank</p>

                                {{-- Kalau belum upload bukti --}}
                                @if(empty($proofPath) && !$isPaid)

                                    <p class="mb-1">
                                        Silakan transfer sebesar
                                        <span class="font-bold">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                                        ke rekening <b>PLATFORM</b> berikut:
                                    </p>

                                    <ul class="ml-4 list-disc mb-2">
                                        <li>Bank: <b>{{ $bank['nama'] ?? '-' }}</b></li>
                                        <li>No. Rekening: <b>{{ $bank['nomor'] ?? '-' }}</b></li>
                                        <li>Atas Nama: <b>{{ $bank['pemilik'] ?? '-' }}</b></li>
                                    </ul>

                                    <form action="{{ route('orders.uploadProof', $order->id) }}"
                                          method="POST"
                                          enctype="multipart/form-data"
                                          class="mt-3">
                                        @csrf

                                        <label class="block text-[11px] text-amber-700 mb-1">Upload bukti transfer</label>
                                        <input type="file" name="payment_proof" required class="block w-full text-xs" />

                                        <button class="mt-2 px-4 py-2 rounded-full bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700">
                                            Upload Bukti Transfer
                                        </button>
                                    </form>

                                    @if($order->payment_deadline)
                                        <p class="mb-1 mt-2">
                                            Batas pembayaran:
                                            <b>{{ $order->payment_deadline->format('d M Y, H:i') }}</b>
                                        </p>
                                    @endif

                                    <p class="text-[11px] text-amber-700 mt-2">
                                        Setelah transfer, silakan upload bukti pembayaran. Pembayaran akan diverifikasi oleh admin.
                                    </p>

                                @else
                                    {{-- ✅ sudah upload / sudah verified --}}
                                    <p class="mb-1">
                                        Status pembayaran:
                                        <b>{{ $transferStatus ?? '-' }}</b>
                                    </p>

                                    @if($isPaid)
                                        <p class="text-[11px] text-emerald-700 mt-2 font-semibold">
                                            Pembayaran sudah diverifikasi.
                                        </p>
                                    @else
                                        <p class="text-[11px] text-amber-700 mt-2 font-semibold">
                                            Bukti sudah diupload. Menunggu verifikasi admin.
                                        </p>

                                        {{-- boleh upload ulang kalau belum verified --}}
                                        <form action="{{ route('orders.uploadProof', $order->id) }}"
                                              method="POST"
                                              enctype="multipart/form-data"
                                              class="mt-3">
                                            @csrf

                                            <label class="block text-[11px] text-amber-700 mb-1">Upload ulang bukti (jika salah)</label>
                                            <input type="file" name="payment_proof" required class="block w-full text-xs" />

                                            <button class="mt-2 px-4 py-2 rounded-full bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700">
                                                Upload Ulang Bukti Transfer
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        @endif

                    </div>

                    {{-- ========== TRACKING STATUS PESANAN ========== --}}
                    <div>
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-3">
                            Tracking Pesanan
                        </h3>

                        @php
                            $pickup = $order->metode_pengiriman === 'pickup';

                            $stepsPickup = [
                                'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
                                'diproses'            => 'Diproses (Menyiapkan Produk)',
                                'siap_diambil'        => 'Siap Diambil',
                                'selesai'             => 'Sudah Diambil / Selesai',
                            ];

                            $stepsDelivery = [
                                'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
                                'diproses'            => 'Diproses (Dikemas)',
                                'dikirim'             => 'Dikirim oleh Kurir',
                                'selesai'             => 'Pesanan Selesai',
                            ];

                            $steps = $pickup ? $stepsPickup : $stepsDelivery;

                            $keys = array_keys($steps);
                            $currentIndex = array_search($order->status_order, $keys);
                        @endphp

                        <div class="mt-4 space-y-6">
                            @foreach ($steps as $key => $label)
                                @php
                                    $index      = array_search($key, $keys);
                                    $isCompleted = $index <= $currentIndex;
                                    $isLast      = $index === count($steps) - 1;
                                @endphp

                                <div class="relative flex items-start gap-4">

                                    @if (!$isLast)
                                        <div class="absolute left-1.5 top-5 w-0.5 h-[calc(100%-10px)]
                                            {{ $isCompleted ? 'bg-emerald-500' : 'bg-gray-300' }}">
                                        </div>
                                    @endif

                                    <div class="z-10 mt-0.5">
                                        @if ($isCompleted)
                                            <div class="w-4 h-4 rounded-full bg-emerald-600"></div>
                                        @else
                                            <div class="w-4 h-4 rounded-full bg-gray-300"></div>
                                        @endif
                                    </div>

                                    <div>
                                        <p class="font-medium {{ $isCompleted ? 'text-emerald-600' : 'text-gray-500' }}">
                                            {{ $label }}
                                        </p>

                                        @if ($isCompleted)
                                            <p class="text-xs text-gray-500">
                                                {{ $order->updated_at->format('d/m/Y H:i') }}
                                            </p>
                                        @endif
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ========== PRODUK YANG DIBELI ========== --}}
                    <div>
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-3">
                            Produk Dipesan
                        </h3>

                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($order->items as $item)
                                @php
                                    $review = isset($userReviews)
                                        ? ($userReviews[$item->product_id] ?? null)
                                        : null;
                                @endphp

                                <div class="py-4 space-y-3">
                                    <div class="flex items-center gap-4">
                                        <div class="w-20 h-20 rounded-md overflow-hidden bg-gray-100 dark:bg-gray-900 flex-shrink-0">
                                            @if($item->product && $item->product->foto_produk)
                                                <img src="{{ asset('storage/' . $item->product->foto_produk) }}"
                                                     class="w-full h-full object-cover"
                                                     alt="{{ $item->product->nama_produk }}">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-xs text-gray-400">
                                                    Tidak ada foto
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ $item->product->nama_produk ?? 'Produk tidak tersedia' }}
                                            </p>
                                            <p class="text-sm text-gray-500 mt-1">
                                                {{ $item->jumlah }} × Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}
                                            </p>
                                        </div>

                                        <p class="font-semibold text-gray-900 dark:text-gray-100">
                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                        </p>
                                    </div>

                                    @if ($order->status_order === 'selesai')
                                        <div class="border-t border-gray-200 dark:border-gray-700 pt-2 mt-1">
                                            @if ($review)
                                                <p class="text-xs text-emerald-700 font-semibold">
                                                    Anda sudah memberi rating:
                                                    <span class="ml-1">
                                                        {{ str_repeat('★', $review->rating) }}
                                                        {{ str_repeat('☆', 5 - $review->rating) }}
                                                    </span>
                                                </p>
                                                @if($review->comment)
                                                    <p class="mt-1 text-[11px] text-gray-600 dark:text-gray-300">
                                                        "{{ $review->comment }}"
                                                    </p>
                                                @endif
                                            @else
                                                <form action="{{ route('products.review.store', $item->product->id) }}"
                                                      method="POST"
                                                      class="space-y-2">
                                                    @csrf

                                                    <div x-data="{ rating: 0 }" class="space-y-2">
                                                        <div class="flex items-center gap-2 text-xs">
                                                            <span class="text-gray-700 dark:text-gray-200">
                                                                Beri rating untuk produk ini:
                                                            </span>

                                                            <div class="flex items-center gap-1">
                                                                @for($i = 1; $i <= 5; $i++)
                                                                    <button
                                                                        type="button"
                                                                        @click="rating = {{ $i }}"
                                                                        class="text-lg focus:outline-none"
                                                                        :class="rating >= {{ $i }} ? 'text-yellow-400' : 'text-gray-400'"
                                                                    >
                                                                        ★
                                                                    </button>
                                                                @endfor
                                                            </div>
                                                        </div>

                                                        <input type="hidden" name="rating" x-model="rating">
                                                    </div>

                                                    <textarea
                                                        name="comment"
                                                        rows="2"
                                                        class="w-full border rounded-md text-xs px-2 py-1 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                                                        placeholder="Ceritakan pengalamanmu (opsional)"></textarea>

                                                    <button type="submit"
                                                            class="inline-flex items-center px-3 py-1.5 rounded-full bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700">
                                                        Kirim Penilaian
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ========== RINCIAN PEMBAYARAN ========== --}}
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-xl p-4 space-y-2">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-1">
                            Rincian Pembayaran
                        </h3>

                        <div class="flex justify-between text-gray-700 dark:text-gray-300 text-sm">
                            <span>Total Harga Barang</span>
                            <span>Rp {{ number_format($order->items->sum('subtotal'), 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between text-gray-700 dark:text-gray-300 text-sm">
                            <span>Ongkos Kirim</span>
                            <span>Rp {{ number_format($order->ongkir, 0, ',', '.') }}</span>
                        </div>

                        <hr class="border-gray-200 dark:border-gray-700">

                        <div class="flex justify-between font-bold text-lg text-emerald-600">
                            <span>Total Pembayaran</span>
                            <span>Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                        </div>

                        <p class="text-xs text-gray-500 mt-1">
                            Metode Pembayaran:
                            <b class="text-gray-700 dark:text-gray-200">{{ strtoupper($order->metode_pembayaran) }}</b>
                        </p>
                    </div>

                </div>

                {{-- FOOTER: TOMBOL AKSI --}}
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/60 flex flex-wrap justify-end gap-3 border-t border-gray-100 dark:border-gray-800">

                    @if (in_array($order->status_order, ['menunggu_konfirmasi','diproses']))
                        <form action="{{ route('orders.request-cancel', $order->id) }}" method="POST"
                              onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
                            @csrf
                            <button class="px-4 py-2 bg-red-600 text-white rounded-full text-xs sm:text-sm hover:bg-red-700">
                                Batalkan Pesanan
                            </button>
                        </form>
                    @endif

                    @if ($order->status_order === 'pending_cancel')
                        <p class="text-orange-600 font-semibold text-xs sm:text-sm">
                            Menunggu persetujuan pembatalan dari penjual...
                        </p>
                    @endif

                    @if ($status === 'dikirim')
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-full text-xs sm:text-sm hover:bg-indigo-700">
                            Lacak Pengiriman
                        </button>
                    @endif

                    @if ($status === 'dikirim' || $status === 'diproses')
                        <button class="px-4 py-2 bg-emerald-600 text-white rounded-full text-xs sm:text-sm hover:bg-emerald-700">
                            Hubungi Penjual
                        </button>
                    @endif

                    @if ($status === 'dikirim')
                        <button class="px-4 py-2 bg-green-600 text-white rounded-full text-xs sm:text-sm hover:bg-green-700">
                            Pesanan Diterima
                        </button>
                    @endif

                </div>

            </div>

        </div>
    </div>

</x-app-layout>
