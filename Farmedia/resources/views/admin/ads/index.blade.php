{{-- resources/views/admin/ads/index.blade.php --}}
<x-app-layout>
    @php
        /** @var \Illuminate\Pagination\LengthAwarePaginator $ads */
        $collection = $ads->getCollection();

        $menuBase = 'flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm';

        // Statistik global admin (biar ringkasannya bener-bener total)
        $total    = \App\Models\AdSubmission::count();
        $pending  = \App\Models\AdSubmission::where('status','pending')->count();
        $approved = \App\Models\AdSubmission::where('status','approved')->count();
        $active   = \App\Models\AdSubmission::where('status','active')->count();
        $paid     = \App\Models\AdSubmission::where('payment_status','paid')->count();
    @endphp

    <div class="space-y-6">
        {{-- Flash message --}}
        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                ✅ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">
                ❌ {{ session('error') }}
            </div>
        @endif

        {{-- Header --}}
        <div>
            <h2 class="text-xl font-semibold text-emerald-900">Iklan / Promosi</h2>
            <p class="text-sm text-emerald-900/70">
                Panel admin untuk verifikasi pengajuan iklan mitra, cek bukti pembayaran, dan aktifkan iklan.
            </p>
        </div>

        {{-- Summary --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-semibold tracking-[0.18em] text-emerald-700">TOTAL</p>
                <p class="mt-2 text-2xl font-bold text-emerald-900">{{ $total }}</p>
                <p class="mt-1 text-xs text-emerald-900/60">Semua pengajuan</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-semibold tracking-[0.18em] text-emerald-700">PENDING</p>
                <p class="mt-2 text-2xl font-bold text-amber-600">{{ $pending }}</p>
                <p class="mt-1 text-xs text-emerald-900/60">Butuh keputusan</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-semibold tracking-[0.18em] text-emerald-700">APPROVED</p>
                <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $approved }}</p>
                <p class="mt-1 text-xs text-emerald-900/60">Menunggu paid/aktivasi</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-semibold tracking-[0.18em] text-emerald-700">AKTIF</p>
                <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $active }}</p>
                <p class="mt-1 text-xs text-emerald-900/60">Sedang berjalan</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-semibold tracking-[0.18em] text-emerald-700">PAID</p>
                <p class="mt-2 text-2xl font-bold text-emerald-800">{{ $paid }}</p>
                <p class="mt-1 text-xs text-emerald-900/60">Sudah lunas</p>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-emerald-100 px-5 py-4">
                <p class="text-sm font-semibold text-emerald-900">Daftar Pengajuan Iklan</p>
                <p class="text-xs text-emerald-900/60">Klik Detail untuk approve/reject/mark paid/activate/end.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-emerald-50/60 text-xs font-semibold uppercase tracking-[0.12em] text-emerald-800">
                    <tr>
                        <th class="px-5 py-3">Produk</th>
                        <th class="px-5 py-3">Mitra</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Payment</th>
                        <th class="px-5 py-3">Placement</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-emerald-100">
                    @forelse($collection as $ad)
                        @php
                            $status = $ad->status ?? 'pending';
                            $payment = $ad->payment_status ?? 'unpaid';

                            $statusBadge = match($status) {
                                'pending'   => 'bg-amber-50 text-amber-700 border-amber-200',
                                'approved'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'active'    => 'bg-emerald-600 text-white border-emerald-600',
                                'rejected'  => 'bg-rose-50 text-rose-700 border-rose-200',
                                'ended'     => 'bg-slate-100 text-slate-700 border-slate-200',
                                'cancelled' => 'bg-slate-100 text-slate-700 border-slate-200',
                                default     => 'bg-slate-100 text-slate-700 border-slate-200',
                            };

                            $payBadge = $payment === 'paid'
                                ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                : 'bg-slate-100 text-slate-700 border-slate-200';
                        @endphp

                        <tr class="hover:bg-emerald-50/30">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-emerald-900">
                                    {{ $ad->product->nama_produk ?? '-' }}
                                </div>
                                <div class="text-xs text-emerald-900/60">ID: {{ $ad->id }}</div>
                            </td>

                            <td class="px-5 py-4 text-emerald-900/80">
                                <div class="font-semibold">
                                    {{ $ad->mitra->nama_toko ?? $ad->mitra->name ?? '-' }}
                                </div>
                                <div class="text-xs text-emerald-900/60">
                                    Mitra ID: {{ $ad->mitra_id }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusBadge }}">
                                    {{ strtoupper($status) }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $payBadge }}">
                                    {{ strtoupper($payment) }}
                                </span>
                                <div class="mt-1 text-xs text-emerald-900/60">
                                    Bukti: {{ $ad->payment_proof ? 'Ada' : 'Belum' }}
                                </div>
                            </td>

                            <td class="px-5 py-4 text-emerald-900/80">
                                <div class="font-semibold">{{ strtoupper($ad->placement ?? '-') }}</div>
                                @if(($ad->placement ?? '') === 'category')
                                    <div class="text-xs text-emerald-900/60">Target: {{ $ad->target_kategori ?? '-' }}</div>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-emerald-900/80">
                                <div class="text-xs">
                                    {{ optional($ad->created_at)->format('d M Y, H:i') }}
                                </div>
                                @if($ad->start_at && $ad->end_at)
                                    <div class="mt-1 text-xs text-emerald-900/60">
                                        Aktif: {{ \Carbon\Carbon::parse($ad->start_at)->format('d M Y') }}
                                        – {{ \Carbon\Carbon::parse($ad->end_at)->format('d M Y') }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('admin.ads.show', $ad->id) }}"
                                   class="inline-flex items-center rounded-xl border border-emerald-200 bg-white px-3 py-2 text-xs font-semibold text-emerald-800 hover:bg-emerald-50">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center">
                                <p class="text-sm font-semibold text-emerald-900">Belum ada pengajuan iklan</p>
                                <p class="mt-1 text-xs text-emerald-900/60">Pengajuan dari mitra akan tampil di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-emerald-100 px-5 py-4">
                {{ $ads->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
