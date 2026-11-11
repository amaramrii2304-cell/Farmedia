<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Produk
        </h2>
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Nama Produk -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Nama Produk</label>
                    <input type="text" name="nama_produk" value="{{ old('nama_produk', $product->nama_produk) }}"
                           class="w-full border px-3 py-2 rounded" required>
                </div>

                <!-- Nama Toko / Mitra (Read-only) -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Mitra / Toko</label>
                    <input type="text" value="{{ $product->mitra->nama_toko ?? '-' }}"
                           class="w-full border px-3 py-2 rounded bg-gray-100" disabled>
                </div>

                <!-- Kategori Produk -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Kategori</label>
                    <input type="text" name="kategori_produk" value="{{ old('kategori_produk', $product->kategori_produk) }}"
                           class="w-full border px-3 py-2 rounded">
                </div>

                <!-- Deskripsi Produk -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="deskripsi_produk" rows="4"
                              class="w-full border px-3 py-2 rounded">{{ old('deskripsi_produk', $product->deskripsi_produk) }}</textarea>
                </div>

                <!-- Harga -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Harga</label>
                    <input type="number" name="harga" value="{{ old('harga', $product->harga) }}"
                           class="w-full border px-3 py-2 rounded" required min="0" step="0.01">
                </div>

                <!-- Stok -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Stok</label>
                    <input type="number" name="stok" value="{{ old('stok', $product->stok) }}"
                           class="w-full border px-3 py-2 rounded" required min="0">
                </div>

                <!-- Berat -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Berat (gram)</label>
                    <input type="number" name="berat" value="{{ old('berat', $product->berat) }}"
                           class="w-full border px-3 py-2 rounded" min="0" step="0.01">
                </div>

                <!-- Foto Produk -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-1">Foto Produk</label>
                    @if ($product->foto_produk)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $product->foto_produk) }}" alt="Foto Produk"
                                 class="w-32 h-32 object-cover border rounded">
                        </div>
                    @endif
                    <input type="file" name="foto_produk" class="w-full">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah foto.</p>
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('admin.products.index') }}"
                       class="px-4 py-2 rounded bg-gray-500 text-white hover:bg-gray-600">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                        Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
