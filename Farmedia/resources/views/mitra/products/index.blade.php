<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Produk Saya
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Kelola produk yang tampil di etalase toko Farmedia.
                </p>
            </div>

            <a href="{{ route('mitra.products.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                      bg-emerald-600 text-white shadow-sm hover:bg-emerald-700 transition">
                <span class="text-base">＋</span>
                <span>Tambah Produk</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">

        {{-- Flash success / error --}}
        @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ session('error') }}
            </div>
        @endif

        @php
            $layout = $layout ?? request('layout', 'list');
        @endphp

        <div class="flex items-center justify-between mb-3">
            <div class="text-xs text-slate-500">
                Tampilan produk
            </div>

            <div class="inline-flex items-center gap-1 text-xs bg-slate-100 rounded-full p-1">
                {{-- tombol LIST --}}
                <a href="{{ route('mitra.products.index', array_merge(request()->query(), ['layout' => 'list'])) }}"
                   class="px-3 py-1.5 rounded-full {{ $layout === 'list' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600' }}">
                    List
                </a>

                {{-- tombol GRID --}}
                <a href="{{ route('mitra.products.index', array_merge(request()->query(), ['layout' => 'grid'])) }}"
                   class="px-3 py-1.5 rounded-full {{ $layout === 'grid' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600' }}">
                    Grid
                </a>
            </div>
        </div>

        {{-- =====================================
             TAMPILAN LIST
        ====================================== --}}
        @if ($layout === 'list')

            @if ($products->count() === 0)
                <div class="bg-white rounded-lg border border-slate-200 p-3 text-[11px] text-slate-500">
                    Belum ada produk. Tambahkan produk pertama Anda.
                </div>
            @else
                <div class="bg-white rounded-lg border border-slate-200 overflow-hidden text-[11px]">

                    {{-- Toolbar atas --}}
                    <div class="px-4 pt-3 pb-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <h3 class="text-[12px] font-semibold text-slate-900">
                                Daftar Produk
                            </h3>
                            <p class="text-[11px] text-slate-500">
                                Total: {{ $products->total() }} produk.
                            </p>
                        </div>

                        <div class="flex items-center gap-2 text-[10px] text-slate-500">
                            <span class="inline-flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Stok aman
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Menipis
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Habis
                            </span>
                        </div>
                    </div>

                    {{-- Header baris --}}
                    <div class="px-4 py-1.5 bg-slate-50 border-y border-slate-200 text-[10px] font-semibold text-slate-500 uppercase tracking-wide flex">
                        <div class="flex-1">Produk</div>
                        <div class="w-24 text-right">Harga</div>
                        <div class="w-20 text-right">Stok</div>
                        <div class="w-20 text-right">Aksi</div>
                    </div>

                    {{-- List produk --}}
                    <div class="divide-y divide-slate-200">
                        @foreach ($products as $produk)
                            @php
                                // stok
                                $stok = $produk->stok;
                                if ($stok <= 0) {
                                    $stokLabel = 'Stok habis';
                                    $stokClass = 'text-rose-600';
                                    $dotClass  = 'bg-rose-500';
                                } elseif ($stok <= 5) {
                                    $stokLabel = 'Stok menipis';
                                    $stokClass = 'text-amber-600';
                                    $dotClass  = 'bg-amber-400';
                                } else {
                                    $stokLabel = 'Stok aman';
                                    $stokClass = 'text-emerald-600';
                                    $dotClass  = 'bg-emerald-500';
                                }

                                // RATING (pakai alias dari controller)
                                $avgRating    = $produk->avg_rating ? round($produk->avg_rating, 1) : null;
                                $reviewsCount = $produk->reviews_count ?? 0;
                            @endphp

                            <div class="px-4 py-2 flex items-start gap-2 hover:bg-slate-50 transition">

                                {{-- ✅ Foto produk (FIXED) --}}
                                <div class="w-12 h-12 flex-shrink-0">
                                    @if ($produk->foto_url)
                                        <img src="{{ $produk->foto_url }}"
                                             alt="{{ $produk->nama_produk }}"
                                             class="w-12 h-12 rounded-md object-cover border border-slate-200">
                                    @else
                                        <div class="w-12 h-12 rounded-md border border-dashed border-slate-300 flex items-center justify-center text-[9px] text-slate-400">
                                            No Img
                                        </div>
                                    @endif
                                </div>

                                {{-- Info utama --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-[12px] font-semibold text-slate-900 truncate">
                                        {{ $produk->nama_produk }}
                                    </p>

                                    @if($produk->kategori_produk)
                                        <p class="text-[10px] text-slate-500 truncate">
                                            {{ $produk->kategori_produk }}
                                        </p>
                                    @endif

                                    {{-- RATING PRODUK --}}
                                    @if($reviewsCount > 0)
                                        <div class="mt-0.5 flex items-center gap-1 text-[10px]">
                                            <span class="text-amber-400">★</span>
                                            <span class="font-semibold text-slate-800">
                                                {{ number_format($avgRating, 1) }}
                                            </span>
                                            <span class="text-slate-400">
                                                ({{ $reviewsCount }} ulasan)
                                            </span>
                                        </div>
                                    @else
                                        <p class="mt-0.5 text-[10px] text-slate-400">
                                            Belum ada ulasan.
                                        </p>
                                    @endif

                                    <p class="mt-0.5 flex items-center gap-1 text-[10px] {{ $stokClass }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                                        {{ $stokLabel }}
                                    </p>
                                </div>

                                {{-- Harga --}}
                                <div class="w-24 text-right">
                                    <p class="text-[11px] font-semibold text-emerald-700 whitespace-nowrap">
                                        Rp {{ number_format($produk->harga, 0, ',', '.') }}
                                    </p>
                                </div>

                                {{-- Stok --}}
                                <div class="w-20 text-right">
                                    <p class="text-[11px] text-slate-800 whitespace-nowrap">
                                        {{ $produk->stok }}
                                    </p>
                                </div>

                                {{-- Aksi --}}
                                <div class="w-20 text-right">
                                    <div class="inline-flex flex-col items-end gap-0.5">
                                        <a href="{{ route('mitra.products.edit', $produk) }}"
                                           class="text-[10px] font-medium text-emerald-700 hover:underline">
                                            Ubah
                                        </a>
                                        <form action="{{ route('mitra.products.destroy', $produk) }}"
                                              method="POST"
                                              onsubmit="return confirm('Yakin hapus produk ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-[10px] font-medium text-rose-600 hover:underline">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="px-4 py-2 border-t border-slate-200 bg-slate-50">
                        {{ $products->links() }}
                    </div>
                </div>
            @endif

        {{-- =====================================
             TAMPILAN GRID
        ====================================== --}}
        @else
            @if ($products->count() === 0)
                <div class="bg-white rounded-xl border border-slate-200 p-4 text-xs text-slate-500">
                    Belum ada produk. Tambahkan produk pertama Anda.
                </div>
            @else
                <div class="bg-white rounded-xl border border-slate-200 p-4 space-y-3">

                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Total: {{ $products->total() }} produk terdaftar.</span>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        @foreach ($products as $produk)
                            @php
                                $avgRating    = $produk->avg_rating ? round($produk->avg_rating, 1) : null;
                                $reviewsCount = $produk->reviews_count ?? 0;
                            @endphp

                            <div class="border border-slate-200 rounded-lg bg-white overflow-hidden hover:shadow-sm transition text-xs">
                                {{-- ✅ Foto (FIXED) --}}
                                <div class="aspect-[4/5] bg-slate-50 flex items-center justify-center">
                                    @if ($produk->foto_url)
                                        <img src="{{ $produk->foto_url }}"
                                             alt="{{ $produk->nama_produk }}"
                                             class="w-full h-full object-cover">
                                    @else
                                        <div class="text-[11px] text-slate-400">
                                            Tidak ada foto
                                        </div>
                                    @endif
                                </div>

                                {{-- Info --}}
                                <div class="p-2 space-y-1">
                                    <p class="text-[13px] font-semibold text-slate-900 line-clamp-2 h-8">
                                        {{ $produk->nama_produk }}
                                    </p>

                                    @if($produk->kategori_produk)
                                        <p class="text-[11px] text-slate-500 truncate">
                                            {{ $produk->kategori_produk }}
                                        </p>
                                    @endif

                                    {{-- RATING PRODUK --}}
                                    @if($reviewsCount > 0)
                                        <div class="flex items-center gap-1 text-[11px] mt-0.5">
                                            <span class="text-amber-400">★</span>
                                            <span class="font-semibold text-slate-800">
                                                {{ number_format($avgRating, 1) }}
                                            </span>
                                            <span class="text-slate-400">
                                                ({{ $reviewsCount }})
                                            </span>
                                        </div>
                                    @else
                                        <p class="text-[11px] text-slate-400 mt-0.5">
                                            Belum ada ulasan
                                        </p>
                                    @endif

                                    <p class="text-sm font-semibold text-emerald-700">
                                        Rp {{ number_format($produk->harga, 0, ',', '.') }}
                                    </p>

                                    <p class="text-[11px] text-slate-500">
                                        Stok {{ $produk->stok }}
                                    </p>

                                    <div class="flex items-center justify-end gap-2 pt-1">
                                        <a href="{{ route('mitra.products.edit', $produk) }}"
                                           class="text-[11px] font-medium text-emerald-700 hover:underline">
                                            Ubah
                                        </a>
                                        <form action="{{ route('mitra.products.destroy', $produk) }}"
                                              method="POST"
                                              onsubmit="return confirm('Yakin hapus produk ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-[11px] font-medium text-rose-600 hover:underline">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="pt-2 border-t border-slate-200">
                        {{ $products->links() }}
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
