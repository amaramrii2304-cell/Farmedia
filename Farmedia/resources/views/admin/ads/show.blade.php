{{-- resources/views/admin/ads/show.blade.php --}}
@php
    use Illuminate\Support\Facades\Storage;

    $status = $ad->status ?? '-';
    $pay    = $ad->payment_status ?? 'unpaid';

    $badgeStatus = match($status) {
        'pending'   => 'bg-amber-50 text-amber-700 border-amber-200',
        'approved'  => 'bg-sky-50 text-sky-700 border-sky-200',
        'active'    => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'rejected'  => 'bg-rose-50 text-rose-700 border-rose-200',
        'ended'     => 'bg-gray-50 text-gray-700 border-gray-200',
        'cancelled' => 'bg-gray-50 text-gray-700 border-gray-200',
        default     => 'bg-gray-50 text-gray-700 border-gray-200',
    };

    $badgePay = $pay === 'paid'
        ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
        : 'bg-gray-50 text-gray-700 border-gray-200';

    $proofUrl = $ad->payment_proof ? Storage::url($ad->payment_proof) : null;

    $canApprove = $status === 'pending';
    $canReject  = in_array($status, ['pending','approved'], true);
    $canMarkPaid = in_array($status, ['pending','approved'], true) && !empty($ad->payment_proof) && $pay !== 'paid';
    $canActivate = ($status === 'approved') && ($pay === 'paid');
    $canEnd = ($status === 'active');
@endphp

