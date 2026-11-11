<x-app-layout>
    {{-- <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Beranda Produk') }}
        </h2>
    </x-slot> --}}

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Judul & deskripsi --}}
            <div class="mb-2">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Daftar Produk
                </h3>
            </div>

            {{-- BANNER UTAMA FARMEDIA (3 KOLOM ALA SHOPEE) --}}
            <div class="mb-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                    {{-- Banner besar kiri: 1194 x 353 --}}
<div class="lg:col-span-2">
    <div class="w-full rounded-xl overflow-hidden shadow-sm">
        <img
            src="{{ asset('images/banner1.png') }}" {{-- 1. UBAH JADI .WEBP (Setelah dikompres di TinyPNG) --}}
            fetchpriority="high"                      {{-- 2. TAMBAHKAN BARIS INI (Prioritas Utama Browser) --}}
            width="1194"                             {{-- 3. TAMBAHKAN UKURAN LEBAR ASLI --}}
            height="353"                             {{-- 4. TAMBAHKAN UKURAN TINGGI ASLI --}}
            alt="Banner utama Farmedia"
            class="w-full h-full object-cover"
            style="aspect-ratio: 1194 / 353;"
        >
    </div>
</div>

                    {{-- Dua banner kecil kanan: 597 x 171 --}}
                    <div class="flex flex-col gap-4">
                        <div class="w-full rounded-xl overflow-hidden shadow-sm">
                            <img
                                src="{{ asset('images/banner2.png') }}"
                                alt="Banner promo Farmedia 2"
                                class="w-full h-full object-cover"
                                style="aspect-ratio: 597 / 171;"
                            >
                        </div>
                        <div class="w-full rounded-xl overflow-hidden shadow-sm">
                            <img
                                src="{{ asset('images/banner3.png') }}"
                                alt="Banner promo Farmedia 3"
                                class="w-full h-full object-cover"
                                style="aspect-ratio: 597 / 171;"
                            >
                        </div>
                    </div>

                </div>
            </div>

            {{-- KATEGORI PRODUK ALA SHOPEE (PAKAI FOTO ICON) --}}
            @if($categories->isNotEmpty())
                <div class="mb-8 rounded-xl shadow-sm p-4
                        bg-gradient-to-r from-emerald-900 via-emerald-800 to-emerald-700 text-white">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                        KATEGORI
                    </h4>

                    @php
                        // Mapping: nama kategori di DB -> nama file ikon
                        $categoryIcons = [
                            'Pupuk'          => 'pupuk.jpg',
                            'Alat Pertanian' => 'alatpertanian.jpg',
                            // tambahkan kategori lain di sini:
                            'Media Tanam'   => 'media-tanam.jpg',
                            'Bibit'      => 'ikon-bibit.jpg',
                            'Benih'      => 'ikon-benih.jpg',
                            'Obat Tanaman'  => 'obat-tanaman.png',
                        ];
                    @endphp

                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
                        @foreach($categories as $category)
                            @php
                                $isActive = ($selectedCategory === $category);

                                // Kalau ada di mapping, pakai itu. Kalau tidak, pakai slug + default.
                                $slug = \Illuminate\Support\Str::slug($category);
                                $iconFile = $categoryIcons[$category] ?? ($slug . '.png');
                            @endphp

                            <a href="{{ route('user.beranda', array_filter(['category' => $category, 'q' => $q])) }}"
                               class="flex flex-col items-center justify-center text-center text-xs sm:text-[13px]
                                   border rounded-lg px-2 py-3
                                   {{ $isActive
                                        ? 'bg-emerald-500 text-white border-emerald-400'
                                        : 'bg-emerald-800/60 text-white border-emerald-700 hover:bg-emerald-700/80' }}">

                                {{-- Icon kategori (foto) --}}
                                <div class="flex items-center justify-center w-12 h-12 mb-1 rounded-full
                                            {{ $isActive ? 'bg-white' : 'bg-emerald-50' }}">
                                    <img
                                        src="{{ asset('images/kategori/' . $iconFile) }}"
                                        alt="{{ $category }}"
                                        class="w-8 h-8 object-contain"
                                        onerror="this.onerror=null;this.src='{{ asset('images/kategori/default.png') }}';">
                                </div>

                                <span class="line-clamp-2">{{ $category }}</span>
                            </a>
                        @endforeach

                        {{-- Tombol hapus filter kategori --}}
                        @if($selectedCategory)
                            <a href="{{ route('user.beranda', ['q' => $q]) }}"
                               class="flex flex-col items-center justify-center text-center text-xs sm:text-[13px]
                                      border rounded-lg px-2 py-3 bg-gray-50 text-gray-600 hover:bg-gray-100 border-gray-200">
                                <div class="flex items-center justify-center w-12 h-12 mb-1 rounded-full bg-gray-100">
                                    ✕
                                </div>
                                <span class="line-clamp-2">Hapus filter</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ======================
                 HOME ADS
            ======================= --}}
            @if(isset($homeAds) && $homeAds->isNotEmpty() && empty($selectedCategory))
                <section class="mb-8">
                    <div class="overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-sm">
                        {{-- Header --}}
                        <div class="flex items-center justify-between bg-gradient-to-r from-emerald-700 to-emerald-500 px-5 py-4 text-white">
                            <div>
                                <p class="text-[11px] font-semibold tracking-[0.22em] text-emerald-100">DISPONSORI</p>
                                <h3 class="text-lg font-semibold leading-tight">Promo / Iklan Pilihan</h3>
                                <p class="text-xs text-emerald-100/90">Rekomendasi sponsor untuk kamu</p>
                            </div>
                            <span class="inline-flex items-center rounded-full border border-white/25 bg-white/15 px-3 py-1 text-xs font-semibold">
                                SPONSORED
                            </span>
                        </div>

                        {{-- Carousel --}}
                        <div class="p-4">
                            <div class="flex gap-4 overflow-x-auto pb-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden snap-x snap-mandatory">
                                @foreach($homeAds as $p)
                                    <a href="{{ route('user.produk.show', $p->id) }}"
                                       class="snap-start min-w-[240px] sm:min-w-[260px] max-w-[260px] rounded-2xl border border-emerald-100 bg-white hover:shadow-md transition overflow-hidden">

                                        {{-- Image --}}
                                        <div class="relative">
                                            @if($p->foto_url)
                                                <img src="{{ $p->foto_url }}"
                                                     class="h-36 w-full object-cover" alt="">
                                            @else
                                                <div class="h-36 w-full bg-emerald-50 flex items-center justify-center text-xs text-emerald-700">
                                                    Tidak ada foto
                                                </div>
                                            @endif

                                            <div class="absolute left-3 top-3 inline-flex items-center rounded-full bg-emerald-900/80 px-2.5 py-1 text-[10px] font-bold tracking-widest text-white">
                                                SPONSORED
                                            </div>
                                        </div>

                                        {{-- Content --}}
                                        <div class="p-4">
                                            <div class="font-semibold text-gray-900 line-clamp-2 min-h-[40px]">
                                                {{ $p->nama_produk }}
                                            </div>

                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $p->mitra->nama_toko ?? '-' }}
                                            </div>

                                            @php
                                                $avg = round($p->reviews_avg_rating ?? 0, 1);
                                                $cnt = (int)($p->reviews_count ?? 0);
                                            @endphp

                                            @if($cnt > 0)
                                                <div class="mt-2 flex items-center gap-1 text-[11px] text-yellow-500">
                                                    @for($i=1; $i<=5; $i++)
                                                        <span>{{ $i <= round($avg) ? '★' : '☆' }}</span>
                                                    @endfor
                                                    <span class="ml-1 text-gray-500">{{ $avg }} ({{ $cnt }})</span>
                                                </div>
                                            @endif

                                            <div class="mt-3 flex items-center justify-between">
                                                <div class="font-bold text-orange-500">
                                                    Rp {{ number_format($p->harga, 0, ',', '.') }}
                                                </div>
                                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                                                    Lihat
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>

                            <p class="mt-2 text-[11px] text-gray-500">
                                Geser ke samping untuk melihat promo lainnya.
                            </p>
                        </div>
                    </div>
                </section>
            @endif

            {{-- ======================
                 CATEGORY ADS
            ======================= --}}
            @if(isset($categoryAds) && $categoryAds->isNotEmpty() && !empty($selectedCategory))
                <section class="mb-8">
                    <div class="overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-sm">
                        {{-- Header --}}
                        <div class="flex items-center justify-between bg-gradient-to-r from-emerald-700 to-emerald-500 px-5 py-4 text-white">
                            <div>
                                <p class="text-[11px] font-semibold tracking-[0.22em] text-emerald-100">DISPONSORI</p>
                                <h3 class="text-lg font-semibold leading-tight">
                                    Iklan Kategori: {{ $selectedCategory }}
                                </h3>
                                <p class="text-xs text-emerald-100/90">Sponsor khusus kategori ini</p>
                            </div>
                            <span class="inline-flex items-center rounded-full border border-white/25 bg-white/15 px-3 py-1 text-xs font-semibold">
                                SPONSORED
                            </span>
                        </div>

                        {{-- Carousel --}}
                        <div class="p-4">
                            <div class="flex gap-4 overflow-x-auto pb-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden snap-x snap-mandatory">
                                @foreach($categoryAds as $p)
                                    <a href="{{ route('user.produk.show', $p->id) }}"
                                       class="snap-start min-w-[240px] sm:min-w-[260px] max-w-[260px] rounded-2xl border border-emerald-100 bg-white hover:shadow-md transition overflow-hidden">

                                        <div class="relative">
                                            @if($p->foto_url)
                                                <img src="{{ $p->foto_url }}"
                                                     class="h-36 w-full object-cover" alt="">
                                            @else
                                                <div class="h-36 w-full bg-emerald-50 flex items-center justify-center text-xs text-emerald-700">
                                                    Tidak ada foto
                                                </div>
                                            @endif

                                            <div class="absolute left-3 top-3 inline-flex items-center rounded-full bg-emerald-900/80 px-2.5 py-1 text-[10px] font-bold tracking-widest text-white">
                                                SPONSORED
                                            </div>
                                        </div>

                                        <div class="p-4">
                                            <div class="font-semibold text-gray-900 line-clamp-2 min-h-[40px]">
                                                {{ $p->nama_produk }}
                                            </div>

                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $p->mitra->nama_toko ?? '-' }}
                                            </div>

                                            @php
                                                $avg = round($p->reviews_avg_rating ?? 0, 1);
                                                $cnt = (int)($p->reviews_count ?? 0);
                                            @endphp

                                            @if($cnt > 0)
                                                <div class="mt-2 flex items-center gap-1 text-[11px] text-yellow-500">
                                                    @for($i=1; $i<=5; $i++)
                                                        <span>{{ $i <= round($avg) ? '★' : '☆' }}</span>
                                                    @endfor
                                                    <span class="ml-1 text-gray-500">{{ $avg }} ({{ $cnt }})</span>
                                                </div>
                                            @endif

                                            <div class="mt-3 flex items-center justify-between">
                                                <div class="font-bold text-orange-500">
                                                    Rp {{ number_format($p->harga, 0, ',', '.') }}
                                                </div>
                                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                                                    Lihat
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>

                            <p class="mt-2 text-[11px] text-gray-500">
                                Geser ke samping untuk melihat sponsor kategori lainnya.
                            </p>
                        </div>
                    </div>
                </section>
            @endif

            {{-- Kalau tidak ada produk --}}
            @if($products->isEmpty())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-gray-600 dark:text-gray-300">
                        Belum ada produk yang tersedia.
                    </p>
                </div>
            @else

                {{-- GRID PRODUK --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">

                    @foreach($products as $product)
                        <div class="relative rounded-xl shadow hover:shadow-lg transition overflow-hidden
                            bg-emerald-900/90 border border-emerald-700 text-white">

                            {{-- TOMBOl KERANJANG --}}
                            <form action="{{ route('cart.add', $product->id) }}" method="POST"
                                  class="absolute top-3 right-3 z-10">
                                @csrf
                                <button type="submit" aria-label="Masukkan produk ke keranjang"
                                        class="p-2 bg-white/90 backdrop-blur rounded-full shadow hover:bg-orange-500 hover:text-white transition">
                                    🛒
                                </button>
                            </form>

                            {{-- FOTO PRODUK --}}
                            <a href="{{ route('user.produk.show', $product->id) }}">
                                @if($product->foto_url)
                                    <img src="{{ $product->foto_url }}"
                                         class="h-44 w-full object-cover" alt="">
                                @else
                                    <div class="h-44 w-full flex items-center justify-center text-white/70 bg-emerald-800/70">
                                        Tidak ada foto
                                    </div>
                                @endif
                            </a>

                            {{-- INFORMASI --}}
                            <div class="p-3 flex flex-col">

                                <a href="{{ route('user.produk.show', $product->id) }}" class="flex-1">
                                    <h4 class="font-semibold text-sm text-gray-100 line-clamp-2 h-10">
                                        {{ $product->nama_produk }}
                                    </h4>

                                    <p class="text-xs text-white/90 mb-1">
                                        {{ $product->mitra->nama_toko ?? 'Toko tidak tersedia' }}
                                    </p>

                                    @php
                                        $avg   = round($product->reviews_avg_rating ?? 0, 1);
                                        $count = $product->reviews_count;
                                    @endphp

                                    {{-- RATING DI CARD (TANPA FORM) --}}
                                    @if($count > 0)
                                        <div class="flex items-center gap-1 text-[11px] text-yellow-300 mb-1">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <span>{{ $i <= round($avg) ? '★' : '☆' }}</span>
                                            @endfor
                                            <span class="ml-1 text-[11px] text-emerald-50">
                                                {{ $avg }} ({{ $count }})
                                            </span>
                                        </div>
                                    @endif

                                    <div class="font-bold text-orange-400 text-lg">
                                        Rp {{ number_format($product->harga, 0, ',', '.') }}
                                    </div>

                                    <p class="text-xs text-white/90 mt-1">
                                        Stok: {{ $product->stok }}
                                    </p>
                                </a>

                            </div>
                        </div>
                    @endforeach

                </div>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
