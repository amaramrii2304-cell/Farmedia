<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Produk Pertanian (Mitra)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('mitra.products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label class="block text-sm mb-1">Nama Produk</label>
                            <input type="text" name="nama_produk" value="{{ old('nama_produk') }}"
                                   class="w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700">
                            @error('nama_produk')
                                <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm mb-1">Kategori Produk</label>
                            <input type="text" name="kategori_produk" value="{{ old('kategori_produk') }}"
                                   class="w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm mb-1">Deskripsi Produk</label>
                            <textarea name="deskripsi_produk" rows="3"
                                      class="w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700">{{ old('deskripsi_produk') }}</textarea>
                        </div>

                        <div class="mb-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm mb-1">Harga (Rp)</label>
                                <input type="number" name="harga" value="{{ old('harga') }}"
                                       class="w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700">
                                @error('harga')
                                    <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Stok</label>
                                <input type="number" name="stok" value="{{ old('stok') }}"
                                       class="w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700">
                                @error('stok')
                                    <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Berat (gram)</label>
                                <input type="number" name="berat" value="{{ old('berat') }}"
                                       class="w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm mb-1">Foto Produk</label>
                            <input type="file" name="foto_produk"
                                   class="w-full text-sm text-gray-300">
                            <p class="text-xs text-gray-400 mt-1">
                                Format: JPG/PNG, maks 2MB.
                            </p>
                            @error('foto_produk')
                                <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex justify-end space-x-2">
                            <a href="{{ route('mitra.products.index') }}"
                               class="px-4 py-2 text-sm rounded border border-gray-500">
                                Batal
                            </a>
                            <button type="submit"
                                    class="px-4 py-2 text-sm rounded bg-blue-600 text-white hover:bg-blue-700">
                                Simpan
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
