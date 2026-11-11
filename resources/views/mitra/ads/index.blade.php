{{-- resources/views/mitra/ads/index.blade.php --}}
<x-app-layout>
    @php
        /** @var \Illuminate\Pagination\LengthAwarePaginator $ads */
        $collection = $ads->getCollection();

        $total    = $ads->total();
        $pending  = (clone \App\Models\AdSubmission::query())->where('mitra_id', auth()->user()->mitra_id)->where('status','pending')->count();
        $approved = (clone \App\Models\AdSubmission::query())->where('mitra_id', auth()->user()->mitra_id)->where('status','approved')->count();
        $active   = (clone \App\Models\AdSubmission::query())->where('mitra_id', auth()->user()->mitra_id)->where('status','active')->count();
        $rejected = (clone \App\Models\AdSubmission::query())->where('mitra_id', auth()->user()->mitra_id)->where('status','rejected')->count();

        $menuBase = 'flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition shadow-sm';
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
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-emerald-900">Iklan / Promosi</h2>
                <p class="text-sm text-emerald-900/70">
                    Ajukan iklan produk, upload bukti transfer, dan pantau status verifikasi admin.
                </p>
            </div>

            <a href="{{ route('mitra.ads.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700">
                ➕ Buat Iklan
            </a>
        </div>

        {{-- Summary --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-semibold tracking-[0.18em] text-emerald-700">TOTAL</p>
                <p class="mt-2 text-2xl font-bold text-emerald-900">{{ $total }}</p>
                <p class="mt-1 text-xs text-emerald-900/60">Semua pengajuan</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-semibold tracking-[0.18em] text-emerald-700">PENDING</p>
                <p class="mt-2 text-2xl font-bold text-amber-600">{{ $pending }}</p>
                <p class="mt-1 text-xs text-emerald-900/60">Menunggu proses</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-semibold tracking-[0.18em] text-emerald-700">APPROVED</p>
                <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $approved }}</p>
                <p class="mt-1 text-xs text-emerald-900/60">Disetujui admin</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-semibold tracking-[0.18em] text-emerald-700">AKTIF</p>
                <p class="mt-2 text-2xl font-bold text-emerald-600">{{ $active }}</p>
                <p class="mt-1 text-xs text-emerald-900/60">Sedang berjalan</p>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-emerald-100 px-5 py-4">
                <p class="text-sm font-semibold text-emerald-900">Daftar Pengajuan</p>
                <p class="text-xs text-emerald-900/60">Klik Detail untuk upload bukti / lihat status.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-emerald-50/60 text-xs font-semibold uppercase tracking-[0.12em] text-emerald-800">
                    <tr>
                        <th class="px-5 py-3">Produk</th>
                        <th class="px-5 py-3">Placement</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Pembayaran</th>
                        <th class="px-5 py-3">Waktu</th>
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
                                    {{ $ad->product->nama_produk ?? 'Produk tidak ditemukan' }}
                                </div>
                                <div class="text-xs text-emerald-900/60">
                                    ID Pengajuan: {{ $ad->id }}
                                </div>
                            </td>

                            <td class="px-5 py-4 text-emerald-900/80">
                                <div class="font-semibold">
                                    {{ strtoupper($ad->placement ?? '-') }}
                                </div>
                                @if(($ad->placement ?? '') === 'category')
                                    <div class="text-xs text-emerald-900/60">
                                        Target: {{ $ad->target_kategori ?? '-' }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusBadge }}">
                                    {{ strtoupper($status) }}
                                </span>
                                @if($status === 'rejected' && $ad->admin_note)
                                    <div class="mt-1 text-xs text-rose-700">
                                        Catatan: {{ $ad->admin_note }}
                                    </div>
                                @endif
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
                                <div class="text-xs">
                                    Dibuat: {{ optional($ad->created_at)->format('d M Y, H:i') }}
                                </div>
                                @if($ad->start_at && $ad->end_at)
                                    <div class="mt-1 text-xs text-emerald-900/60">
                                        Aktif: {{ \Carbon\Carbon::parse($ad->start_at)->format('d M Y') }}
                                        – {{ \Carbon\Carbon::parse($ad->end_at)->format('d M Y') }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('mitra.ads.show', $ad->id) }}"
                                       class="rounded-xl border border-emerald-200 bg-white px-3 py-2 text-xs font-semibold text-emerald-800 hover:bg-emerald-50">
                                        Detail
                                    </a>

                                    @if(in_array($status, ['pending','approved']))
                                        <form method="POST" action="{{ route('mitra.ads.cancel', $ad->id) }}"
                                              onsubmit="return confirm('Yakin batalkan pengajuan ini?');">
                                            @csrf
                                            <button type="submit"
                                                    class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                                Batalkan
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center">
                                <p class="text-sm font-semibold text-emerald-900">Belum ada pengajuan iklan</p>
                                <p class="mt-1 text-xs text-emerald-900/60">Klik “Buat Iklan” untuk mulai promosi produk.</p>
                                <a href="{{ route('mitra.ads.create') }}"
                                   class="mt-4 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700">
                                    ➕ Buat Iklan
                                </a>
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
