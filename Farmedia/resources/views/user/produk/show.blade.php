<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-slate-900 leading-tight">
            Detail Produk
        </h2>
    </x-slot>

    <div class="py-6 bg-slate-100">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            {{-- FLASH MESSAGE dari review --}}
            @if(session('success'))
                <div class="mb-3 px-4 py-2 rounded-lg bg-emerald-50 border border-emerald-100 text-xs text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-3 px-4 py-2 rounded-lg bg-red-50 border border-red-100 text-xs text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            {{-- CARD DETAIL PRODUK --}}
            <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-slate-200">

                {{-- HEADER FARMEDIA + ringkas rating --}}
                <div class="bg-emerald-700 px-5 py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                    <div class="space-y-1">
                        <p class="text-[11px] uppercase tracking-[0.16em] text-emerald-100/80">
                            Produk Farmedia
                        </p>
                        <h1 class="text-lg md:text-xl font-semibold text-white leading-snug line-clamp-2">
                            {{ $product->nama_produk }}
                        </h1>
                        <div class="flex items-center gap-2 text-[11px] text-emerald-50/90">
                            {{-- <span class="px-2 py-0.5 rounded-full bg-emerald-600/60 border border-emerald-300/40">
                                Toko
                            </span> --}}
                            <span class="font-medium">
  {{ $product->mitra->nama_toko ?? 'Tidak tersedia' }}
</span>

@if($product->mitra)
  <a href="{{ route('toko.show', $product->mitra->id) }}"
     class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold
            bg-white/15 border border-white/25 text-white hover:bg-white/20">
    Kunjungi
  </a>
