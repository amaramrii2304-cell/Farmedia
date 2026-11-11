{{-- resources/views/admin/cod/index.blade.php --}}
<x-app-layout>
  <div class="min-w-0 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
      <div class="min-w-0">
        <h2 class="text-2xl font-semibold text-emerald-900">Pengelolaan Transaksi COD</h2>
        <p class="text-sm text-emerald-900/70">
          Verifikasi uang cash dari kurir, hitung potongan, lalu tandai pembayaran ke mitra (manual transfer).
        </p>
      </div>

      <a href="{{ route('admin.dashboard') }}"
         class="shrink-0 inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-50">
        ← Kembali
      </a>
    </div>

    {{-- Filter --}}
<div class="rounded-2xl border border-emerald-100 bg-white shadow-sm px-4 py-3">
  <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-center gap-2">
      <span class="text-[11px] font-semibold text-emerald-900/70">Filter Status:</span>

      <select name="status"
              class="rounded-xl border border-emerald-200 bg-white px-3 py-2 text-[12px] focus:border-emerald-400 focus:ring-emerald-400">
        <option value="">Semua</option>
        <option value="unverified" @selected(request('status')==='unverified')>Belum Verif</option>
        <option value="verified"   @selected(request('status')==='verified')>Diverif</option>
        <option value="selisih"    @selected(request('status')==='selisih')>Selisih</option>
        <option value="paid"       @selected(request('status')==='paid')>Selesai</option>
      </select>

      <button class="rounded-xl bg-emerald-600 px-4 py-2 text-[12px] font-semibold text-white hover:bg-emerald-700">
        Terapkan
      </button>

      <a href="{{ route('admin.cod.index') }}"
         class="rounded-xl border border-emerald-200 bg-white px-4 py-2 text-[12px] font-semibold text-emerald-800 hover:bg-emerald-50">
        Reset
      </a>
    </div>

    <div class="text-[11px] text-emerald-900/60">
      Menampilkan:
      <b class="text-emerald-900">
        {{ request('status') ? strtoupper(request('status')) : 'SEMUA' }}
      </b>
    </div>
  </form>