<x-app-layout>
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
                <h2 class="text-xl font-semibold text-emerald-900">Detail Iklan / Promosi (Admin)</h2>
                <p class="text-sm text-emerald-900/70">
                    Verifikasi pengajuan, pembayaran, dan aktivasi iklan.
                </p>
            </div>

            <a href="{{ route('admin.ads.index') }}"
               class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-50">
                ← Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            {{-- Kiri: Info utama --}}
            <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm lg:col-span-2">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-[11px] font-semibold tracking-[0.18em] text-emerald-700">PENGAJUAN</p>
                        <h3 class="mt-1 text-lg font-bold text-emerald-900">
                            {{ $ad->product->nama_produk ?? '—' }}
                        </h3>
                        <p class="mt-1 text-sm text-emerald-900/70">
                            Mitra: <b>{{ $ad->mitra->nama_toko ?? '-' }}</b>
                            <span class="mx-2">•</span>
                            Placement: <b>{{ strtoupper($ad->placement ?? '-') }}</b>
                            <span class="mx-2">•</span>
                            Durasi: <b>{{ (int)($ad->duration_days ?? 0) }} hari</b>
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $badgeStatus }}">
                            {{ strtoupper($status) }}
                        </span>
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $badgePay }}">
                            {{ strtoupper($pay) }}
                        </span>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/40 p-4">
                        <p class="text-sm font-semibold text-emerald-900">Informasi</p>
                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-emerald-900/70">ID Pengajuan</dt>
                                <dd class="font-semibold text-emerald-900">#{{ $ad->id }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-emerald-900/70">Dibuat</dt>
                                <dd class="font-semibold text-emerald-900">{{ optional($ad->created_at)->format('d M Y, H:i') ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-emerald-900/70">Kategori Snapshot</dt>
                                <dd class="font-semibold text-emerald-900">{{ $ad->kategori_snapshot ?? '-' }}</dd>
                            </div>
                            @if(($ad->placement ?? null) === 'category')
                                <div class="flex justify-between gap-4">
                                    <dt class="text-emerald-900/70">Target Kategori</dt>
                                    <dd class="font-semibold text-emerald-900">{{ $ad->target_kategori ?? '-' }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/40 p-4">
                        <p class="text-sm font-semibold text-emerald-900">Masa Aktif</p>
                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-emerald-900/70">Mulai</dt>
                                <dd class="font-semibold text-emerald-900">{{ $ad->start_at ? \Carbon\Carbon::parse($ad->start_at)->format('d M Y, H:i') : '-' }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-emerald-900/70">Selesai</dt>
                                <dd class="font-semibold text-emerald-900">{{ $ad->end_at ? \Carbon\Carbon::parse($ad->end_at)->format('d M Y, H:i') : '-' }}</dd>
                            </div>
                        </dl>

                        <p class="mt-3 text-xs text-emerald-900/60">
                            Catatan: Aktivasi akan mengisi tanggal otomatis (start=sekarang, end=+durasi).
                        </p>
                    </div>

                    <div class="rounded-2xl border border-emerald-100 bg-white p-4 sm:col-span-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[11px] font-semibold tracking-[0.18em] text-emerald-700">TAGIHAN</p>
                                <p class="mt-1 text-2xl font-bold text-emerald-900">
                                    Rp {{ number_format((int)($ad->price ?? 0), 0, ',', '.') }}
                                </p>
                                <p class="text-sm text-emerald-900/70">
                                    Metode: {{ strtoupper($ad->payment_method ?? 'transfer') }}
                                </p>
                            </div>
                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $badgePay }}">
                                {{ strtoupper($pay) }}
                            </span>
                        </div>

                        @if(!empty($ad->admin_note))
                            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800">
                                <b>Catatan Admin:</b> {{ $ad->admin_note }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Kanan: Bukti + Aksi Admin --}}
            <div class="space-y-4">
                {{-- Bukti pembayaran (read-only untuk admin) --}}
                <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-emerald-900">Bukti Pembayaran</p>
                    <p class="mt-1 text-xs text-emerald-900/60">
                        Admin hanya melihat bukti yang diupload Mitra.
                    </p>

                    <div class="mt-4 rounded-2xl border border-emerald-100 bg-emerald-50/40 p-3">
                        @if($proofUrl)
                            <a href="{{ $proofUrl }}" target="_blank" class="block">
                                <img src="{{ $proofUrl }}"
                                     alt="Bukti pembayaran"
                                     class="w-full rounded-xl border border-emerald-100 object-contain bg-white">
                                <p class="mt-2 text-xs text-emerald-900/60">
                                    Klik gambar untuk membuka ukuran penuh.
                                </p>
                            </a>
                        @else
                            <div class="rounded-xl border border-dashed border-emerald-200 bg-white p-6 text-center">
                                <p class="text-sm font-semibold text-emerald-900">Belum ada bukti</p>
                                <p class="mt-1 text-xs text-emerald-900/60">
                                    Mitra belum upload bukti transfer.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Aksi Admin --}}
                <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-emerald-900">Aksi Admin</p>
                    <p class="mt-1 text-xs text-emerald-900/60">
                        Ikuti urutan: Approve → (Mitra upload) → Mark Paid → Activate.
                    </p>

                    <div class="mt-4 space-y-2">
                        {{-- Approve --}}
                        @if($canApprove)
                            <form method="POST" action="{{ route('admin.ads.approve', $ad->id) }}">
                                @csrf
                                <button type="submit"
                                        class="w-full rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-sky-700">
                                    Approve Pengajuan
                                </button>
                            </form>
                        @endif

                        {{-- Reject --}}
                        @if($canReject)
                            <form method="POST" action="{{ route('admin.ads.reject', $ad->id) }}" class="space-y-2">
                                @csrf
                                <label class="block text-xs font-semibold text-emerald-800">Catatan Penolakan</label>
                                <textarea name="admin_note" rows="3" required
                                          class="w-full rounded-xl border border-emerald-200 px-3 py-2 text-sm focus:border-emerald-400 focus:ring-emerald-400"
                                          placeholder="Alasan penolakan..."></textarea>
                                <button type="submit"
                                        class="w-full rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">
                                    Tolak Pengajuan
                                </button>
                            </form>
                        @endif

                        {{-- Mark paid --}}
                        @if($canMarkPaid)
                            <form method="POST" action="{{ route('admin.ads.markPaid', $ad->id) }}">
                                @csrf
                                <button type="submit"
                                        class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                                    Mark Paid (Verifikasi Lunas)
                                </button>
                            </form>
                        @endif

                        {{-- Activate --}}
                        @if($canActivate)
                            <form method="POST" action="{{ route('admin.ads.activate', $ad->id) }}">
                                @csrf
                                <button type="submit"
                                        class="w-full rounded-xl bg-emerald-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-900">
                                    Aktifkan Iklan (Otomatis)
                                </button>
                            </form>
                        @endif

                        {{-- End --}}
                        @if($canEnd)
                            <form method="POST" action="{{ route('admin.ads.end', $ad->id) }}"
                                  onsubmit="return confirm('Akhiri iklan ini?');">
                                @csrf
                                <button type="submit"
                                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                    Akhiri Iklan
                                </button>
                            </form>
                        @endif

                        @if(!$canApprove && !$canReject && !$canMarkPaid && !$canActivate && !$canEnd)
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700">
                                Tidak ada aksi yang tersedia untuk status saat ini.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