@endif
                        </div>
                    </div>

                    <div class="text-right space-y-1">
                        <p class="text-[11px] text-emerald-100/80">Harga</p>
                        <p class="text-xl font-bold text-white">
                            Rp {{ number_format($product->harga, 0, ',', '.') }}
                        </p>
                        <p class="text-[11px] text-emerald-100/80">
                            Stok: <span class="font-semibold">{{ $product->stok }}</span>
                        </p>

                        {{-- ringkas rating di header --}}
                        <div class="mt-1 flex items-center justify-end gap-1 text-[11px] text-amber-200">
                            @for($i=1;$i<=5;$i++)
                                @if($i <= floor($avgRating))
                                    <span>★</span>
                                @else
                                    <span class="text-emerald-200/40">★</span>
                                @endif
                            @endfor
                            <span class="ml-1">
                                {{ $reviewsCount }} ulasan
                            </span>
                        </div>
                    </div>
                </div>

                {{-- ISI CARD --}}
                <div class="p-5 space-y-4 bg-slate-50">

                    {{-- BAGIAN ATAS: FOTO & INFO SINGKAT --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- FOTO PRODUK --}}
                        <div class="bg-white rounded-lg p-3 flex items-center justify-center border border-slate-200">
                            <img src="{{ asset('storage/' . $product->foto_produk) }}"
                                 class="max-h-56 w-full object-contain rounded-md"
                                 alt="{{ $product->nama_produk }}">
                        </div>

                        {{-- INFO & TOMBOL --}}
                        <div class="flex flex-col justify-between gap-4">
                            {{-- INFO RINGKAS --}}
                            <div class="space-y-1.5">
                                <p class="text-sm text-slate-600">
                                    Kategori:
                                    <span class="font-semibold text-slate-800">
                                        {{ $product->kategori_produk ?? '-' }}
                                    </span>
                                </p>

                                <p class="text-sm text-slate-600">
                                    Stok:
                                    <span class="font-semibold text-emerald-700">
                                        {{ $product->stok }} unit
                                    </span>
                                </p>
                            </div>

                            {{-- TOMBOL AKSI --}}
                            <div class="flex flex-col sm:flex-row gap-2 mt-2">
                                @if($product->stok > 0)
                                    {{-- Beli Sekarang --}}
                                    <form action="{{ route('checkout.show', $product->id) }}" method="GET" class="flex-1">
                                        <button
                                            type="submit"
                                            class="w-full inline-flex items-center justify-center px-3 py-2 rounded-full 
                                                   bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold
                                                   shadow-sm transition">
                                            Beli Sekarang
                                        </button>
                                    </form>
                                @else
                                    <button
                                        class="flex-1 inline-flex items-center justify-center px-3 py-2 rounded-full 
                                               bg-slate-300 text-white text-xs font-semibold cursor-not-allowed">
                                        Stok Habis
                                    </button>
                                @endif

                                {{-- Kembali --}}
                                <a href="{{ route('user.beranda') }}"
                                   class="inline-flex items-center justify-center px-3 py-2 rounded-full border 
                                          border-slate-300 text-xs font-medium
                                          text-slate-700 bg-white hover:bg-slate-50 transition">
                                    Kembali
                                </a>
                            </div>
                        </div>
                    </div>

                    <hr class="border-slate-200">

                    {{-- DESKRIPSI PRODUK --}}
                    <div>
                        <h3 class="text-sm md:text-base font-semibold text-slate-900 mb-1.5">
                            Deskripsi Produk
                        </h3>
                        <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">
                            {{ $product->deskripsi_produk ?? '-' }}
                        </p>
                    </div>

                    {{-- RATING & ULASAN --}}
                    <div class="pt-3">
                        <h3 class="text-sm md:text-base font-semibold text-slate-900 mb-2">
                            Rating & Ulasan
                        </h3>

                        <div class="bg-white rounded-lg border border-slate-200 p-4 space-y-4">
                            {{-- RINGKASAN RATING --}}
                            <div class="flex items-center gap-4">
                                <div class="text-3xl font-bold text-amber-500">
                                    {{ $avgRating ?: '0.0' }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-1 text-[13px]">
                                        @for($i=1;$i<=5;$i++)
                                            @if($i <= floor($avgRating))
                                                <span class="text-amber-400">★</span>
                                            @else
                                                <span class="text-slate-300">★</span>
                                            @endif
                                        @endfor
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1">
                                        {{ $reviewsCount }} ulasan pembeli
                                    </p>
                                </div>
                            </div>

                            {{-- FORM ULASAN (jika boleh review) --}}
                            @if($canReview)
                                <form action="{{ route('products.review.store', $product->id) }}"
                                      method="POST"
                                      class="border-t border-slate-200 pt-3 space-y-2">
                                    @csrf
                                    <p class="text-xs font-semibold text-slate-800">
                                        Beri ulasan Anda
                                    </p>

                                    <div>
                                        <label class="text-[11px] font-medium text-slate-700">Rating *</label>
                                        <div class="flex items-center gap-2 mt-1">
                                            @for($i=1;$i<=5;$i++)
                                                <label class="flex items-center gap-1 text-xs cursor-pointer">
                                                    <input type="radio" name="rating" value="{{ $i }}"
                                                           class="text-amber-500"
                                                           @checked(old('rating', 5) == $i)>
                                                    <span>{{ $i }}</span>
                                                </label>
                                            @endfor
                                        </div>
                                        @error('rating')
                                            <p class="text-[11px] text-red-500 mt-0.5">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="text-[11px] font-medium text-slate-700">
                                            Ulasan (opsional)
                                        </label>
                                        <textarea name="comment" rows="3"
                                                  class="mt-1 w-full border-slate-300 rounded-md text-sm
                                                         focus:ring-emerald-500 focus:border-emerald-500">{{ old('comment') }}</textarea>
                                        @error('comment')
                                            <p class="text-[11px] text-red-500 mt-0.5">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <button type="submit"
                                            class="mt-1 inline-flex items-center px-4 py-2 rounded-md text-xs font-semibold
                                                   bg-emerald-600 text-white hover:bg-emerald-700">
                                        Kirim Ulasan
                                    </button>
                                </form>
                            @endif

                            {{-- DAFTAR ULASAN --}}
                            @if($reviewsCount > 0)
                                <div class="border-t border-slate-200 pt-3 space-y-3">
                                    @foreach($product->reviews->sortByDesc('created_at') as $review)
                                        <div class="flex gap-3">
                                            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-xs font-semibold text-emerald-800">
                                                {{ strtoupper(mb_substr($review->user->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <p class="text-xs font-semibold text-slate-800">
                                                        {{ $review->user->name ?? 'Pengguna' }}
                                                    </p>
                                                    <p class="text-[10px] text-slate-400">
                                                        {{ $review->created_at?->format('d M Y') }}
                                                    </p>
                                                </div>
                                                <div class="flex items-center gap-1 text-[11px] mt-0.5">
                                                    @for($i=1;$i<=5;$i++)
                                                        @if($i <= $review->rating)
                                                            <span class="text-amber-400">★</span>
                                                        @else
                                                            <span class="text-slate-300">★</span>
                                                        @endif
                                                    @endfor
                                                </div>
                                                @if($review->comment)
                                                    <p class="mt-1 text-xs text-slate-700">
                                                        {{ $review->comment }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-slate-500 border-t border-slate-200 pt-3">
                                    Belum ada ulasan untuk produk ini.
                                </p>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
