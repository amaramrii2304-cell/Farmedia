<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="font-semibold text-xl text-emerald-900 leading-tight">Ulasan Produk</h2>
        <p class="text-sm text-emerald-900/60">Semua rating & ulasan pembeli untuk produk toko Anda.</p>
      </div>

      <a href="{{ route('mitra.products.index') }}"
         class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-50">
        ← Kembali ke Produk
      </a>
    </div>
  </x-slot>

  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

      {{-- FILTER --}}
      <div class="bg-white rounded-2xl shadow-sm border border-emerald-50 p-5">
        <form method="GET" action="{{ route('mitra.reviews.index') }}" class="flex flex-col md:flex-row md:items-end gap-3">
          <div class="flex-1">
            <label class="block text-xs text-gray-500 mb-1">Cari (produk / pembeli / isi ulasan)</label>
            <input type="text" name="q" value="{{ $q }}"
                   class="w-full rounded-xl border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500"
                   placeholder="Contoh: pupuk, bagus...">
          </div>

          <div class="w-full md:w-56">
    <label for="filter-rating" class="block text-xs text-gray-500 mb-1">Filter rating</label>
    
    <select name="rating" id="filter-rating"
            class="w-full rounded-xl border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
      <option value="">Semua rating</option>
      @for($i=5;$i>=1;$i--)
        <option value="{{ $i }}" @selected((string)$rating === (string)$i)>{{ $i }} ★</option>
      @endfor
    </select>
</div>

          <div class="flex gap-2">
            <button type="submit"
                    class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700">
              Terapkan
            </button>
            <a href="{{ route('mitra.reviews.index') }}"
               class="px-3 py-2 rounded-xl border text-xs text-gray-600 hover:bg-gray-50">
              Reset
            </a>
          </div>
        </form>
      </div>

      {{-- KARTU RINGKASAN --}}
      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-emerald-50 p-4">
          <p class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wide">Rata-rata Rating</p>
          <p class="mt-2 text-2xl font-extrabold text-emerald-800">{{ number_format($avgRating, 1) }}</p>
          <p class="mt-1 text-[11px] text-gray-500">Dari semua ulasan.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-emerald-50 p-4">
          <p class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wide">Total Ulasan</p>
          <p class="mt-2 text-2xl font-extrabold text-emerald-800">{{ number_format($totalReviews, 0, ',', '.') }}</p>
          <p class="mt-1 text-[11px] text-gray-500">Jumlah review masuk.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-emerald-50 p-4">
          <p class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wide">Ulasan 5★</p>
          <p class="mt-2 text-2xl font-extrabold text-emerald-800">{{ number_format($count5, 0, ',', '.') }}</p>
          <p class="mt-1 text-[11px] text-gray-500">Rating tertinggi.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-emerald-50 p-4">
          <p class="text-[11px] font-semibold text-rose-600 uppercase tracking-wide">Perlu Perhatian (1–2★)</p>
          <p class="mt-2 text-2xl font-extrabold text-rose-600">{{ number_format($lowCount, 0, ',', '.') }}</p>
          <p class="mt-1 text-[11px] text-gray-500">Keluhan / kurang puas.</p>
        </div>
      </div>

      {{-- DISTRIBUSI RATING (simple bar) --}}
      <div class="bg-white rounded-2xl shadow-sm border border-emerald-50 p-5">
        <p class="text-sm font-semibold text-emerald-700 mb-3">Distribusi Rating</p>

        @php
          $max = max(1, $count1, $count2, $count3, $count4, $count5);
          $bars = [
            5 => $count5,
            4 => $count4,
            3 => $count3,
            2 => $count2,
            1 => $count1,
          ];
        @endphp

        <div class="space-y-2">
          @foreach($bars as $star => $cnt)
            @php $w = (int) round(($cnt / $max) * 100); @endphp
            <div class="flex items-center gap-3">
              <div class="w-12 text-xs text-gray-600 font-semibold">{{ $star }} ★</div>
              <div class="flex-1 h-2 rounded-full bg-gray-100 overflow-hidden">
                <div class="h-2 bg-emerald-500" style="width: {{ $w }}%"></div>
              </div>
              <div class="w-16 text-right text-xs text-gray-600 tabular-nums">{{ $cnt }}</div>
            </div>
          @endforeach
        </div>
      </div>

      {{-- LIST ULASAN --}}
      <div class="bg-white rounded-2xl shadow-sm border border-emerald-50 p-5">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-sm font-semibold text-emerald-700">Daftar Ulasan</h3>
          <p class="text-xs text-gray-500">
            Menampilkan {{ $reviews->count() }} ulasan
          </p>
        </div>

        @if($reviews->isEmpty())
          <p class="text-sm text-gray-500">Belum ada ulasan untuk produk Anda.</p>
        @else
          <div class="space-y-3">
            @foreach($reviews as $r)
              @php
                $p = $r->product;
                $buyer = $r->user;
              @endphp

              <div class="rounded-2xl border border-gray-100 hover:border-emerald-200 hover:shadow-sm transition bg-gray-50/60 p-4">
                <div class="flex flex-col md:flex-row md:items-start gap-4">
                  {{-- Thumbnail produk --}}
                  <div class="w-full md:w-28 shrink-0">
                    <div class="aspect-square rounded-xl overflow-hidden border border-gray-200 bg-white">
                      @if($p && !empty($p->foto_produk))
                        <img src="{{ asset('storage/' . $p->foto_produk) }}" class="w-full h-full object-cover" alt="Produk">
                      @else
                        <div class="w-full h-full flex items-center justify-center text-xs text-gray-400">
                          No Image
                        </div>
                      @endif
                    </div>
                  </div>

                  <div class="flex-1 min-w-0">
                    {{-- header review --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                      <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate">
                          {{ $p->nama_produk ?? 'Produk tidak ditemukan' }}
                        </p>
                        <p class="text-xs text-gray-500">
                          Pembeli: <span class="font-semibold text-gray-700">{{ $buyer->name ?? 'User' }}</span>
                          • {{ $r->created_at?->format('d M Y, H:i') }}
                        </p>
                      </div>

                      {{-- rating stars --}}
                      <div class="flex items-center gap-1 text-sm">
                        @for($i=1;$i<=5;$i++)
                          @if($i <= (int)($r->rating ?? 0))
                            <span class="text-amber-400">★</span>
                          @else
                            <span class="text-gray-300">★</span>
                          @endif
                        @endfor
                        <span class="ml-1 text-xs text-gray-500">({{ (int)($r->rating ?? 0) }}/5)</span>
                      </div>
                    </div>

                    {{-- isi komentar --}}
                    @if(!empty($r->comment))
                      <div class="mt-3 text-sm text-gray-700 whitespace-pre-line">
                        “{{ $r->comment }}”
                      </div>
                    @else
                      <div class="mt-3 text-xs text-gray-400 italic">
                        Pembeli tidak menulis komentar (rating saja).
                      </div>
                    @endif

                    {{-- tombol cepat --}}
                    <div class="mt-4 flex flex-wrap gap-2">
                      @if($p)
                        <a href="{{ route('mitra.products.edit', $p->id) }}"
                           class="inline-flex items-center px-3 py-2 rounded-xl bg-white border text-xs font-semibold text-gray-700 hover:bg-gray-50">
                          Edit Produk
                        </a>

                        <a href="{{ route('mitra.products.show', $p->id) }}"
                           class="inline-flex items-center px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700">
                          Lihat Detail Produk
                        </a>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>

          <div class="mt-4">
            {{ $reviews->links() }}
          </div>
        @endif
      </div>

    </div>
  </div>
</x-app-layout>
