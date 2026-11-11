<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Tambah Produk
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Lengkapi detail produk agar menarik di mata pembeli.
                </p>
            </div>

            <a href="{{ route('mitra.products.index') }}"
               class="inline-flex items-center px-3 py-1.5 text-xs rounded-lg border border-gray-300
                      bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                ← Kembali ke daftar
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <div class="font-semibold mb-1">Ada kesalahan pada input:</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800/90 shadow-sm border border-emerald-50/70
                    dark:border-gray-700 sm:rounded-2xl overflow-hidden">
            <div class="p-6 sm:p-8 text-gray-900 dark:text-gray-100">

                <form action="{{ route('mitra.products.store') }}" method="POST" enctype="multipart/form-data"
                      class="space-y-6">
                    @csrf

                    {{-- Baris 1: nama + kategori --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nama Produk
                            </label>
                            <input type="text" name="nama_produk" value="{{ old('nama_produk') }}"
                                   class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700
                                          dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm
                                          focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Kategori Produk
                            </label>
                            <input type="text" name="kategori_produk" value="{{ old('kategori_produk') }}"
                                   class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700
                                          dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm
                                          focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Deskripsi Produk
                        </label>
                        <textarea name="deskripsi_produk" rows="4"
                                  class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700
                                         dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm
                                         focus:ring-emerald-500 focus:border-emerald-500">{{ old('deskripsi_produk') }}</textarea>
                    </div>

                    {{-- Harga / Stok / Berat --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Harga (Rp)
                            </label>
                            <input type="number" name="harga" value="{{ old('harga') }}"
                                   class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700
                                          dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm
                                          focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Stok
                            </label>
                            <input type="number" name="stok" value="{{ old('stok') }}"
                                   class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700
                                          dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm
                                          focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Berat (gram)
                            </label>
                            <input type="number" name="berat" value="{{ old('berat') }}"
                                   class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700
                                          dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm
                                          focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>

                    {{-- Foto --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Foto Produk
                        </label>
                        <input type="file" name="foto_produk"
                               class="mt-2 block w-full text-sm text-gray-900 dark:text-gray-100
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-lg file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-emerald-50 file:text-emerald-700
                                      hover:file:bg-emerald-100">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Gunakan foto yang jelas dan menarik. Format: jpg, jpeg, png. Maks 2MB.
                        </p>
                    </div>

                    {{-- Tombol --}}
                    <div class="pt-4 flex justify-end gap-3">
                        <a href="{{ route('mitra.products.index') }}"
                           class="px-4 py-2 text-xs rounded-lg border border-gray-300
                                  bg-white text-gray-700 hover:bg-gray-50
                                  dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                            Batal
                        </a>
                        <button type="submit"
                                class="px-5 py-2 text-xs rounded-lg font-semibold
                                       bg-emerald-600 text-white shadow-sm hover:bg-emerald-700
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                            Simpan Produk
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>
