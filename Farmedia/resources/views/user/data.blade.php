{{-- resources/views/user/beranda.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Beranda Produk') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Section judul & info singkat --}}
            <div class="mb-6">
                <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-1">
                    Daftar Produk Tersedia
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Berikut produk-produk yang tersedia dari berbagai mitra/toko.
                </p>
            </div>

            {{-- Jika tidak ada produk --}}
            @if($products->isEmpty())
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-700 dark:text-gray-200">
                        Belum ada produk yang tersedia saat ini.
                    </div>
                </div>
            @else
                {{-- Grid card produk --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($products as $product)
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 flex flex-col">
                            {{-- Gambar produk --}}
                            @if($product->foto_produk)
                                <img
                                    src="{{ asset('storage/' . $product->foto_produk) }}"
                                    alt="{{ $product->nama_produk }}"
                                    class="h-40 w-full object-cover"
                                >
                            @else
                                {{-- Placeholder kalau tidak ada foto --}}
                                <div class="h-40 w-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 text-gray-400 text-xs">
                                    Tidak ada foto
                                </div>
                            @endif

                            {{-- Isi card --}}
                            <div class="p-4 flex-1 flex flex-col">
                                {{-- Nama produk --}}
                                <h4 class="font-semibold text-sm md:text-base text-gray-800 dark:text-gray-100 mb-1 line-clamp-2">
                                    {{ $product->nama_produk }}
                                </h4>

                                {{-- Nama toko / mitra --}}
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                    Toko:
                                    <span class="font-medium">
                                        {{ $product->mitra->nama_toko ?? '-' }}
                                    </span>
                                </p>

                                {{-- Deskripsi singkat --}}
                                @if($product->deskripsi_produk)
                                    <p class="text-xs text-gray-600 dark:text-gray-300 mb-3 line-clamp-3">
                                        {{ Str::limit($product->deskripsi_produk, 90) }}
                                    </p>
                                @else
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3 italic">
                                        Tidak ada deskripsi.
                                    </p>
                                @endif

                                {{-- Harga & stok --}}
                                <div class="mt-auto">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                            Rp {{ number_format($product->harga, 0, ',', '.') }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            Stok: {{ $product->stok }}
                                        </span>
                                    </div>

                                    {{-- Tombol aksi (kalau nanti mau dibuat detail / beli) --}}
                                    <button
                                        type="button"
                                        class="w-full inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none">
                                        Lihat Detail
                                    </button>
                                </div>
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