</div>

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

    {{-- Card --}}
    <div class="rounded-2xl border border-emerald-100 bg-white shadow-sm overflow-hidden">

      <div class="flex flex-col gap-2 border-b border-emerald-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="text-sm font-semibold text-emerald-900">Daftar Transaksi COD</p>
          <p class="text-xs text-emerald-900/60">Hanya pesanan dengan metode pembayaran <b>COD</b>.</p>
        </div>
        <div class="text-xs text-emerald-900/60">
          {{ method_exists($orders,'total') ? $orders->total() : (is_countable($orders) ? count($orders) : '-') }} transaksi
        </div>
      </div>

      {{-- Table: dibuat ringkas agar muat tanpa scroll --}}
      <div class="min-w-0">
        <table class="w-full table-fixed text-[12px]">
          <thead class="bg-emerald-50/70 text-emerald-900">
            <tr class="text-left">
              {{-- PESANAN + PEMBELI + PENJUAL + REKENING (GABUNG) --}}
              <th class="w-[36%] px-4 py-3 text-[11px] font-semibold">Pesanan</th>

              <th class="w-[14%] px-3 py-3 text-[11px] font-semibold text-right">Total</th>
              <th class="w-[14%] px-3 py-3 text-[11px] font-semibold">Status</th>

              <th class="w-[12%] px-3 py-3 text-[11px] font-semibold text-right">Komisi</th>
              <th class="w-[12%] px-3 py-3 text-[11px] font-semibold text-right">Biaya</th>
              <th class="w-[12%] px-3 py-3 text-[11px] font-semibold text-right">Sisa</th>

              <th class="w-[14%] px-4 py-3 text-[11px] font-semibold text-right">Kelola</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-emerald-100/70">
            @forelse($orders as $o)
              @php
                $s = $o->codSettlement;

                $expected    = (float)($o->total_harga ?? 0);
                $platformFee = (float)($o->komisi_produk ?? 0) + (float)($o->komisi_ongkir ?? 0);
                $serviceFee  = (float)($o->ongkir ?? 0);

                $showPlatformFee = (float)($s->platform_fee ?? $platformFee);
                $showServiceFee  = (float)($s->service_fee  ?? $serviceFee);

                $netFallback = max(0, $expected - $platformFee - $serviceFee);
                $showNet     = (float)($s->net_to_seller ?? $netFallback);

                $status = $s?->status ?? 'unverified';
                $badgeCls = match($status) {
                  'verified' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                  'paid'     => 'bg-sky-50 text-sky-700 border-sky-200',
                  'selisih'  => 'bg-rose-50 text-rose-700 border-rose-200',
                  default    => 'bg-amber-50 text-amber-700 border-amber-200',
                };
                $label = match($status) {
                  'verified' => 'DIVERIF',
                  'paid'     => 'SELESAI',
                  'selisih'  => 'SELISIH',
                  default    => 'BELUM VERIF',
                };

                $hasBank = !empty($o->mitra?->bank_nama) && !empty($o->mitra?->bank_nomor) && !empty($o->mitra?->bank_pemilik);

                $canReceive = $status !== 'paid';
                $canDeduct  = $s && !is_null($s->received_amount) && ($status !== 'paid') && ($status !== 'selisih');
                $canPay     = $s && !is_null($s->received_amount) && !is_null($s->net_to_seller) && ($status !== 'paid') && ($status !== 'selisih');

                $selisihVal = ($s && !is_null($s->received_amount))
                  ? ((float)$s->received_amount - (float)($s->expected_amount ?? $expected))
                  : null;
              @endphp

              <tr class="hover:bg-emerald-50/30 align-top">
                {{-- PESANAN (gabungan) --}}
                <td class="px-4 py-3">
                  <div class="flex flex-col gap-1 min-w-0">
                    <div class="flex items-center gap-2 min-w-0">
                      <span class="shrink-0 rounded-lg bg-emerald-900 px-2.5 py-1 text-[11px] font-semibold text-white">
                        ORD-{{ $o->id }}
                      </span>
                      <span class="text-[11px] text-emerald-900/60 truncate">
                        {{ $o->created_at?->format('d M Y, H:i') }}
                      </span>
                    </div>

                    <div class="text-[11px] text-emerald-900/70">
                      Pembeli: <span class="font-semibold text-emerald-900">{{ $o->user->name ?? '-' }}</span>
                    </div>

                    {{-- penjual disatukan di bawah pembeli --}}
                    <div class="text-[11px] text-emerald-900/70">
                      Penjual: <span class="font-semibold text-emerald-900">{{ $o->mitra->nama_toko ?? '-' }}</span>
                      <span class="text-emerald-900/50">•</span>
                      <span class="text-emerald-900/60 truncate inline-block max-w-[240px] align-bottom">
                        {{ $o->mitra->alamat ?? '-' }}
                      </span>
                    </div>

                    {{-- rekening ringkas --}}
                    <div class="text-[11px] text-emerald-900/70">
                      @if($hasBank)
                        <span class="font-semibold">{{ $o->mitra->bank_nama }}</span>
                        <span class="text-emerald-900/50">•</span>
                        <span class="font-mono">{{ $o->mitra->bank_nomor }}</span>
                        <span class="text-emerald-900/50">•</span>
                        <span>A/N {{ $o->mitra->bank_pemilik }}</span>
                      @else
                        <span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700">
                          Rekening belum lengkap
                        </span>
                      @endif
                    </div>
                  </div>
                </td>

                {{-- Total --}}
                <td class="px-3 py-3 text-right tabular-nums">
                  <div class="font-semibold text-emerald-900 text-[12px]">
                    Rp {{ number_format($expected,0,',','.') }}
                  </div>
                  <div class="text-[10.5px] text-emerald-900/60">Cash kurir</div>
                </td>

                {{-- Status --}}
                <td class="px-3 py-3">
                  <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10.5px] font-semibold {{ $badgeCls }}">
                    {{ $label }}
                  </span>

                  @if($status === 'selisih' && $selisihVal !== null)
                    <div class="mt-2 text-[10.5px] text-rose-700">
                      Selisih: <b class="tabular-nums">Rp {{ number_format($selisihVal,0,',','.') }}</b>
                    </div>
                  @endif
                </td>

                {{-- Komisi --}}
                <td class="px-3 py-3 text-right tabular-nums text-[11px]">
                  Rp {{ number_format($showPlatformFee,0,',','.') }}
                </td>

                {{-- Biaya --}}
                <td class="px-3 py-3 text-right tabular-nums text-[11px]">
                  Rp {{ number_format($showServiceFee,0,',','.') }}
                </td>

                {{-- Sisa --}}
                <td class="px-3 py-3 text-right tabular-nums">
                  <div class="font-extrabold text-emerald-700 text-[12px]">
                    Rp {{ number_format($showNet,0,',','.') }}
                  </div>
                  <div class="text-[10.5px] text-emerald-900/60">ke mitra</div>
                </td>

                {{-- Kelola (dropdown tetap) --}}
                <td class="px-5 py-4 align-top" x-data="{ open:false }">
  <button
    type="button"
    @click="open=true"
    class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
    Detail
  </button>

  {{-- DRAWER DETAIL TRANSAKSI --}}
  <div x-cloak x-show="open" class="fixed inset-0 z-[60]">
    {{-- overlay --}}
    <div class="absolute inset-0 bg-black/40" @click="open=false"></div>

    {{-- panel kanan --}}
    <div
      class="absolute right-0 top-0 h-full w-full max-w-[420px] bg-white shadow-2xl border-l border-emerald-100 overflow-y-auto"
      x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="translate-x-full"
      x-transition:enter-end="translate-x-0"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="translate-x-0"
      x-transition:leave-end="translate-x-full"
    >
      {{-- header drawer --}}
      <div class="sticky top-0 bg-white border-b border-emerald-100 px-5 py-4 z-10">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="text-sm font-semibold text-emerald-900">Detail Transaksi COD</p>
            <p class="text-xs text-emerald-900/60">
              ORD-{{ $o->id }} • {{ $o->created_at?->format('d M Y, H:i') }}
            </p>
          </div>
          <button @click="open=false" class="rounded-lg px-2 py-1 text-emerald-900/60 hover:bg-emerald-50">
            ✕
          </button>
        </div>

        <div class="mt-3 flex items-center gap-2">
          <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $badgeCls }}">
            {{ $label }}
          </span>

          <div class="ml-auto text-right">
            <p class="text-[11px] text-emerald-900/60">Total COD</p>
            <p class="text-sm font-extrabold text-emerald-900 tabular-nums">
              Rp {{ number_format($expected,0,',','.') }}
            </p>
          </div>
        </div>
      </div>

      {{-- konten drawer --}}
      <div class="p-5 space-y-4">
        {{-- Ringkasan pihak --}}
        <div class="rounded-2xl border border-emerald-100 p-4">
          <p class="text-xs font-semibold text-emerald-900">Pesanan</p>
          <div class="mt-2 text-xs text-emerald-900/70 space-y-1">
            <div>
              Pembeli: <span class="font-semibold text-emerald-900">{{ $o->user->name ?? '-' }}</span>
            </div>
            <div>
              Penjual: <span class="font-semibold text-emerald-900">{{ $o->mitra->nama_toko ?? '-' }}</span>
            </div>
            <div class="line-clamp-2">
              {{ $o->mitra->alamat ?? '-' }}
            </div>
          </div>
        </div>

        {{-- Rekening --}}
        <div class="rounded-2xl border border-emerald-100 p-4">
          <div class="flex items-center justify-between">
            <p class="text-xs font-semibold text-emerald-900">Rekening Mitra</p>
            @if(!$hasBank)
              <span class="text-[11px] font-semibold text-rose-700">Belum lengkap</span>
            @endif
          </div>

          @if($hasBank)
            <div class="mt-2 text-xs text-emerald-900/80">
              <div class="font-semibold">{{ $o->mitra->bank_nama }}</div>
              <div class="mt-1 font-mono">{{ $o->mitra->bank_nomor }}</div>
              <div class="mt-1">A/N {{ $o->mitra->bank_pemilik }}</div>
            </div>
          @else
            <p class="mt-2 text-xs text-emerald-900/60">
              Lengkapi bank_nama, bank_nomor, bank_pemilik di data mitra agar bisa proses “Bayar”.
            </p>
          @endif
        </div>

        {{-- Perhitungan --}}
        <div class="rounded-2xl border border-emerald-100 p-4">
          <p class="text-xs font-semibold text-emerald-900">Perhitungan</p>
          <div class="mt-2 text-xs text-emerald-900/70 space-y-1 tabular-nums">
            <div class="flex justify-between">
              <span>Komisi Platform</span>
              <b>Rp {{ number_format($platformFee,0,',','.') }}</b>
            </div>
            <div class="flex justify-between">
              <span>Biaya Layanan (Ongkir)</span>
              <b>Rp {{ number_format($serviceFee,0,',','.') }}</b>
            </div>
            <div class="border-t border-dashed border-emerald-100 pt-2 flex justify-between">
              <span class="font-semibold">Sisa ke Mitra</span>
              <b class="text-emerald-700">Rp {{ number_format($showNet,0,',','.') }}</b>
            </div>
          </div>
        </div>

        {{-- Aksi Admin --}}
        <div class="space-y-3">
          {{-- 1) Terima COD --}}
          <div class="rounded-2xl border border-emerald-100 p-4">
            <p class="text-xs font-semibold text-emerald-900">1) Terima COD (Cash Kurir)</p>

            <form method="POST" action="{{ route('admin.cod.receive', $o->id) }}" class="mt-3 space-y-2">
              @csrf
              <input name="received_amount"
                     value="{{ old('received_amount', $s->received_amount ?? $expected) }}"
                     class="w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs"
                     placeholder="Jumlah diterima"
                     {{ $canReceive ? '' : 'disabled' }}>
              <input name="received_note"
                     value="{{ old('received_note', $s->received_note ?? '') }}"
                     class="w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs"
                     placeholder="Catatan (opsional)"
                     {{ $canReceive ? '' : 'disabled' }}>

              <button
                class="w-full rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed"
                {{ $canReceive ? '' : 'disabled' }}>
                Simpan Penerimaan
              </button>
            </form>
          </div>

          {{-- 2) Potong --}}
          <div class="rounded-2xl border border-emerald-100 p-4">
            <p class="text-xs font-semibold text-emerald-900">2) Proses Potongan</p>

            <form method="POST" action="{{ route('admin.cod.deduct', $o->id) }}" class="mt-3">
              @csrf
              <button
                class="w-full rounded-xl border border-emerald-200 bg-white px-4 py-2 text-xs font-semibold text-emerald-800 hover:bg-emerald-50 disabled:opacity-50 disabled:cursor-not-allowed"
                {{ $canDeduct ? '' : 'disabled' }}>
                Proses Potongan
              </button>
            </form>

            <p class="mt-2 text-[11px] text-emerald-900/60">
              Potongan mengisi <b>platform_fee</b>, <b>service_fee</b>, <b>net_to_seller</b>.
            </p>
          </div>

          {{-- 3) Bayar --}}
          <div class="rounded-2xl border border-emerald-100 p-4">
            <p class="text-xs font-semibold text-emerald-900">3) Bayar ke Mitra (Manual Transfer)</p>

            <form method="POST" action="{{ route('admin.cod.pay', $o->id) }}" class="mt-3 space-y-2">
              @csrf
              <input name="payout_ref"
                     class="w-full rounded-xl border border-emerald-200 px-3 py-2 text-xs"
                     placeholder="No Ref Transfer (wajib)"
                     {{ ($canPay && $hasBank) ? '' : 'disabled' }}>
              <button
                class="w-full rounded-xl bg-sky-600 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-700 disabled:opacity-50 disabled:cursor-not-allowed"
                {{ ($canPay && $hasBank) ? '' : 'disabled' }}>
                Tandai Sudah Dibayar
              </button>
            </form>

            @if(!$hasBank)
              <p class="mt-2 text-[11px] text-rose-700">
                Tidak bisa bayar: rekening mitra belum lengkap.
              </p>
            @endif
          </div>
        </div>

        {{-- footer tombol --}}
        <div class="pt-2">
          <button @click="open=false"
                  class="w-full rounded-xl border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-50">
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="px-5 py-12 text-center text-sm text-emerald-900/60">
                  Belum ada transaksi COD.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="border-t border-emerald-100 px-5 py-4">
        {{ $orders->links() }}
      </div>
    </div>
  </div>
</x-app-layout>
