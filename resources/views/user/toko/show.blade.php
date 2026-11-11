<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-lg text-emerald-900 leading-tight">Profil Toko</h2>
      <a href="{{ url()->previous() }}"
         class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-50">
        ← Kembali
      </a>
    </div>
  </x-slot>

  @php
    // query state
    $activeCat = $activeCat ?? request('cat');
    $q         = $q ?? request('q');

    // pill style
    $pillBase  = 'inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold border transition whitespace-nowrap';
    $pillOn    = 'bg-emerald-600 text-white border-emerald-600 shadow-sm';
    $pillOff   = 'bg-white text-slate-700 border-slate-200 hover:border-emerald-200 hover:text-emerald-700';

    // total produk yang tampil (hasil filter)
    $visibleTotal = method_exists($products,'total') ? $products->total() : $products->count();

    // helper membuat URL tab supaya query tetap nyambung
    $buildUrl = function ($catValue = null) use ($mitra, $q) {
      $params = [];
      if (!empty($catValue)) $params['cat'] = $catValue;
      if (!empty($q))        $params['q']   = $q;

      $base = route('toko.show', $mitra->id);
      return $params ? ($base . '?' . http_build_query($params) . '#produk') : ($base . '#produk');
    };
  @endphp

  <div class="py-6 bg-emerald-50/60">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-5">

      {{-- Header toko (Farmedia green) --}}
      <div class="rounded-2xl overflow-hidden shadow-sm border border-emerald-100 bg-white">
        {{-- top banner --}}
        <div class="bg-gradient-to-r from-emerald-700 to-emerald-500">
          <div class="p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

              {{-- kiri: logo + nama --}}
              <div class="flex items-center gap-4 min-w-0">
                <div class="w-16 h-16 rounded-2xl overflow-hidden border border-white/30 bg-white/10 flex items-center justify-center shrink-0">
                  @if(!empty($mitra->logo_path))
                    <img src="{{ asset('storage/' . $mitra->logo_path) }}" class="w-full h-full object-cover" alt="Logo">
                  @else
                    <span class="text-xl font-extrabold text-white">
                      {{ strtoupper(mb_substr($mitra->nama_toko ?? 'T', 0, 1)) }}
                    </span>
                  @endif
                </div>

                <div class="text-white min-w-0">
                  <p class="text-xl font-semibold leading-tight truncate">{{ $mitra->nama_toko }}</p>
                  <p class="text-xs text-emerald-50/80">
                    Bergabung:
                    <span class="font-medium">{{ $mitra->created_at?->translatedFormat('d M Y') ?? '-' }}</span>
                  </p>

                  @if(!empty($mitra->alamat))
                    <p class="mt-1 text-xs text-emerald-50/80 line-clamp-1 max-w-[640px]">
                      {{ $mitra->alamat }}
                    </p>
                  @endif
                </div>
              </div>

              {{-- kanan: statistik ringkas --}}
              <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <div class="rounded-2xl bg-white/10 border border-white/20 px-4 py-3">
                  <p class="text-[11px] uppercase tracking-[0.16em] text-emerald-50/70">Produk</p>
                  <p class="mt-1 text-lg font-bold text-white">{{ $produkCount }}</p>
                </div>

                <div class="rounded-2xl bg-white/10 border border-white/20 px-4 py-3">
                  <p class="text-[11px] uppercase tracking-[0.16em] text-emerald-50/70">Penilaian</p>
                  <p class="mt-1 text-lg font-bold text-white">{{ number_format($shopAvgRating, 1) }}</p>
                  <p class="text-[11px] text-emerald-50/70">
                    {{ number_format($shopReviewsCount,0,',','.') }} ulasan
                  </p>
                </div>
              </div>

            </div>
          </div>
        </div>

        {{-- tabs kategori + badge --}}
        <div class="border-t border-emerald-100 bg-white px-4 sm:px-6">
          <div class="flex items-center gap-2 py-3 overflow-x-auto">

            {{-- Semua --}}
            <a href="{{ $buildUrl(null) }}"
               class="{{ $pillBase }} {{ empty($activeCat) ? $pillOn : $pillOff }}">
              Semua
              <span class="text-[10px] px-2 py-0.5 rounded-full {{ empty($activeCat) ? 'bg-white/20' : 'bg-slate-100' }}">
                {{ $produkCount }}
              </span>
            </a>

            @foreach($categories as $cat)
              @php $cnt = (int)($categoryCounts[$cat] ?? 0); @endphp
              <a href="{{ $buildUrl($cat) }}"
                 class="{{ $pillBase }} {{ $activeCat === $cat ? $pillOn : $pillOff }}">
                {{ $cat }}
                <span class="text-[10px] px-2 py-0.5 rounded-full {{ $activeCat === $cat ? 'bg-white/20' : 'bg-slate-100' }}">
                  {{ $cnt }}
                </span>
              </a>
            @endforeach

          </div>

          {{-- bar kecil filter aktif --}}
          @if(!empty($activeCat) || !empty($q))
            <div class="pb-4">
              <div class="flex flex-wrap items-center gap-2 text-xs">
                @if(!empty($activeCat))
                  <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-100 px-3 py-1">
                    Kategori: <b>{{ $activeCat }}</b>
                    <a href="{{ $buildUrl(null) }}" class="ml-1 text-emerald-700 hover:underline">×</a>
                  </span>
                @endif

                @if(!empty($q))
                  <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 text-slate-700 border border-slate-200 px-3 py-1">
                    Cari: <b>{{ $q }}</b>
                    <a href="{{ $buildUrl($activeCat) }}" class="ml-1 text-slate-600 hover:underline">×</a>
                  </span>
                @endif
              </div>
            </div>
          @endif
        </div>
      </div>

      {{-- Produk --}}
      <div id="produk" class="bg-white border border-emerald-100 rounded-2xl shadow-sm p-5 sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
          <div>
            <h3 class="text-sm font-semibold text-emerald-900">Produk Toko</h3>
            <p class="text-xs text-emerald-900/60">
              Menampilkan <b>{{ $visibleTotal }}</b> produk
              @if(!empty($activeCat)) • Kategori: <b>{{ $activeCat }}</b>@endif
              @if(!empty($q)) • Pencarian: <b>{{ $q }}</b>@endif
            </p>
          </div>

          {{-- Search box --}}
          <form method="GET" action="{{ route('toko.show', $mitra->id) }}" class="w-full sm:w-[420px]">
            @if(!empty($activeCat))
              <input type="hidden" name="cat" value="{{ $activeCat }}">
            @endif

            <div class="flex gap-2">
              <input type="text" name="q" value="{{ $q }}"
                     placeholder="Cari produk di toko ini…"
                     class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm
                            focus:border-emerald-400 focus:ring-emerald-400">

              <button class="shrink-0 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                Cari
              </button>

              <a href="{{ route('toko.show', $mitra->id) }}{{ !empty($activeCat) ? ('?cat=' . urlencode($activeCat)) : '' }}#produk"
                 class="shrink-0 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Reset
              </a>
            </div>
          </form>
        </div>

        @if($products->isEmpty())
          <div class="rounded-2xl border border-emerald-100 bg-emerald-50/40 px-5 py-10 text-center">
            <p class="text-sm font-semibold text-emerald-900">
              Produk tidak ditemukan
            </p>
            <p class="mt-1 text-xs text-emerald-900/60">
              @if(!empty($q) && !empty($activeCat))
                Tidak ada produk untuk pencarian <b>"{{ $q }}"</b> pada kategori <b>{{ $activeCat }}</b>.
              @elseif(!empty($q))
                Tidak ada produk untuk pencarian <b>"{{ $q }}"</b>.
              @elseif(!empty($activeCat))
                Belum ada produk pada kategori <b>{{ $activeCat }}</b>.
              @else
                Belum ada produk di toko ini.
              @endif
            </p>

            <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
              <a href="{{ route('toko.show', $mitra->id) }}#produk"
                 class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                Lihat Semua Produk
              </a>

              @if(!empty($activeCat) || !empty($q))
                <a href="{{ route('toko.show', $mitra->id) }}{{ !empty($activeCat) ? ('?cat=' . urlencode($activeCat)) : '' }}#produk"
                   class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                  Reset Filter
                </a>
              @endif
            </div>
          </div>
        @else
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($products as $p)
              <a href="{{ route('user.produk.show', $p->id) }}"
                 class="group rounded-2xl border border-emerald-100 bg-white overflow-hidden hover:shadow-md hover:-translate-y-[1px] transition">
                <div class="aspect-square bg-emerald-50/40">
                  <img src="{{ asset('storage/' . $p->foto_produk) }}"
                       class="w-full h-full object-cover"
                       alt="{{ $p->nama_produk }}">
                </div>

                <div class="p-3">
                  <p class="text-sm font-semibold text-emerald-900 line-clamp-2">
                    {{ $p->nama_produk }}
                  </p>

                  <p class="mt-1 text-sm font-extrabold text-emerald-700">
                    Rp {{ number_format($p->harga,0,',','.') }}
                  </p>

                  <div class="mt-1 text-[11px] text-emerald-900/60 flex items-center justify-between">
                    <span>★ {{ number_format((float)($p->reviews_avg_rating ?? 0), 1) }}</span>
                    <span>{{ (int)($p->reviews_count ?? 0) }} ulasan</span>
                  </div>

                  @if(!empty($p->kategori_produk))
                    <div class="mt-2">
                      <span class="inline-flex items-center rounded-full border border-emerald-100 bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                        {{ $p->kategori_produk }}
                      </span>
                    </div>
                  @endif
                </div>
              </a>
            @endforeach
          </div>

          <div class="mt-5">
            {{ $products->withQueryString()->links() }}
          </div>
        @endif
      </div>

    </div>
  </div>
</x-app-layout>
