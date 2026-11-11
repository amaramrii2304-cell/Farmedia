{{-- resources/views/mitra/ads/show.blade.php --}}
<x-app-layout>
    @php
        $status  = $ad->status ?? 'pending';
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

    <div class="space-y-6">
        {{-- Flash --}}
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
                <h2 class="text-xl font-semibold text-emerald-900">Detail Iklan / Promosi</h2>
                <p class="text-sm text-emerald-900/70">Pantau status dan upload bukti pembayaran di sini.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('mitra.ads.index') }}"
                   class="rounded-xl border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-50">
                    ← Kembali
                </a>

                @if(in_array($status, ['pending','approved']))
                    <form method="POST" action="{{ route('mitra.ads.cancel', $ad->id) }}"
                          onsubmit="return confirm('Yakin batalkan pengajuan iklan ini?');">
                        @csrf
                        <button type="submit"
                                class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">
                            Batalkan
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Ringkas status --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm lg:col-span-2">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold tracking-[0.18em] text-emerald-700">PENGAJUAN</p>
                        <h3 class="mt-2 text-lg font-bold text-emerald-900">
                            {{ $ad->product->nama_produk ?? 'Produk tidak ditemukan' }}
                        </h3>
                        <p class="mt-1 text-sm text-emerald-900/70">
                            Placement: <span class="font-semibold text-emerald-900">{{ strtoupper($ad->placement ?? '-') }}</span>
                            @if(($ad->placement ?? '') === 'category')
                                • Target: <span class="font-semibold text-emerald-900">{{ $ad->target_kategori ?? '-' }}</span>
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $statusBadge }}">
                            {{ strtoupper($status) }}
                        </span>
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $payBadge }}">
                            {{ strtoupper($payment) }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-xl bg-emerald-50/60 p-4">
                        <p class="text-xs font-semibold text-emerald-700">Informasi</p>
                        <div class="mt-2 space-y-1 text-sm text-emerald-900/80">
                            <div>ID Pengajuan: <span class="font-semibold text-emerald-900">{{ $ad->id }}</span></div>
                            <div>Dibuat: <span class="font-semibold text-emerald-900">{{ optional($ad->created_at)->format('d M Y, H:i') }}</span></div>
                            <div>Kategori Snapshot: <span class="font-semibold text-emerald-900">{{ $ad->kategori_snapshot ?? '-' }}</span></div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-emerald-50/60 p-4">
                        <p class="text-xs font-semibold text-emerald-700">Masa Aktif</p>
                        <div class="mt-2 space-y-1 text-sm text-emerald-900/80">
                            <div>Mulai: <span class="font-semibold text-emerald-900">{{ $ad->start_at ? \Carbon\Carbon::parse($ad->start_at)->format('d M Y') : '-' }}</span></div>
                            <div>Selesai: <span class="font-semibold text-emerald-900">{{ $ad->end_at ? \Carbon\Carbon::parse($ad->end_at)->format('d M Y') : '-' }}</span></div>
                        </div>
                        <p class="mt-2 text-xs text-emerald-900/60">
                            *Tanggal aktif diisi admin setelah iklan disetujui & pembayaran diverifikasi.
                        </p>
                    </div>
                </div>

                @if($status === 'rejected' && $ad->admin_note)
                    <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-4">
                        <p class="text-sm font-semibold text-rose-800">Ditolak Admin</p>
                        <p class="mt-1 text-sm text-rose-700">{{ $ad->admin_note }}</p>
                    </div>
                @endif
            </div>

            {{-- Bukti pembayaran --}}
            <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-emerald-900">Bukti Pembayaran</p>
                <p class="mt-1 text-xs text-emerald-900/60">
                    Upload bukti transfer (JPG/PNG, max 2MB). Admin akan verifikasi.
                </p>

                <div class="mt-4">
                    @if($ad->payment_proof)
                        <div class="overflow-hidden rounded-xl border border-emerald-100">
                            <img src="{{ asset('storage/'.$ad->payment_proof) }}" alt="Bukti pembayaran" class="w-full object-cover">
                        </div>
                        <p class="mt-2 text-xs text-emerald-900/60">Bukti sudah diupload.</p>
                    @else
                        <div class="rounded-xl border border-dashed border-emerald-200 bg-emerald-50/40 p-6 text-center">
                            <p class="text-sm font-semibold text-emerald-900">Belum ada bukti</p>
                            <p class="mt-1 text-xs text-emerald-900/60">Silakan upload untuk diproses admin.</p>
                        </div>
                    @endif
                </div>

                @if(in_array($status, ['pending','approved']))
                    <form method="POST" action="{{ route('mitra.ads.uploadProof', $ad->id) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-emerald-800">File Bukti</label>
                            <input type="file" name="payment_proof" accept="image/png,image/jpeg"
                                   class="mt-2 block w-full rounded-xl border border-emerald-200 bg-white px-3 py-2 text-sm focus:border-emerald-400 focus:ring-emerald-400">
                            @error('payment_proof')
                                <p class="mt-1 text-xs text-rose-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                                class="w-full rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700">
                            Upload Bukti
                        </button>
                    </form>
                @else
                    <div class="mt-4 rounded-xl bg-slate-50 p-4 text-xs text-slate-700">
                        Upload hanya tersedia saat status <b>pending/approved</b>.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
